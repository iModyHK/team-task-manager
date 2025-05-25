@extends('layouts.app')

@section('title', 'Tasks')

@section('header')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Tasks</h1>
        <button type="button" 
                onclick="window.createTask.show()"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>
            New Task
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Task Filters -->
    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <form action="{{ route('tasks.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" 
                                name="status" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All Status</option>
                            <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>In Review</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                        <select id="priority" 
                                name="priority" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>

                    <!-- Assignee Filter -->
                    <div>
                        <label for="assignee" class="block text-sm font-medium text-gray-700">Assignee</label>
                        <select id="assignee" 
                                name="assignee" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All Assignees</option>
                            <option value="me" {{ request('assignee') === 'me' ? 'selected' : '' }}>Assigned to Me</option>
                            <option value="unassigned" {{ request('assignee') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('assignee') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Due Date Filter -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                        <select id="due_date" 
                                name="due_date" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All Dates</option>
                            <option value="overdue" {{ request('due_date') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="today" {{ request('due_date') === 'today' ? 'selected' : '' }}>Due Today</option>
                            <option value="week" {{ request('due_date') === 'week' ? 'selected' : '' }}>Due This Week</option>
                            <option value="month" {{ request('due_date') === 'month' ? 'selected' : '' }}>Due This Month</option>
                        </select>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           id="search"
                           value="{{ request('search') }}"
                           placeholder="Search tasks..."
                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="flex justify-end space-x-3">
        <button type="button" 
                onclick="setView('list')"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                :class="{ 'bg-gray-100': currentView === 'list' }">
            <i class="fas fa-list mr-2"></i>
            List View
        </button>
        <button type="button" 
                onclick="setView('board')"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                :class="{ 'bg-gray-100': currentView === 'board' }">
            <i class="fas fa-columns mr-2"></i>
            Board View
        </button>
    </div>

    <!-- List View -->
    <div x-show="currentView === 'list'" class="bg-white shadow rounded-lg">
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Task
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Priority
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Assignee
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Due Date
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($tasks as $task)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('tasks.show', $task) }}" class="hover:text-indigo-600">
                                                        {{ $task->title }}
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ Str::limit($task->description, 100) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($task->status === 'completed') bg-green-100 text-green-800
                                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                                @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($task->priority === 'high') bg-red-100 text-red-800
                                                @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                                @else bg-green-100 text-green-800 @endif">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($task->assignee)
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        @if($task->assignee->avatar)
                                                            <img class="h-8 w-8 rounded-full" src="{{ $task->assignee->avatar_url }}" alt="">
                                                        @else
                                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                                <span class="text-sm font-medium text-indigo-600">
                                                                    {{ substr($task->assignee->name, 0, 2) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $task->assignee->name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">Unassigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($task->due_date)
                                                <span class="{{ $task->is_overdue ? 'text-red-600' : '' }}">
                                                    {{ $task->due_date->format('M j, Y') }}
                                                </span>
                                            @else
                                                <span>No due date</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-3">
                                                <a href="{{ route('tasks.edit', $task) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </a>
                                                <form action="{{ route('tasks.destroy', $task) }}" 
                                                      method="POST" 
                                                      class="inline-block"
                                                      onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No tasks found. 
                                            <button type="button" 
                                                    onclick="window.createTask.show()"
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                Create your first task
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($tasks->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $tasks->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Board View -->
    <div x-show="currentView === 'board'" class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        <!-- To Do -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h3 class="text-sm font-medium text-gray-900">To Do</h3>
                <p class="text-sm text-gray-500">{{ $taskCounts['todo'] }} tasks</p>
            </div>
            <div class="p-4 space-y-4 min-h-[200px]">
                @foreach($tasks->where('status', 'todo') as $task)
                    <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                    {{ $task->title }}
                                </a>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($task->priority === 'high') bg-red-100 text-red-800
                                    @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ Str::limit($task->description, 100) }}
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                @if($task->assignee)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-6 w-6">
                                            @if($task->assignee->avatar)
                                                <img class="h-6 w-6 rounded-full" src="{{ $task->assignee->avatar_url }}" alt="">
                                            @else
                                                <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-indigo-600">
                                                        {{ substr($task->assignee->name, 0, 2) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-2 text-xs text-gray-500">
                                            {{ $task->assignee->name }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500">Unassigned</span>
                                @endif
                                @if($task->due_date)
                                    <span class="text-xs {{ $task->is_overdue ? 'text-red-600' : 'text-gray-500' }}">
                                        {{ $task->due_date->format('M j') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- In Progress -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h3 class="text-sm font-medium text-gray-900">In Progress</h3>
                <p class="text-sm text-gray-500">{{ $taskCounts['in_progress'] }} tasks</p>
            </div>
            <div class="p-4 space-y-4 min-h-[200px]">
                @foreach($tasks->where('status', 'in_progress') as $task)
                    <!-- Similar task card structure as To Do column -->
                @endforeach
            </div>
        </div>

        <!-- In Review -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h3 class="text-sm font-medium text-gray-900">In Review</h3>
                <p class="text-sm text-gray-500">{{ $taskCounts['review'] }} tasks</p>
            </div>
            <div class="p-4 space-y-4 min-h-[200px]">
                @foreach($tasks->where('status', 'review') as $task)
                    <!-- Similar task card structure as To Do column -->
                @endforeach
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white shadow rounded-lg">
            <div class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h3 class="text-sm font-medium text-gray-900">Completed</h3>
                <p class="text-sm text-gray-500">{{ $taskCounts['completed'] }} tasks</p>
            </div>
            <div class="p-4 space-y-4 min-h-[200px]">
                @foreach($tasks->where('status', 'completed') as $task)
                    <!-- Similar task card structure as To Do column -->
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div x-data="createTask"
     x-init="init()"
     x-show="isOpen"
     class="fixed z-50 inset-0 overflow-y-auto"
     x-cloak>
    <!-- Modal content -->
</div>

@push('scripts')
<script>
    let currentView = '{{ session("task_view", "list") }}';

    function setView(view) {
        currentView = view;
        fetch('{{ route("tasks.set-view") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ view })
        });
    }

    window.createTask = function() {
        return {
            isOpen: false,
            form: {
                title: '',
                description: '',
                status: 'todo',
                priority: 'medium',
                assignee_id: '',
                due_date: ''
            },
            errors: {},
            init() {
                // Initialize any required data
            },
            show() {
                this.isOpen = true;
            },
            close() {
                this.isOpen = false;
                this.form = {
                    title: '',
                    description: '',
                    status: 'todo',
                    priority: 'medium',
                    assignee_id: '',
                    due_date: ''
                };
                this.errors = {};
            },
            async submit() {
                try {
                    const response = await fetch('{{ route("tasks.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.errors = data.errors;
                        return;
                    }

                    window.location.reload();
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }
    }
</script>
@endpush
