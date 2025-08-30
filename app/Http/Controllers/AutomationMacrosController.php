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
                    'create_device' => [
                        'label' => 'Create Device',
                        'fields' => [
                            'device_name' => [
                                'type' => 'text',
                                'label' => 'Device Name',
                                'placeholder' => 'Enter device name',
                                'required' => true
                            ],
                            'device_type' => [
                                'type' => 'select',
                                'label' => 'Device Type',
                                'options' => [
                                    'android' => 'Android',
                                    'ios' => 'iOS',
                                    'windows' => 'Windows'
                                ],
                                'required' => true
                            ],
                            'auto_start' => [
                                'type' => 'checkbox',
                                'label' => 'Auto Start After Creation',
                                'default' => false
                            ]
                        ]
                    ],
                    'select_device' => [
                        'label' => 'Select Device',
                        'fields' => [
                            'device_id' => [
                                'type' => 'select',
                                'label' => 'Device',
                                'options' => 'dynamic', // Will be populated from available devices
                                'required' => true
                            ]
                        ]
                    ],
                    'start_device' => [
                        'label' => 'Start Device',
                        'fields' => [
                            'device_id' => [
                                'type' => 'select',
                                'label' => 'Device',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'wait_for_boot' => [
                                'type' => 'checkbox',
                                'label' => 'Wait for Boot Complete',
                                'default' => true
                            ]
                        ]
                    ],
                    'stop_device' => [
                        'label' => 'Stop Device',
                        'fields' => [
                            'device_id' => [
                                'type' => 'select',
                                'label' => 'Device',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'force_stop' => [
                                'type' => 'checkbox',
                                'label' => 'Force Stop',
                                'default' => false
                            ]
                        ]
                    ],
                    'restart_device' => [
                        'label' => 'Restart Device',
                        'fields' => [
                            'device_id' => [
                                'type' => 'select',
                                'label' => 'Device',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'wait_for_boot' => [
                                'type' => 'checkbox',
                                'label' => 'Wait for Boot Complete',
                                'default' => true
                            ]
                        ]
                    ],
                    'install_language' => [
                        'label' => 'Install Language',
                        'fields' => [
                            'device_id' => [
                                'type' => 'select',
                                'label' => 'Device',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'language_code' => [
                                'type' => 'select',
                                'label' => 'Language',
                                'options' => [
                                    'en' => 'English',
                                    'es' => 'Spanish',
                                    'fr' => 'French',
                                    'de' => 'German',
                                    'ru' => 'Russian',
                                    'zh' => 'Chinese'
                                ],
                                'required' => true
                            ]
                        ]
                    ],
                    'set_resolution' => [
                        'label' => 'Set Screen Resolution',
                        'fields' => [
                            'device_id' => [
                                'type' => 'select',
                                'label' => 'Device',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'width' => [
                                'type' => 'number',
                                'label' => 'Width',
                                'placeholder' => '1920',
                                'min' => 320,
                                'max' => 3840,
                                'required' => true
                            ],
                            'height' => [
                                'type' => 'number',
                                'label' => 'Height',
                                'placeholder' => '1080',
                                'min' => 240,
                                'max' => 2160,
                                'required' => true
                            ]
                        ]
                    ]
                ]
            ],
            'file' => [
                'label' => 'File Operations',
                'icon' => 'file',
                'actions' => [
                    'read_file' => [
                        'label' => 'Read File',
                        'fields' => [
                            'file_path' => [
                                'type' => 'text',
                                'label' => 'File Path',
                                'placeholder' => '/path/to/file.txt',
                                'required' => true
                            ],
                            'encoding' => [
                                'type' => 'select',
                                'label' => 'Encoding',
                                'options' => [
                                    'utf8' => 'UTF-8',
                                    'ascii' => 'ASCII',
                                    'latin1' => 'Latin-1'
                                ],
                                'default' => 'utf8'
                            ]
                        ]
                    ],
                    'write_file' => [
                        'label' => 'Write File',
                        'fields' => [
                            'file_path' => [
                                'type' => 'text',
                                'label' => 'File Path',
                                'placeholder' => '/path/to/file.txt',
                                'required' => true
                            ],
                            'content' => [
                                'type' => 'textarea',
                                'label' => 'Content',
                                'placeholder' => 'Enter file content',
                                'required' => true
                            ],
                            'overwrite' => [
                                'type' => 'checkbox',
                                'label' => 'Overwrite if exists',
                                'default' => false
                            ]
                        ]
                    ],
                    'delete_file' => [
                        'label' => 'Delete File',
                        'fields' => [
                            'file_path' => [
                                'type' => 'text',
                                'label' => 'File Path',
                                'placeholder' => '/path/to/file.txt',
                                'required' => true
                            ],
                            'confirm_delete' => [
                                'type' => 'checkbox',
                                'label' => 'Confirm deletion',
                                'default' => true
                            ]
                        ]
                    ],
                    'copy_file' => [
                        'label' => 'Copy File',
                        'fields' => [
                            'source_path' => [
                                'type' => 'text',
                                'label' => 'Source Path',
                                'placeholder' => '/source/file.txt',
                                'required' => true
                            ],
                            'destination_path' => [
                                'type' => 'text',
                                'label' => 'Destination Path',
                                'placeholder' => '/destination/file.txt',
                                'required' => true
                            ]
                        ]
                    ],
                    'move_file' => [
                        'label' => 'Move File',
                        'fields' => [
                            'source_path' => [
                                'type' => 'text',
                                'label' => 'Source Path',
                                'placeholder' => '/source/file.txt',
                                'required' => true
                            ],
                            'destination_path' => [
                                'type' => 'text',
                                'label' => 'Destination Path',
                                'placeholder' => '/destination/file.txt',
                                'required' => true
                            ]
                        ]
                    ]
                ]
            ],
            'sms' => [
                'label' => 'SMS Services',
                'icon' => 'comment',
                'actions' => [
                    'send_sms' => [
                        'label' => 'Send SMS',
                        'fields' => [
                            'phone_number' => [
                                'type' => 'text',
                                'label' => 'Phone Number',
                                'placeholder' => '+1234567890',
                                'required' => true
                            ],
                            'message' => [
                                'type' => 'textarea',
                                'label' => 'Message',
                                'placeholder' => 'Enter SMS message',
                                'required' => true
                            ],
                            'priority' => [
                                'type' => 'select',
                                'label' => 'Priority',
                                'options' => [
                                    'low' => 'Low',
                                    'normal' => 'Normal',
                                    'high' => 'High'
                                ],
                                'default' => 'normal'
                            ]
                        ]
                    ],
                    'read_sms' => [
                        'label' => 'Read SMS',
                        'fields' => [
                            'phone_number' => [
                                'type' => 'text',
                                'label' => 'Phone Number (optional)',
                                'placeholder' => '+1234567890'
                            ],
                            'limit' => [
                                'type' => 'number',
                                'label' => 'Message Limit',
                                'placeholder' => '10',
                                'min' => 1,
                                'max' => 100,
                                'default' => 10
                            ]
                        ]
                    ],
                    'delete_sms' => [
                        'label' => 'Delete SMS',
                        'fields' => [
                            'sms_id' => [
                                'type' => 'text',
                                'label' => 'SMS ID',
                                'placeholder' => 'Enter SMS ID',
                                'required' => true
                            ]
                        ]
                    ]
                ]
            ],
            'database' => [
                'label' => 'Database Operations',
                'icon' => 'database',
                'actions' => [
                    'query_database' => [
                        'label' => 'Query Database',
                        'fields' => [
                            'connection_name' => [
                                'type' => 'select',
                                'label' => 'Database Connection',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'sql_query' => [
                                'type' => 'textarea',
                                'label' => 'SQL Query',
                                'placeholder' => 'SELECT * FROM table WHERE condition',
                                'required' => true
                            ],
                            'parameters' => [
                                'type' => 'json',
                                'label' => 'Query Parameters (JSON)',
                                'placeholder' => '{"param1": "value1"}'
                            ]
                        ]
                    ],
                    'insert_record' => [
                        'label' => 'Insert Record',
                        'fields' => [
                            'connection_name' => [
                                'type' => 'select',
                                'label' => 'Database Connection',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'table_name' => [
                                'type' => 'text',
                                'label' => 'Table Name',
                                'placeholder' => 'users',
                                'required' => true
                            ],
                            'data' => [
                                'type' => 'json',
                                'label' => 'Record Data (JSON)',
                                'placeholder' => '{"column1": "value1", "column2": "value2"}',
                                'required' => true
                            ]
                        ]
                    ],
                    'update_record' => [
                        'label' => 'Update Record',
                        'fields' => [
                            'connection_name' => [
                                'type' => 'select',
                                'label' => 'Database Connection',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'table_name' => [
                                'type' => 'text',
                                'label' => 'Table Name',
                                'placeholder' => 'users',
                                'required' => true
                            ],
                            'where_condition' => [
                                'type' => 'text',
                                'label' => 'WHERE Condition',
                                'placeholder' => 'id = 1',
                                'required' => true
                            ],
                            'data' => [
                                'type' => 'json',
                                'label' => 'Update Data (JSON)',
                                'placeholder' => '{"column1": "new_value1"}',
                                'required' => true
                            ]
                        ]
                    ],
                    'delete_record' => [
                        'label' => 'Delete Record',
                        'fields' => [
                            'connection_name' => [
                                'type' => 'select',
                                'label' => 'Database Connection',
                                'options' => 'dynamic',
                                'options' => 'dynamic',
                                'required' => true
                            ],
                            'table_name' => [
                                'type' => 'text',
                                'label' => 'Table Name',
                                'placeholder' => 'users',
                                'required' => true
                            ],
                            'where_condition' => [
                                'type' => 'text',
                                'label' => 'WHERE Condition',
                                'placeholder' => 'id = 1',
                                'required' => true
                            ]
                        ]
                    ]
                ]
            ],
            'text' => [
                'label' => 'Text Processing',
                'icon' => 'edit',
                'actions' => [
                    'extract_text' => [
                        'label' => 'Extract Text',
                        'fields' => [
                            'input_text' => [
                                'type' => 'textarea',
                                'label' => 'Input Text',
                                'placeholder' => 'Enter text to process',
                                'required' => true
                            ],
                            'pattern' => [
                                'type' => 'text',
                                'label' => 'Extraction Pattern',
                                'placeholder' => 'regex pattern or simple text',
                                'required' => true
                            ],
                            'case_sensitive' => [
                                'type' => 'checkbox',
                                'label' => 'Case Sensitive',
                                'default' => false
                            ]
                        ]
                    ],
                    'replace_text' => [
                        'label' => 'Replace Text',
                        'fields' => [
                            'input_text' => [
                                'type' => 'textarea',
                                'label' => 'Input Text',
                                'placeholder' => 'Enter text to process',
                                'required' => true
                            ],
                            'search_text' => [
                                'type' => 'text',
                                'label' => 'Search Text',
                                'placeholder' => 'Text to find',
                                'required' => true
                            ],
                            'replace_with' => [
                                'type' => 'text',
                                'label' => 'Replace With',
                                'placeholder' => 'Replacement text',
                                'required' => true
                            ],
                            'replace_all' => [
                                'type' => 'checkbox',
                                'label' => 'Replace All Occurrences',
                                'default' => true
                            ]
                        ]
                    ],
                    'split_text' => [
                        'label' => 'Split Text',
                        'fields' => [
                            'input_text' => [
                                'type' => 'textarea',
                                'label' => 'Input Text',
                                'placeholder' => 'Enter text to split',
                                'required' => true
                            ],
                            'delimiter' => [
                                'type' => 'text',
                                'label' => 'Delimiter',
                                'placeholder' => ',',
                                'default' => ','
                            ],
                            'limit' => [
                                'type' => 'number',
                                'label' => 'Split Limit',
                                'placeholder' => '0 (no limit)',
                                'min' => 0,
                                'default' => 0
                            ]
                        ]
                    ],
                    'join_text' => [
                        'label' => 'Join Text',
                        'fields' => [
                            'text_array' => [
                                'type' => 'json',
                                'label' => 'Text Array (JSON)',
                                'placeholder' => '["text1", "text2", "text3"]',
                                'required' => true
                            ],
                            'separator' => [
                                'type' => 'text',
                                'label' => 'Separator',
                                'placeholder' => ' ',
                                'default' => ' '
                            ]
                        ]
                    ]
                ]
            ],
            'json' => [
                'label' => 'JSON/XML Processing',
                'icon' => 'code',
                'actions' => [
                    'parse_json' => [
                        'label' => 'Parse JSON',
                        'fields' => [
                            'json_string' => [
                                'type' => 'textarea',
                                'label' => 'JSON String',
                                'placeholder' => '{"key": "value"}',
                                'required' => true
                            ],
                            'validate_schema' => [
                                'type' => 'checkbox',
                                'label' => 'Validate Schema',
                                'default' => false
                            ]
                        ]
                    ],
                    'generate_json' => [
                        'label' => 'Generate JSON',
                        'fields' => [
                            'data_structure' => [
                                'type' => 'json',
                                'label' => 'Data Structure',
                                'placeholder' => '{"template": "structure"}',
                                'required' => true
                            ],
                            'pretty_print' => [
                                'type' => 'checkbox',
                                'label' => 'Pretty Print',
                                'default' => true
                            ]
                        ]
                    ],
                    'extract_value' => [
                        'label' => 'Extract Value',
                        'fields' => [
                            'json_data' => [
                                'type' => 'textarea',
                                'label' => 'JSON Data',
                                'placeholder' => '{"key": "value"}',
                                'required' => true
                            ],
                            'path' => [
                                'type' => 'text',
                                'label' => 'JSON Path',
                                'placeholder' => 'key.subkey',
                                'required' => true
                            ]
                        ]
                    ],
                    'validate_json' => [
                        'label' => 'Validate JSON',
                        'fields' => [
                            'json_string' => [
                                'type' => 'textarea',
                                'label' => 'JSON String',
                                'placeholder' => '{"key": "value"}',
                                'required' => true
                            ],
                            'schema' => [
                                'type' => 'textarea',
                                'label' => 'JSON Schema (optional)',
                                'placeholder' => 'Schema definition'
                            ]
                        ]
                    ]
                ]
            ],
            'image' => [
                'label' => 'Image Processing',
                'icon' => 'image',
                'actions' => [
                    'resize_image' => [
                        'label' => 'Resize Image',
                        'fields' => [
                            'image_path' => [
                                'type' => 'text',
                                'label' => 'Image Path',
                                'placeholder' => '/path/to/image.jpg',
                                'required' => true
                            ],
                            'width' => [
                                'type' => 'number',
                                'label' => 'New Width',
                                'placeholder' => '800',
                                'min' => 1,
                                'required' => true
                            ],
                            'height' => [
                                'type' => 'number',
                                'label' => 'New Height',
                                'placeholder' => '600',
                                'min' => 1,
                                'required' => true
                            ],
                            'maintain_aspect' => [
                                'type' => 'checkbox',
                                'label' => 'Maintain Aspect Ratio',
                                'default' => true
                            ]
                        ]
                    ],
                    'crop_image' => [
                        'label' => 'Crop Image',
                        'fields' => [
                            'image_path' => [
                                'type' => 'text',
                                'label' => 'Image Path',
                                'placeholder' => '/path/to/image.jpg',
                                'required' => true
                            ],
                            'x' => [
                                'type' => 'number',
                                'label' => 'X Position',
                                'placeholder' => '0',
                                'min' => 0,
                                'required' => true
                            ],
                            'y' => [
                                'type' => 'number',
                                'label' => 'Y Position',
                                'placeholder' => '0',
                                'min' => 0,
                                'required' => true
                            ],
                            'width' => [
                                'type' => 'number',
                                'label' => 'Crop Width',
                                'placeholder' => '100',
                                'min' => 1,
                                'required' => true
                            ],
                            'height' => [
                                'type' => 'number',
                                'label' => 'Crop Height',
                                'placeholder' => '100',
                                'min' => 1,
                                'required' => true
                            ]
                        ]
                    ],
                    'convert_format' => [
                        'label' => 'Convert Format',
                        'fields' => [
                            'image_path' => [
                                'type' => 'text',
                                'label' => 'Image Path',
                                'placeholder' => '/path/to/image.jpg',
                                'required' => true
                            ],
                            'output_format' => [
                                'type' => 'select',
                                'label' => 'Output Format',
                                'options' => [
                                    'jpg' => 'JPEG',
                                    'png' => 'PNG',
                                    'gif' => 'GIF',
                                    'webp' => 'WebP'
                                ],
                                'required' => true
                            ],
                            'quality' => [
                                'type' => 'number',
                                'label' => 'Quality (1-100)',
                                'placeholder' => '90',
                                'min' => 1,
                                'max' => 100,
                                'default' => 90
                            ]
                        ]
                    ],
                    'apply_filter' => [
                        'label' => 'Apply Filter',
                        'fields' => [
                            'image_path' => [
                                'type' => 'text',
                                'label' => 'Image Path',
                                'placeholder' => '/path/to/image.jpg',
                                'required' => true
                            ],
                            'filter_type' => [
                                'type' => 'select',
                                'label' => 'Filter Type',
                                'options' => [
                                    'blur' => 'Blur',
                                    'sharpen' => 'Sharpen',
                                    'grayscale' => 'Grayscale',
                                    'sepia' => 'Sepia',
                                    'invert' => 'Invert'
                                ],
                                'required' => true
                            ],
                            'intensity' => [
                                'type' => 'number',
                                'label' => 'Filter Intensity',
                                'placeholder' => '5',
                                'min' => 1,
                                'max' => 10,
                                'default' => 5
                            ]
                        ]
                    ]
                ]
            ],
            'variable' => [
                'label' => 'Variable Processing',
                'icon' => 'cogs',
                'actions' => [
                    'set_variable' => [
                        'label' => 'Set Variable',
                        'fields' => [
                            'variable_name' => [
                                'type' => 'text',
                                'label' => 'Variable Name',
                                'placeholder' => 'my_variable',
                                'required' => true
                            ],
                            'variable_value' => [
                                'type' => 'text',
                                'label' => 'Variable Value',
                                'placeholder' => 'Enter value',
                                'required' => true
                            ],
                            'variable_type' => [
                                'type' => 'select',
                                'label' => 'Variable Type',
                                'options' => [
                                    'string' => 'String',
                                    'number' => 'Number',
                                    'boolean' => 'Boolean',
                                    'array' => 'Array',
                                    'object' => 'Object'
                                ],
                                'default' => 'string'
                            ]
                        ]
                    ],
                    'get_variable' => [
                        'label' => 'Get Variable',
                        'fields' => [
                            'variable_name' => [
                                'type' => 'text',
                                'label' => 'Variable Name',
                                'placeholder' => 'my_variable',
                                'required' => true
                            ],
                            'default_value' => [
                                'type' => 'text',
                                'label' => 'Default Value',
                                'placeholder' => 'Default if not found'
                            ]
                        ]
                    ],
                    'increment_variable' => [
                        'label' => 'Increment Variable',
                        'fields' => [
                            'variable_name' => [
                                'type' => 'text',
                                'label' => 'Variable Name',
                                'placeholder' => 'counter',
                                'required' => true
                            ],
                            'increment_by' => [
                                'type' => 'number',
                                'label' => 'Increment By',
                                'placeholder' => '1',
                                'default' => 1
                            ]
                        ]
                    ],
                    'decrement_variable' => [
                        'label' => 'Decrement Variable',
                        'fields' => [
                            'variable_name' => [
                                'type' => 'text',
                                'label' => 'Variable Name',
                                'placeholder' => 'counter',
                                'required' => true
                            ],
                            'decrement_by' => [
                                'type' => 'number',
                                'label' => 'Decrement By',
                                'placeholder' => '1',
                                'default' => 1
                            ]
                        ]
                    ]
                ]
            ],
            'content' => [
                'label' => 'Content Creation',
                'icon' => 'desktop',
                'actions' => [
                    'generate_text' => [
                        'label' => 'Generate Text',
                        'fields' => [
                            'prompt' => [
                                'type' => 'textarea',
                                'label' => 'Generation Prompt',
                                'placeholder' => 'Describe what text to generate',
                                'required' => true
                            ],
                            'max_length' => [
                                'type' => 'number',
                                'label' => 'Maximum Length',
                                'placeholder' => '500',
                                'min' => 10,
                                'max' => 5000,
                                'default' => 500
                            ],
                            'style' => [
                                'type' => 'select',
                                'label' => 'Writing Style',
                                'options' => [
                                    'formal' => 'Formal',
                                    'casual' => 'Casual',
                                    'technical' => 'Technical',
                                    'creative' => 'Creative'
                                ],
                                'default' => 'formal'
                            ]
                        ]
                    ],
                    'create_image' => [
                        'label' => 'Create Image',
                        'fields' => [
                            'prompt' => [
                                'type' => 'textarea',
                                'label' => 'Image Description',
                                'placeholder' => 'Describe the image to create',
                                'required' => true
                            ],
                            'width' => [
                                'type' => 'number',
                                'label' => 'Image Width',
                                'placeholder' => '512',
                                'min' => 256,
                                'max' => 1024,
                                'default' => 512
                            ],
                            'height' => [
                                'type' => 'number',
                                'label' => 'Image Height',
                                'placeholder' => '512',
                                'min' => 256,
                                'max' => 1024,
                                'default' => 512
                            ],
                            'style' => [
                                'type' => 'select',
                                'label' => 'Art Style',
                                'options' => [
                                    'realistic' => 'Realistic',
                                    'cartoon' => 'Cartoon',
                                    'abstract' => 'Abstract',
                                    'photographic' => 'Photographic'
                                ],
                                'default' => 'realistic'
                            ]
                        ]
                    ],
                    'generate_report' => [
                        'label' => 'Generate Report',
                        'fields' => [
                            'report_type' => [
                                'type' => 'select',
                                'label' => 'Report Type',
                                'options' => [
                                    'summary' => 'Summary',
                                    'detailed' => 'Detailed',
                                    'analytical' => 'Analytical'
                                ],
                                'required' => true
                            ],
                            'data_source' => [
                                'type' => 'text',
                                'label' => 'Data Source',
                                'placeholder' => 'Database table or file path',
                                'required' => true
                            ],
                            'include_charts' => [
                                'type' => 'checkbox',
                                'label' => 'Include Charts',
                                'default' => true
                            ]
                        ]
                    ]
                ]
            ],
            'timer' => [
                'label' => 'Timers & Delays',
                'icon' => 'clock',
                'actions' => [
                    'wait' => [
                        'label' => 'Wait',
                        'fields' => [
                            'duration' => [
                                'type' => 'number',
                                'label' => 'Duration',
                                'placeholder' => '5',
                                'min' => 1,
                                'required' => true
                            ],
                            'unit' => [
                                'type' => 'select',
                                'label' => 'Time Unit',
                                'options' => [
                                    'seconds' => 'Seconds',
                                    'minutes' => 'Minutes',
                                    'hours' => 'Hours'
                                ],
                                'default' => 'seconds'
                            ]
                        ]
                    ],
                    'schedule' => [
                        'label' => 'Schedule',
                        'fields' => [
                            'schedule_time' => [
                                'type' => 'datetime-local',
                                'label' => 'Schedule Time',
                                'required' => true
                            ],
                            'timezone' => [
                                'type' => 'select',
                                'label' => 'Timezone',
                                'options' => [
                                    'UTC' => 'UTC',
                                    'America/New_York' => 'Eastern Time',
                                    'Europe/London' => 'London Time',
                                    'Asia/Tokyo' => 'Tokyo Time'
                                ],
                                'default' => 'UTC'
                            ]
                        ]
                    ],
                    'repeat' => [
                        'label' => 'Repeat',
                        'fields' => [
                            'action_to_repeat' => [
                                'type' => 'select',
                                'label' => 'Action to Repeat',
                                'options' => 'dynamic', // Will be populated from available actions
                                'required' => true
                            ],
                            'repeat_count' => [
                                'type' => 'number',
                                'label' => 'Repeat Count',
                                'placeholder' => '5',
                                'min' => 1,
                                'max' => 1000,
                                'default' => 5
                            ],
                            'interval' => [
                                'type' => 'number',
                                'label' => 'Interval (seconds)',
                                'placeholder' => '60',
                                'min' => 1,
                                'default' => 60
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return response()->json($actionTypes);
    }
}

