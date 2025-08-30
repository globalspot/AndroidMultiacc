<?php

namespace App\Services\Automation;

use App\Models\AutomationTask;
use App\Models\TaskStep;
use App\Models\TaskStepBinding;
use App\Models\TaskStepCall;
use App\Models\TaskVariable;
use App\Models\TaskVersion;
use App\Models\ValidationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishService
{
    public function publish(AutomationTask $task): array
    {
        $validator = app(ConstructorValidator::class);
        $result = $validator->validateDraft($task);
        if (!empty($result['errors'])) {
            $this->logValidation($task, 'fail', $result);
            return ['ok' => false, 'errors' => $result['errors'], 'warnings' => $result['warnings'] ?? []];
        }

        // TODO: cycle detection across nested calls could be added here

        return DB::transaction(function () use ($task, $result) {
            $nextVersion = (int)($task->current_version ?? 0) + 1;
            $snapshotMeta = [
                'name' => $task->name,
                'slug' => $task->slug,
                'description' => $task->description,
                'status' => 'published',
            ];

            $version = TaskVersion::create([
                'task_id' => $task->id,
                'version_number' => $nextVersion,
                'snapshot_meta' => $snapshotMeta,
                'published_at' => now(),
                'validation_report' => $result,
            ]);

            // Copy variables
            $vars = TaskVariable::where('task_id', $task->id)->get();
            foreach ($vars as $v) {
                TaskVariable::create([
                    'task_version_id' => $version->id,
                    'code' => $v->code,
                    'direction' => $v->direction,
                    'type' => $v->type,
                    'required' => $v->required,
                    'default' => $v->default,
                    'validation_rules' => $v->validation_rules,
                    'description' => $v->description,
                ]);
            }

            // Copy steps
            $steps = TaskStep::where('task_id', $task->id)->orderBy('order_index')->get();
            $idMap = [];
            foreach ($steps as $s) {
                $new = TaskStep::create([
                    'task_version_id' => $version->id,
                    'action_type_id' => $s->action_type_id,
                    'order_index' => $s->order_index,
                    'alias' => $s->alias,
                    'parameters' => $s->parameters,
                    'condition' => $s->condition,
                    'policies' => $s->policies,
                    'metadata' => $s->metadata,
                ]);
                $idMap[$s->id] = $new->id;
            }

            // Copy bindings and calls
            foreach ($steps as $s) {
                $newStepId = $idMap[$s->id];
                foreach (TaskStepBinding::where('task_step_id', $s->id)->get() as $b) {
                    TaskStepBinding::create([
                        'task_step_id' => $newStepId,
                        'from_output_field' => $b->from_output_field,
                        'to_variable_code' => $b->to_variable_code,
                        'transform' => $b->transform,
                        'notes' => $b->notes,
                    ]);
                }
                foreach (TaskStepCall::where('task_step_id', $s->id)->get() as $c) {
                    TaskStepCall::create([
                        'task_step_id' => $newStepId,
                        'called_task_slug' => $c->called_task_slug,
                        'called_task_version' => $c->called_task_version,
                        'allow_call_draft' => $c->allow_call_draft,
                        'args_map' => $c->args_map,
                        'result_map' => $c->result_map,
                        'notes' => $c->notes,
                    ]);
                }
            }

            // Update task status and version pointers
            $task->status = 'published';
            $task->current_version = $nextVersion;
            $task->published_version_id = $version->id;
            $task->save();

            $this->logValidation($task, 'pass', $result, $version->id);
            return ['ok' => true, 'version_id' => $version->id, 'version_number' => $nextVersion];
        });
    }

    private function logValidation(AutomationTask $task, string $status, array $report, ?int $taskVersionId = null): void
    {
        ValidationLog::create([
            'task_id' => $task->id,
            'task_version_id' => $taskVersionId,
            'status' => $status === 'pass' ? 'pass' : 'fail',
            'errors' => $report['errors'] ?? [],
            'warnings' => $report['warnings'] ?? [],
        ]);
    }
}




