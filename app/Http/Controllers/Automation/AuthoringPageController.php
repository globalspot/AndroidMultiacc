<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\AutomationTask;
use Illuminate\View\View;

class AuthoringPageController extends Controller
{
    public function list(): View
    {
        $tasks = AutomationTask::query()
            ->orderByDesc('updated_at')
            ->paginate(20);
        return view('automation.list', compact('tasks'));
    }

    public function edit(AutomationTask $task): View
    {
        $task->load(['variables', 'steps.bindings', 'steps.call', 'steps.actionType']);
        return view('automation.editor', compact('task'));
    }
}


