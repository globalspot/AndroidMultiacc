<?php

namespace App\Services\Automation;

use App\Models\AutomationTask;

class LintService
{
    public function lint(AutomationTask $task): array
    {
        $warnings = [];
        $variables = $task->variables()->get();
        $steps = $task->steps()->with(['bindings', 'actionType'])->get();

        // Unused variables
        $usedVars = [];
        foreach ($steps as $s) {
            $params = is_array($s->parameters) ? $s->parameters : [];
            foreach ($params as $v) {
                if (is_string($v) && preg_match('/^\{\{\s*([^}]+)\s*\}\}$/', $v, $m)) {
                    $key = $m[1];
                    $key = str_starts_with($key, 'var.') ? substr($key, 4) : $key;
                    $usedVars[$key] = true;
                }
            }
            foreach ($s->bindings as $b) {
                $usedVars[$b->to_variable_code] = true;
            }
        }
        foreach ($variables as $v) {
            if (!isset($usedVars[$v->code])) {
                $warnings[] = ['entity' => 'variable', 'code' => $v->code, 'message' => 'Variable appears unused'];
            }
        }

        // Steps that produce outputs but have no bindings or downstream refs
        $producingSteps = [];
        foreach ($steps as $idx => $s) {
            $out = $s->actionType?->output_schema ?? [];
            if (!empty($out)) {
                $producingSteps[$s->alias ?? ('#'.$idx)] = $s;
            }
        }
        // If step has outputs but has zero bindings, warn
        foreach ($producingSteps as $alias => $s) {
            if ($s->bindings()->count() === 0) {
                $warnings[] = ['entity' => 'step', 'alias' => $alias, 'message' => 'Step produces outputs but has no bindings'];
            }
        }

        return ['warnings' => $warnings];
    }
}




