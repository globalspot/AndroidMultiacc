<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\AutomationMacro;
use App\Models\MacroVariable;
use App\Models\MacroTimer;
use App\Models\MacroNode;
use App\Models\MacroConnection;

class AutomationMacrosController extends Controller
{
    public function index()
    {
        $macros = AutomationMacro::where('user_id', Auth::id())
            ->with(['variables', 'timers'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('automation.macros.index', compact('macros'));
    }

    public function create()
    {
        return view('automation.macros.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'required|array',
            'connections' => 'required|array',
            'variables' => 'array',
            'timers' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $macro = AutomationMacro::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'nodes' => $request->nodes,
            'connections' => $request->connections,
        ]);

        // Save variables
        if ($request->variables) {
            foreach ($request->variables as $variable) {
                MacroVariable::create([
                    'macro_id' => $macro->id,
                    'name' => $variable['name'],
                    'type' => $variable['type'],
                    'default_value' => $variable['default_value'] ?? null,
                    'description' => $variable['description'] ?? null,
                ]);
            }
        }

        // Save timers
        if ($request->timers) {
            foreach ($request->timers as $timer) {
                MacroTimer::create([
                    'macro_id' => $macro->id,
                    'name' => $timer['name'],
                    'delay' => $timer['delay'],
                    'unit' => $timer['unit'],
                    'description' => $timer['description'] ?? null,
                ]);
            }
        }

        return response()->json(['success' => true, 'macro_id' => $macro->id]);
    }

    public function show(AutomationMacro $macro)
    {
        $this->authorize('view', $macro);

        $macro->load(['variables', 'timers']);
        
        return view('automation.macros.show', compact('macro'));
    }

    public function edit(AutomationMacro $macro)
    {
        $this->authorize('update', $macro);

        $macro->load(['variables', 'timers']);
        
        return view('automation.macros.edit', compact('macro'));
    }

    public function update(Request $request, AutomationMacro $macro)
    {
        $this->authorize('update', $macro);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'nodes' => 'required|array',
            'connections' => 'required|array',
            'variables' => 'array',
            'timers' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $macro->update([
            'name' => $request->name,
            'description' => $request->description,
            'nodes' => $request->nodes,
            'connections' => $request->connections,
        ]);

        // Update variables
        $macro->variables()->delete();
        if ($request->variables) {
            foreach ($request->variables as $variable) {
                MacroVariable::create([
                    'macro_id' => $macro->id,
                    'name' => $variable['name'],
                    'type' => $variable['type'],
                    'default_value' => $variable['default_value'] ?? null,
                    'description' => $variable['description'] ?? null,
                ]);
            }
        }

        // Update timers
        $macro->timers()->delete();
        if ($request->timers) {
            foreach ($request->timers as $timer) {
                MacroTimer::create([
                    'macro_id' => $macro->id,
                    'name' => $timer['name'],
                    'delay' => $timer['delay'],
                    'unit' => $timer['unit'],
                    'description' => $timer['description'] ?? null,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(AutomationMacro $macro)
    {
        $this->authorize('delete', $macro);

        $macro->delete();

        return response()->json(['success' => true]);
    }

    public function execute(AutomationMacro $macro)
    {
        $this->authorize('execute', $macro);

        // Execute the macro logic here
        // This would involve processing the nodes and connections
        // and executing the actual automation tasks

        return response()->json(['success' => true, 'message' => 'Macro executed successfully']);
    }

    public function getActionTypes()
    {
        $actionTypes = [
            'device' => [
                'label' => 'Device Operations',
                'icon' => 'mobile-alt',
                'actions' => [
                    'create_device' => 'Create Device',
                    'select_device' => 'Select Device',
                    'start_device' => 'Start Device',
                    'stop_device' => 'Stop Device',
                    'restart_device' => 'Restart Device',
                    'install_language' => 'Install Language',
                    'set_resolution' => 'Set Screen Resolution',
                ]
            ],
            'file' => [
                'label' => 'File Operations',
                'icon' => 'file',
                'actions' => [
                    'read_file' => 'Read File',
                    'write_file' => 'Write File',
                    'delete_file' => 'Delete File',
                    'copy_file' => 'Copy File',
                    'move_file' => 'Move File',
                ]
            ],
            'sms' => [
                'label' => 'SMS Services',
                'icon' => 'comment',
                'actions' => [
                    'send_sms' => 'Send SMS',
                    'read_sms' => 'Read SMS',
                    'delete_sms' => 'Delete SMS',
                ]
            ],
            'database' => [
                'label' => 'Database Operations',
                'icon' => 'database',
                'actions' => [
                    'query_database' => 'Query Database',
                    'insert_record' => 'Insert Record',
                    'update_record' => 'Update Record',
                    'delete_record' => 'Delete Record',
                ]
            ],
            'text' => [
                'label' => 'Text Processing',
                'icon' => 'edit',
                'actions' => [
                    'extract_text' => 'Extract Text',
                    'replace_text' => 'Replace Text',
                    'split_text' => 'Split Text',
                    'join_text' => 'Join Text',
                ]
            ],
            'json' => [
                'label' => 'JSON/XML Processing',
                'icon' => 'code',
                'actions' => [
                    'parse_json' => 'Parse JSON',
                    'generate_json' => 'Generate JSON',
                    'extract_value' => 'Extract Value',
                    'validate_json' => 'Validate JSON',
                ]
            ],
            'image' => [
                'label' => 'Image Processing',
                'icon' => 'image',
                'actions' => [
                    'resize_image' => 'Resize Image',
                    'crop_image' => 'Crop Image',
                    'convert_format' => 'Convert Format',
                    'apply_filter' => 'Apply Filter',
                ]
            ],
            'variable' => [
                'label' => 'Variable Processing',
                'icon' => 'cogs',
                'actions' => [
                    'set_variable' => 'Set Variable',
                    'get_variable' => 'Get Variable',
                    'increment_variable' => 'Increment Variable',
                    'decrement_variable' => 'Decrement Variable',
                ]
            ],
            'content' => [
                'label' => 'Content Creation',
                'icon' => 'desktop',
                'actions' => [
                    'generate_text' => 'Generate Text',
                    'create_image' => 'Create Image',
                    'generate_report' => 'Generate Report',
                ]
            ],
            'timer' => [
                'label' => 'Timers & Delays',
                'icon' => 'clock',
                'actions' => [
                    'wait' => 'Wait',
                    'schedule' => 'Schedule',
                    'repeat' => 'Repeat',
                ]
            ],
        ];

        return response()->json($actionTypes);
    }
}

