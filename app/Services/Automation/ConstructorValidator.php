<?php

namespace App\Services\Automation;

use App\Models\ActionType;
use App\Models\AutomationTask;
use App\Models\TaskStep;
use App\Models\TaskStepBinding;
use App\Models\TaskStepCall;
use App\Models\TaskVariable;

class ConstructorValidator
{
    public function validateDraft(AutomationTask $task): array
    {
        $errors = [];
        $warnings = [];

        // Task meta
        if (!$task->name) { $errors[] = ['entity' => 'task', 'field' => 'name', 'message' => 'Name is required']; }
        if (!$task->slug) { $errors[] = ['entity' => 'task', 'field' => 'slug', 'message' => 'Slug is required']; }

        // Load relations
        $variables = $task->variables()->get()->keyBy('code');
        $steps = $task->steps()->with(['actionType', 'bindings', 'call'])->get();

        // Variables: code uniqueness already enforced by db, validate identifier
        foreach ($variables as $code => $var) {
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $code)) {
                $errors[] = ['entity' => 'variable', 'code' => $code, 'field' => 'code', 'message' => 'Invalid identifier'];
            }
        }

        // Steps: check order continuity
        $expectedIndex = 0;
        foreach ($steps as $i => $step) {
            if ($step->order_index !== $expectedIndex) {
                $errors[] = ['entity' => 'step', 'index' => $i, 'field' => 'order_index', 'message' => 'Order must be continuous starting at 0'];
                $expectedIndex = $step->order_index; // resync
            }
            $expectedIndex++;
        }

        // Validate each step parameters against action type schema
        $availableOutputs = []; // alias.field => type
        foreach ($steps as $i => $step) {
            if (!$step->actionType || !$step->actionType->is_active) {
                $errors[] = ['entity' => 'step', 'index' => $i, 'field' => 'action_type_id', 'message' => 'Invalid or inactive action type'];
                continue;
            }
            $inputSchema = $step->actionType->input_schema ?? [];
            $outputSchema = $step->actionType->output_schema ?? [];

            // schema presence and types
            foreach ($inputSchema as $field) {
                $name = $field['name'] ?? null;
                $required = $field['required'] ?? false;
                $type = $field['type'] ?? null;
                if (!$name || !$type) { continue; }

                $has = is_array($step->parameters) && array_key_exists($name, $step->parameters);
                if ($required && !$has) {
                    $errors[] = ['entity' => 'step', 'index' => $i, 'field' => "parameters.$name", 'message' => 'Required parameter missing'];
                    continue;
                }
                if ($has) {
                    $value = $step->parameters[$name];
                    $refCheck = $this->validateParameterValue($value, $type, $variables->all(), $availableOutputs);
                    if ($refCheck !== true) {
                        $errors[] = ['entity' => 'step', 'index' => $i, 'field' => "parameters.$name", 'message' => $refCheck];
                    }
                }
            }

            // Bindings
            foreach ($step->bindings as $binding) {
                $outField = $binding->from_output_field;
                $decl = $this->findSchemaField($outputSchema, $outField);
                if (!$decl) {
                    $errors[] = ['entity' => 'binding', 'step' => $i, 'field' => 'from_output_field', 'message' => 'Output field not declared'];
                    continue;
                }
                $targetCode = $binding->to_variable_code;
                $target = $variables[$targetCode] ?? null;
                if (!$target) {
                    $errors[] = ['entity' => 'binding', 'step' => $i, 'field' => 'to_variable_code', 'message' => 'Target variable does not exist'];
                    continue;
                }
                if (!in_array($target->direction, ['output','inout'], true)) {
                    $errors[] = ['entity' => 'binding', 'step' => $i, 'field' => 'to_variable_code', 'message' => 'Target variable not writable'];
                    continue;
                }
                $fromType = $decl['type'] ?? 'json';
                $transform = $binding->transform;
                $toType = $target->type;
                if ($transform) {
                    $exprInputTypes = $this->buildExprTypes($variables->all(), $availableOutputs);
                    $exprVal = app(ExpressionValidator::class)->validateTransform($transform, $exprInputTypes);
                    if (!$exprVal['ok']) {
                        $errors[] = ['entity' => 'binding', 'step' => $i, 'field' => 'transform', 'message' => $exprVal['error']];
                    } else {
                        $fromType = $exprVal['returnType'];
                    }
                }
                if (!$this->isTypeCompatible($fromType, $toType)) {
                    $errors[] = ['entity' => 'binding', 'step' => $i, 'message' => "Type mismatch: $fromType -> $toType"]; 
                }
            }

            // Nested task call
            foreach ($step->call as $call) {
                // Only allow calling published snapshots (policy enforcement handled elsewhere)
                if (!$call->called_task_slug || !$call->called_task_version) {
                    $errors[] = ['entity' => 'call', 'step' => $i, 'message' => 'Called task slug/version required'];
                }
                // args_map/result_map type checks require resolving the child task snapshot externally (out of scope for now),
                // but we enforce presence of required structures
            }

            // Register available outputs for later steps
            if ($step->alias) {
                foreach ($outputSchema as $f) {
                    if (!empty($f['name']) && !empty($f['type'])) {
                        $availableOutputs[$step->alias.'.'.$f['name']] = $f['type'];
                    }
                }
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    private function validateParameterValue($value, string $expectedType, array $variables, array $availableOutputs)
    {
        // Placeholder (variable or step output)
        if (is_string($value) && preg_match('/^\{\{\s*([^}]+)\s*\}\}$/', $value, $m)) {
            $ref = $m[1];
            // step output reference alias.field
            if (strpos($ref, 'step.') === 0) {
                $key = substr($ref, strlen('step.'));
                if (!array_key_exists($key, $availableOutputs)) {
                    return 'Unresolved step output reference or future step reference';
                }
                $fromType = $availableOutputs[$key];
                return $this->isTypeCompatible($fromType, $expectedType) ? true : "Type mismatch: $fromType -> $expectedType";
            }
            // variable reference
            $varCode = str_replace(['var.'], '', $ref);
            if (!isset($variables[$varCode])) {
                return 'Unresolved variable reference';
            }
            $fromType = $variables[$varCode]->type;
            return $this->isTypeCompatible($fromType, $expectedType) ? true : "Type mismatch: $fromType -> $expectedType";
        }

        // Constant primitive: best-effort type inference
        if (is_string($value)) { return $this->isTypeCompatible('string', $expectedType) ? true : 'Expected '.$expectedType; }
        if (is_int($value) || is_float($value)) { return $this->isTypeCompatible('number', $expectedType) ? true : 'Expected '.$expectedType; }
        if (is_bool($value)) { return $this->isTypeCompatible('boolean', $expectedType) ? true : 'Expected '.$expectedType; }
        if (is_array($value)) { return $this->isTypeCompatible('array', $expectedType) ? true : 'Expected '.$expectedType; }
        if ($value === null) { return true; }

        return 'Unsupported parameter value type';
    }

    private function findSchemaField(array $schema, string $name): ?array
    {
        foreach ($schema as $field) {
            if (($field['name'] ?? null) === $name) { return $field; }
        }
        return null;
    }

    private function isTypeCompatible(string $from, string $to): bool
    {
        if ($from === $to) { return true; }
        if (($from === 'number' && $to === 'string') || ($from === 'string' && $to === 'number')) { return true; }
        return false;
    }

    private function buildExprTypes(array $variables, array $availableOutputs): array
    {
        $types = [];
        foreach ($variables as $v) { $types[$v->code] = $v->type; $types['var.'.$v->code] = $v->type; }
        foreach ($availableOutputs as $k => $t) { $types['step.'.$k] = $t; }
        return $types;
    }
}




