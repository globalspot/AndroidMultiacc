<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $task->name }} ({{ $task->status }})</h2>
            <div class="space-x-2">
                <a href="{{ route('automation.list') }}" class="text-gray-600 hover:underline">Back</a>
                <button x-data x-on:click="window.location.reload()" class="px-3 py-2 bg-gray-100 rounded text-sm">Refresh</button>
            </div>
        </div>
    </x-slot>

    @php
        $variablesForJs = $task->variables->map(function($v){
            return [ 'id'=>$v->id, 'code'=>$v->code, 'direction'=>$v->direction, 'type'=>$v->type ];
        })->values();

        $stepsForJs = $task->steps->map(function($s){
            $call = $s->call->first();
            return [
                'id'=>$s->id,
                'action_type_id'=>$s->action_type_id,
                'action_type_label'=>optional($s->actionType)->label ?? ('#'.$s->action_type_id),
                'alias'=>$s->alias,
                'parameters'=>$s->parameters ?? (object)[],
                'bindings'=>$s->bindings->map(function($b){ return [ 'id'=>$b->id, 'from_output_field'=>$b->from_output_field, 'to_variable_code'=>$b->to_variable_code, 'transform'=>$b->transform ]; })->values(),
                'is_call_task'=>(optional($s->actionType)->code ?? '')==='call_task',
                'call'=>$call ? [
                    'called_task_slug'=>$call->called_task_slug,
                    'called_task_version'=>$call->called_task_version,
                    'allow_call_draft'=>$call->allow_call_draft,
                    'args_map'=>$call->args_map,
                    'result_map'=>$call->result_map,
                ] : new stdClass(),
            ];
        })->values();
    @endphp

    <div class="py-6" x-data="automationEditor()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-12 gap-4">
            <div class="col-span-3 space-y-4">
                <div class="bg-white shadow-sm rounded p-4">
                    <div class="font-medium mb-2">Variables</div>
                    <div class="text-sm space-y-2">
                        <template x-for="v in variables" :key="v.id">
                            <div class="flex items-center justify-between">
                                <span><span class="text-gray-500" x-text="v.direction"></span>: <span x-text="v.code"></span> <span class="text-gray-400">(<span x-text="v.type"></span>)</span></span>
                                <button class="text-red-600 text-xs" x-on:click="deleteVariable(v)">Delete</button>
                            </div>
                        </template>
                        <div class="mt-3">
                            <div class="text-xs text-gray-500 mb-1">Add variable</div>
                            <div class="flex items-center space-x-1">
                                <input x-model="newVar.code" class="border rounded px-2 py-1 text-xs w-20" placeholder="code">
                                <select x-model="newVar.direction" class="border rounded px-1 py-1 text-xs">
                                    <option value="input">input</option>
                                    <option value="output">output</option>
                                    <option value="inout">inout</option>
                                </select>
                                <select x-model="newVar.type" class="border rounded px-1 py-1 text-xs">
                                    <template x-for="t in types">
                                        <option :value="t" x-text="t"></option>
                                    </template>
                                </select>
                                <button class="px-2 py-1 bg-blue-600 text-white rounded text-xs" x-on:click="addVariable()">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm rounded p-4">
                    <div class="font-medium mb-2">Action Types</div>
                    <input x-model="paletteQuery" class="border rounded px-2 py-1 text-xs w-full mb-2" placeholder="Search actions...">
                    <div class="max-h-64 overflow-y-auto text-sm space-y-1">
                        <template x-for="a in filteredPalette()" :key="a.id">
                            <div class="flex items-center justify-between px-2 py-1 hover:bg-gray-50 rounded">
                                <span x-text="a.label"></span>
                                <button class="text-blue-600 text-xs" x-on:click="addStep(a)">Add</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="col-span-6">
                <div class="bg-white shadow-sm rounded">
                    <div class="p-4 border-b font-medium flex items-center justify-between">
                        <span>Steps</span>
                        <button class="text-xs text-gray-600" x-on:click="validate()">Run Validation</button>
                    </div>
                    <div class="p-2">
                        @php // reuse the precomputed variables/steps @endphp
                        @vite('resources/js/automation-editor.jsx')
                        <div id="react-automation-editor" data-task-id="{{ $task->id }}" data-variables='@json($variablesForJs)' data-steps='@json($stepsForJs)'></div>
                    </div>
                </div>
            </div>
            <div class="col-span-3">
                <div class="bg-white shadow-sm rounded p-4">
                    <div class="font-medium mb-2">Validation</div>
                    <div id="validation-results" class="text-sm text-gray-600">
                        <template x-if="validation.errors.length === 0 && validation.warnings.length === 0">
                            <div>No issues</div>
                        </template>
                        <template x-if="validation.errors.length > 0">
                            <div class="text-red-700 font-medium">Errors</div>
                            <ul class="list-disc ml-5 text-red-700">
                                <template x-for="e in validation.errors" :key="e.message">
                                    <li x-text="formatIssue(e)"></li>
                                </template>
                            </ul>
                        </template>
                        <template x-if="validation.warnings.length > 0">
                            <div class="mt-2 text-yellow-700 font-medium">Warnings</div>
                            <ul class="list-disc ml-5 text-yellow-700">
                                <template x-for="w in validation.warnings" :key="w.message">
                                    <li x-text="formatIssue(w)"></li>
                                </template>
                            </ul>
                        </template>
                        <div class="mt-4 space-x-2">
                            <button class="px-3 py-2 bg-blue-600 text-white rounded text-xs" x-on:click="validate()">Run validation</button>
                            <button class="px-3 py-2 bg-green-600 text-white rounded text-xs" x-on:click="publish()">Publish</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <script>
    function automationEditor() {
        return {
            taskId: {{ $task->id }},
            variables: @json($variablesForJs),
            steps: @json($stepsForJs),
            schemas: {},
            palette: [],
            paletteQuery: '',
            types: ['string','number','boolean','json','array','datetime'],
            newVar: { code:'', direction:'input', type:'string' },
            validation: { errors: [], warnings: [] },
            jsonText: { args:'{}', results:'{}' },
            async init() {
                await this.loadActionTypes();
            },
            async loadActionTypes() {
                const res = await fetch('/automation/action-types');
                const items = await res.json();
                this.palette = items;
                this.schemas = Object.fromEntries(items.map(a=>[a.id, { inputs:(a.input_schema||[]), outputs:(a.output_schema||[]) }]));
            },
            filteredPalette(){
                const q=this.paletteQuery.toLowerCase();
                return this.palette.filter(a=>!q||a.label.toLowerCase().includes(q)||a.code.toLowerCase().includes(q));
            },
            placeholderFor(field){ return field.required ? (field.name+'*') : field.name; },
            async addVariable(){
                const body = { code:this.newVar.code, direction:this.newVar.direction, type:this.newVar.type };
                const res = await fetch(`/automation/tasks/${this.taskId}/variables`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify(body) });
                if (res.ok){ const v=await res.json(); this.variables.push(v); this.newVar={ code:'', direction:'input', type:'string' }; }
            },
            async deleteVariable(v){
                await fetch(`/automation/variables/${v.id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} });
                this.variables = this.variables.filter(x=>x.id!==v.id);
            },
            async addStep(a){
                const body = { action_type_id:a.id, order_index:this.steps.length };
                const res = await fetch(`/automation/tasks/${this.taskId}/steps`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify(body) });
                if (res.ok){ const s=await res.json(); this.steps.push({ id:s.id, action_type_id:s.action_type_id, action_type_label:a.label, alias:null, parameters:{}, bindings:[], is_call_task:(a.code==='call_task'), call:{} }); }
            },
            async updateStep(s){
                await fetch(`/automation/steps/${s.id}`, { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({ alias:s.alias, parameters:s.parameters }) });
            },
            async deleteStep(s){
                await fetch(`/automation/steps/${s.id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} });
                this.steps = this.steps.filter(x=>x.id!==s.id);
            },
            async onReorder(e){
                const order = Array.from(e.target.querySelectorAll('[x-sortable-item]')).map(el=>Number(el.getAttribute('data-id')));
                await fetch(`/automation/tasks/${this.taskId}/reorder`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({ order }) });
            },
            addBinding(s){ s.bindings.push({_tmpId: crypto.randomUUID(), from_output_field:'', to_variable_code:'', transform:''}); },
            async saveBinding(s,b){
                if (b.id){
                    await fetch(`/automation/bindings/${b.id}`, { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({ from_output_field:b.from_output_field, to_variable_code:b.to_variable_code, transform:b.transform }) });
                } else {
                    const res = await fetch(`/automation/steps/${s.id}/binding`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({ from_output_field:b.from_output_field, to_variable_code:b.to_variable_code, transform:b.transform }) });
                    if (res.ok){ const nb=await res.json(); Object.assign(b, nb); }
                }
            },
            async deleteBinding(b){
                if (!b.id){ return; }
                await fetch(`/automation/bindings/${b.id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} });
                this.steps.forEach(s=> s.bindings = s.bindings.filter(x=>x!==b));
            },
            async saveCall(s){
                await fetch(`/automation/steps/${s.id}/call`, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify(s.call) });
            },
            async saveCallJson(s){
                try { s.call.args_map = JSON.parse(this.jsonText.args || '{}'); } catch(e){}
                try { s.call.result_map = JSON.parse(this.jsonText.results || '{}'); } catch(e){}
                await this.saveCall(s);
            },
            formatIssue(i){
                const scope = i.entity || 'item';
                return `[${scope}] ${i.message}`;
            },
            async validate(){
                const res = await fetch(`/automation/tasks/${this.taskId}/validate`);
                this.validation = await res.json();
            },
            async publish(){
                const res = await fetch(`/automation/tasks/${this.taskId}/publish`, { method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} });
                const data = await res.json();
                if (!res.ok){ this.validation = { errors: data.errors||[], warnings: data.warnings||[] }; }
                else { window.location.reload(); }
            },
        };
    }
    </script>
</x-app-layout>


