<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('app.automation_tasks') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <form method="GET" class="flex items-center space-x-2">
                            <input name="q" value="{{ request('q') }}" class="border rounded px-3 py-2 text-sm" placeholder="Search tasks...">
                            <select name="status" class="border rounded px-2 py-2 text-sm">
                                <option value="">All</option>
                                <option value="draft" @selected(request('status')==='draft')>Draft</option>
                                <option value="published" @selected(request('status')==='published')>Published</option>
                                <option value="archived" @selected(request('status')==='archived')>Archived</option>
                            </select>
                            <button class="px-3 py-2 bg-blue-600 text-white rounded text-sm">{{ __('app.search') }}</button>
                        </form>
                        <form method="POST" action="{{ url('/automation/tasks') }}" class="inline">
                            @csrf
                            <input type="hidden" name="workspace_id" value="{{ auth()->user()->id }}">
                            <input type="hidden" name="owner_user_id" value="{{ auth()->id() }}">
                            <input type="hidden" name="name" value="Untitled">
                            <input type="hidden" name="slug" value="task-{{ \Illuminate\Support\Str::uuid() }}">
                            <button class="px-3 py-2 bg-green-600 text-white rounded text-sm">Create Draft</button>
                        </form>
                    </div>

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b">
                                <th class="py-2">Name</th>
                                <th class="py-2">Slug</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Version</th>
                                <th class="py-2">Owner</th>
                                <th class="py-2">Updated</th>
                                <th class="py-2 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $t)
                                <tr class="border-b">
                                    <td class="py-2">{{ $t->name }}</td>
                                    <td class="py-2 text-gray-500">{{ $t->slug }}</td>
                                    <td class="py-2">
                                        <span class="px-2 py-1 rounded text-xs {{ $t->status==='draft' ? 'bg-yellow-50 text-yellow-700' : ($t->status==='published' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600') }}">{{ $t->status }}</span>
                                    </td>
                                    <td class="py-2">{{ $t->current_version ?? '-' }}</td>
                                    <td class="py-2">{{ $t->owner_user_id }}</td>
                                    <td class="py-2">{{ $t->updated_at->diffForHumans() }}</td>
                                    <td class="py-2 text-right space-x-2">
                                        <a href="{{ route('automation.editor', $t) }}" class="text-blue-600 hover:underline">Open</a>
                                        @if($t->status==='published')
                                            <span class="text-gray-400">|</span>
                                            <span class="text-gray-500">Archive via API</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">{{ $tasks->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>




