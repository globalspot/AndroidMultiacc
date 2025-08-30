<?php

namespace App\Http\Controllers\Automation;

use App\Http\Controllers\Controller;
use App\Models\ActionType;
use Illuminate\Http\Request;

class ActionTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = ActionType::query();
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }
        if ($request->filled('workspace_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('scope', 'global')
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('scope', 'workspace')
                         ->where('workspace_id', $request->integer('workspace_id'));
                  });
            });
        }
        return response()->json($query->orderBy('code')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope' => 'required|in:global,workspace',
            'workspace_id' => 'nullable|integer',
            'code' => 'required|string',
            'label' => 'required|string',
            'description' => 'nullable|string',
            'input_schema' => 'nullable|array',
            'output_schema' => 'nullable|array',
            'ui_hints' => 'nullable|array',
            'is_active' => 'boolean',
        ]);
        $type = ActionType::create($data);
        return response()->json($type, 201);
    }

    public function update(Request $request, ActionType $actionType)
    {
        $data = $request->validate([
            'label' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'input_schema' => 'nullable|array',
            'output_schema' => 'nullable|array',
            'ui_hints' => 'nullable|array',
            'is_active' => 'boolean',
        ]);
        $actionType->update($data);
        return response()->json($actionType);
    }
}


