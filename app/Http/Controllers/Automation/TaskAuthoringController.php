<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationTask;
use App\Models\TaskStep;
use App\Models\TaskStepBinding;
use App\Models\TaskStepCall;
use App\Models\TaskVariable;
use App\Services\Automation\ConstructorValidator;
use App\Services\Automation\DraftService;
use App\Services\Automation\LintService;
use App\Services\Automation\PublishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskAuthoringController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->validate([
            'workspace_id' => 'required|integer',
            'owner_user_id' => 'required|integer',
            'name' => 'required|string',
            'slug' => 'required|string',
            'description' => 'nullable|string',
        ]);
        $task = AutomationTask::create($data);
        return response()->json($task, 201);
    }

    public function updateMeta(Request $request, AutomationTask $task)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
        ]);
        $task->update($data);
        return response()->json($task);
    }

    public function addVariable(Request $request, AutomationTask $task)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'direction' => 'required|in:input,output,inout',
            'type' => 'required|string',
            'required' => 'boolean',
            'default' => 'nullable',
            'validation_rules' => 'nullable|array',
            'description' => 'nullable|string',
        ]);
        $data['task_id'] = $task->id;
        $var = TaskVariable::create($data);
        return response()->json($var, 201);
    }

    public function updateVariable(Request $request, TaskVariable $variable)
    {
        $data = $request->validate([
            'direction' => 'sometimes|in:input,output,inout',
            'type' => 'sometimes|string',
            'required' => 'boolean',
            'default' => 'nullable',
            'validation_rules' => 'nullable|array',
            'description' => 'nullable|string',
        ]);
        $variable->update($data);
        return response()->json($variable);
    }

    public function deleteVariable(TaskVariable $variable)
    {
        $variable->delete();
        return response()->json(['ok' => true]);
    }

    public function addStep(Request $request, AutomationTask $task)
    {
        $data = $request->validate([
            'action_type_id' => 'required|integer',
            'order_index' => 'required|integer|min:0',
            'alias' => 'nullable|string',
            'parameters' => 'nullable|array',
            'condition' => 'nullable|string',
            'policies' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);
        $data['task_id'] = $task->id;
        $step = TaskStep::create($data);
        return response()->json($step, 201);
    }

    public function updateStep(Request $request, TaskStep $step)
    {
        $data = $request->validate([
            'alias' => 'nullable|string',
            'parameters' => 'nullable|array',
            'condition' => 'nullable|string',
            'policies' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);
        $step->update($data);
        return response()->json($step);
    }

    public function deleteStep(TaskStep $step)
    {
        DB::transaction(function () use ($step) {
            TaskStepBinding::where('task_step_id', $step->id)->delete();
            TaskStepCall::where('task_step_id', $step->id)->delete();
            $step->delete();
        });
        return response()->json(['ok' => true]);
    }

    public function reorderSteps(Request $request, AutomationTask $task)
    {
        $data = $request->validate([
            'order' => 'required|array',
        ]);
        DB::transaction(function () use ($data) {
            foreach ($data['order'] as $idx => $stepId) {
                TaskStep::where('id', $stepId)->update(['order_index' => $idx]);
            }
        });
        return response()->json(['ok' => true]);
    }

    public function bindOutput(Request $request, TaskStep $step)
    {
        $data = $request->validate([
            'from_output_field' => 'required|string',
            'to_variable_code' => 'required|string',
            'transform' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $data['task_step_id'] = $step->id;
        $binding = TaskStepBinding::create($data);
        return response()->json($binding, 201);
    }

    public function updateBinding(Request $request, TaskStepBinding $binding)
    {
        $data = $request->validate([
            'from_output_field' => 'sometimes|string',
            'to_variable_code' => 'sometimes|string',
            'transform' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $binding->update($data);
        return response()->json($binding);
    }

    public function deleteBinding(TaskStepBinding $binding)
    {
        $binding->delete();
        return response()->json(['ok' => true]);
    }

    public function setNestedCall(Request $request, TaskStep $step)
    {
        $data = $request->validate([
            'called_task_slug' => 'required|string',
            'called_task_version' => 'required|integer',
            'allow_call_draft' => 'boolean',
            'args_map' => 'nullable|array',
            'result_map' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        $data['task_step_id'] = $step->id;
        $call = TaskStepCall::updateOrCreate(['task_step_id' => $step->id], $data);
        return response()->json($call);
    }

    public function validateTask(AutomationTask $task, ConstructorValidator $validator)
    {
        return response()->json($validator->validateDraft($task));
    }

    public function lintTask(AutomationTask $task, LintService $linter)
    {
        return response()->json($linter->lint($task));
    }

    public function publish(AutomationTask $task, PublishService $publisher)
    {
        $res = $publisher->publish($task);
        $code = $res['ok'] ? 200 : 422;
        return response()->json($res, $code);
    }

    public function createDraftFromVersion(AutomationTask $task, TaskVersion $version, DraftService $drafts)
    {
        $drafts->createDraftFromVersion($task, $version);
        return response()->json(['ok' => true]);
    }

    public function publishedTasks()
    {
        $tasks = AutomationTask::query()
            ->where('status', 'published')
            ->orderBy('slug')
            ->get(['id', 'slug', 'current_version', 'published_version_id']);
        return response()->json($tasks);
    }

    public function exportSnapshot(AutomationTask $task)
    {
        $task->load(['variables', 'steps.bindings', 'steps.call', 'steps.actionType']);
        $snapshot = [
            'meta' => [
                'name' => $task->name,
                'slug' => $task->slug,
                'description' => $task->description,
                'version' => $task->current_version,
                'status' => $task->status,
            ],
            'variables' => $task->variables->map->only(['code','direction','type','required','default','validation_rules','description'])->values(),
            'steps' => $task->steps->map(function ($s) {
                return [
                    'action_type_code' => $s->actionType->code ?? null,
                    'action_type_id' => $s->action_type_id,
                    'order_index' => $s->order_index,
                    'alias' => $s->alias,
                    'parameters' => $s->parameters,
                    'condition' => $s->condition,
                    'policies' => $s->policies,
                    'bindings' => $s->bindings->map->only(['from_output_field','to_variable_code','transform','notes'])->values(),
                    'call' => $s->call->map->only(['called_task_slug','called_task_version','allow_call_draft','args_map','result_map','notes'])->values(),
                ];
            })->values(),
        ];
        return response()->json($snapshot);
    }

    public function importSnapshot(Request $request)
    {
        $data = $request->validate(['snapshot' => 'required|array']);
        $snap = $data['snapshot'];
        $task = AutomationTask::create([
            'workspace_id' => $request->integer('workspace_id', auth()->id()),
            'owner_user_id' => auth()->id(),
            'name' => $snap['meta']['name'] ?? 'Imported',
            'slug' => $snap['meta']['slug'] ?? ('import-'.\Illuminate\Support\Str::uuid()),
            'description' => $snap['meta']['description'] ?? null,
            'status' => 'draft',
        ]);
        foreach ($snap['variables'] ?? [] as $v) {
            TaskVariable::create([
                'task_id' => $task->id,
                'code' => $v['code'],
                'direction' => $v['direction'],
                'type' => $v['type'],
                'required' => $v['required'] ?? false,
                'default' => $v['default'] ?? null,
                'validation_rules' => $v['validation_rules'] ?? null,
                'description' => $v['description'] ?? null,
            ]);
        }
        foreach ($snap['steps'] ?? [] as $s) {
            $step = TaskStep::create([
                'task_id' => $task->id,
                'action_type_id' => $s['action_type_id'] ?? 0,
                'order_index' => $s['order_index'] ?? 0,
                'alias' => $s['alias'] ?? null,
                'parameters' => $s['parameters'] ?? [],
                'condition' => $s['condition'] ?? null,
                'policies' => $s['policies'] ?? [],
            ]);
            foreach ($s['bindings'] ?? [] as $b) {
                TaskStepBinding::create([
                    'task_step_id' => $step->id,
                    'from_output_field' => $b['from_output_field'],
                    'to_variable_code' => $b['to_variable_code'],
                    'transform' => $b['transform'] ?? null,
                    'notes' => $b['notes'] ?? null,
                ]);
            }
            foreach ($s['call'] ?? [] as $c) {
                TaskStepCall::create([
                    'task_step_id' => $step->id,
                    'called_task_slug' => $c['called_task_slug'] ?? null,
                    'called_task_version' => $c['called_task_version'] ?? 0,
                    'allow_call_draft' => $c['allow_call_draft'] ?? false,
                    'args_map' => $c['args_map'] ?? [],
                    'result_map' => $c['result_map'] ?? [],
                    'notes' => $c['notes'] ?? null,
                ]);
            }
        }
        return response()->json(['ok' => true, 'task_id' => $task->id], 201);
    }
}


