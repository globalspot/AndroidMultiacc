<?php

namespace App\Services\Automation;

use App\Models\AutomationTask;
use App\Models\TaskStep;
use App\Models\TaskStepBinding;
use App\Models\TaskStepCall;
use App\Models\TaskVariable;
use App\Models\TaskVersion;
use Illuminate\Support\Facades\DB;

class DraftService
{
    public function createDraftFromVersion(AutomationTask $task, TaskVersion $version): AutomationTask
    {
        return DB::transaction(function () use ($task, $version) {
            // Clear existing draft content
            TaskVariable::where('task_id', $task->id)->delete();
            $existingSteps = TaskStep::where('task_id', $task->id)->pluck('id');
            if ($existingSteps->isNotEmpty()) {
                TaskStepBinding::whereIn('task_step_id', $existingSteps)->delete();
                TaskStepCall::whereIn('task_step_id', $existingSteps)->delete();
                TaskStep::whereIn('id', $existingSteps)->delete();
            }

            // Copy from snapshot to draft
            $vars = TaskVariable::where('task_version_id', $version->id)->get();
            foreach ($vars as $v) {
                TaskVariable::create([
                    'task_id' => $task->id,
                    'code' => $v->code,
                    'direction' => $v->direction,
                    'type' => $v->type,
                    'required' => $v->required,
                    'default' => $v->default,
                    'validation_rules' => $v->validation_rules,
                    'description' => $v->description,
                ]);
            }

            $steps = TaskStep::where('task_version_id', $version->id)->orderBy('order_index')->get();
            $idMap = [];
            foreach ($steps as $s) {
                $new = TaskStep::create([
                    'task_id' => $task->id,
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

            $task->status = 'draft';
            $task->save();

            return $task;
        });
    }
}




