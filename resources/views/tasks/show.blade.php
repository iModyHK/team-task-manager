@extends('layouts.app')

@section('title', $task->title)

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center min-w-0">
            <h1 class="text-2xl font-semibold text-gray-900 truncate">{{ $task->title }}</h1>
            <span class="ml-4 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                @if($task->status === 'completed') bg-green-100 text-green-800
                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                @elseif($task->status === 'review') bg-yellow-100 text-yellow-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
            </span>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('tasks.edit', $task) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-edit mr-2"></i>
                Edit Task
            </a>
            <form action="{{ route('tasks.destroy', $task) }}" 
                  method="POST" 
                  class="inline-block"
                  onsubmit="return confirm('Are you sure you want to delete this task?');">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Task
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="col-span-2 space-y-6">
        <!-- Task Description -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900">Description</h3>
                <div class="mt-4 prose max-w-none">
                    {!! nl2br(e($task->description)) !!}
                </div>
            </div>
        </div>

        <!-- Subtasks -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Subtasks</h3>
                    <button type="button" 
                            onclick="window.addSubtask.show()"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                        <i class="fas fa-plus mr-2"></i>
                        Add Subtask
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse($task->subtasks as $subtask)
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   {{ $subtask->completed ? 'checked' : '' }}
                                   onchange="toggleSubtask({{ $subtask->id }})">
                            <span class="text-sm text-gray-900 {{ $subtask->completed ? 'line-through text-gray-500' : '' }}">
                                {{ $subtask->title }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No subtasks yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Comments</h3>
                
                <!-- Comment Form -->
                <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mb-6">
                    @csrf
                    <div>
                        <label for="comment" class="sr-only">Add a comment</label>
                        <textarea id="comment" 
                                 name="content" 
                                 rows="3"
                                 class="shadow-sm block w-full focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border border-gray-300 rounded-md"
                                 placeholder="Add a comment..."></textarea>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Post Comment
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-6">
                    @forelse($task->comments as $comment)
                        <div class="flex space-x-3">
                            <div class="flex-shrink-0">
                                @if($comment->user->avatar)
                                    <img class="h-10 w-10 rounded-full" src="{{ $comment->user->avatar_url }}" alt="">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-600">
                                            {{ substr($comment->user->name, 0, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow">
                                <div class="text-sm">
                                    <a href="#" class="font-medium text-gray-900">{{ $comment->user->name }}</a>
                                </div>
                                <div class="mt-1 text-sm text-gray-700">
                                    {!! nl2br(e($comment->content)) !!}
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    {{ $comment->created_at->diffForHumans() }}
                                </div>
                            </div>
                            @if($comment->user_id === auth()->id())
                                <div class="flex-shrink-0">
                                    <form action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" 
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this comment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-gray-500">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Activity</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($task->activities as $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center ring-8 ring-white">
                                                <i class="fas {{ $activity->icon }} text-gray-500"></i>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    {{ $activity->description }}
                                                    <a href="#" class="font-medium text-gray-900">
                                                        {{ $activity->user->name }}
                                                    </a>
                                                </p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Task Details -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <select id="status" 
                                    name="status" 
                                    onchange="updateStatus(this.value)"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>To Do</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>In Review</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Priority</dt>
                        <dd class="mt-1">
                            <select id="priority" 
                                    name="priority" 
                                    onchange="updatePriority(this.value)"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Assignee</dt>
                        <dd class="mt-1">
                            <select id="assignee" 
                                    name="assignee_id" 
                                    onchange="updateAssignee(this.value)"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $task->assignee_id === $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                        <dd class="mt-1">
                            <input type="date" 
                                   id="due_date" 
                                   name="due_date" 
                                   value="{{ $task->due_date?->format('Y-m-d') }}"
                                   onchange="updateDueDate(this.value)"
                                   class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created By</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $task->creator->name }} on {{ $task->created_at->format('M j, Y') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $task->updated_at->diffForHumans() }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Attachments -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Attachments</h3>
                    <button type="button" 
                            onclick="document.getElementById('file-upload').click()"
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                        <i class="fas fa-paperclip mr-2"></i>
                        Add File
                    </button>
                </div>
                <form id="upload-form" action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" 
                           id="file-upload" 
                           name="attachment" 
                           onchange="document.getElementById('upload-form').submit()">
                </form>
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($task->attachments as $attachment)
                        <li class="py-3 flex justify-between items-center">
                            <div class="flex items-center">
                                <i class="fas fa-file text-gray-400 mr-2"></i>
                                <span class="text-sm text-gray-900">{{ $attachment->filename }}</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('tasks.attachments.destroy', [$task, $attachment]) }}" 
                                      method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure you want to delete this file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-500">
                            No attachments yet.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    async function updateStatus(status) {
        await updateTask({ status });
    }

    async function updatePriority(priority) {
        await updateTask({ priority });
    }

    async function updateAssignee(assignee_id) {
        await updateTask({ assignee_id });
    }

    async function updateDueDate(due_date) {
        await updateTask({ due_date });
    }

    async function updateTask(data) {
        try {
            const response = await fetch('{{ route("tasks.update", $task) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error('Failed to update task');
            }

            // Optionally refresh the page or show a success message
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update task. Please try again.');
        }
    }

    async function toggleSubtask(id) {
        try {
            const response = await fetch(`/tasks/subtasks/${id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to update subtask');
            }

            // Optionally refresh the page or update the UI
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update subtask. Please try again.');
        }
    }
</script>
@endpush
