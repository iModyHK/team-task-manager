@extends('layouts.app')

@section('title', $team->name)

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-5">
            @if($team->avatar)
                <img class="h-16 w-16 rounded-full" src="{{ $team->avatar_url }}" alt="">
            @else
                <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-2xl font-medium text-indigo-600">
                        {{ substr($team->name, 0, 2) }}
                    </span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $team->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $team->members_count }} members · Created {{ $team->created_at->format('M j, Y') }}</p>
            </div>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('teams.edit', $team) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-edit mr-2"></i>
                Edit Team
            </a>
            @if(auth()->user()->can('manage', $team))
                <button type="button"
                        onclick="window.inviteMembers.show()"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <i class="fas fa-user-plus mr-2"></i>
                    Invite Members
                </button>
            @endif
        </div>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="col-span-2 space-y-6">
        <!-- Team Overview -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900">Overview</h3>
                <div class="mt-4 prose max-w-none">
                    {!! nl2br(e($team->description)) !!}
                </div>
            </div>
        </div>

        <!-- Team Tasks -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Tasks</h3>
                    <a href="{{ route('tasks.create', ['team_id' => $team->id]) }}" 
                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                        <i class="fas fa-plus mr-2"></i>
                        Add Task
                    </a>
                </div>

                <!-- Task Stats -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-gray-500">Total Tasks</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $taskStats['total'] }}</dd>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-gray-500">In Progress</dt>
                        <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ $taskStats['in_progress'] }}</dd>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-gray-500">Completed</dt>
                        <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $taskStats['completed'] }}</dd>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dt class="text-sm font-medium text-gray-500">Overdue</dt>
                        <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $taskStats['overdue'] }}</dd>
                    </div>
                </div>

                <!-- Recent Tasks -->
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
                                    Assignee
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Due Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentTasks as $task)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('tasks.show', $task) }}" class="hover:text-indigo-600">
                                                    {{ $task->title }}
                                                </a>
                                            </div>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No tasks found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Team Activity -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($activities as $activity)
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
        <!-- Team Members -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Team Members</h3>
                <ul role="list" class="divide-y divide-gray-200">
                    @foreach($team->members as $member)
                        <li class="py-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    @if($member->avatar)
                                        <img class="h-8 w-8 rounded-full" src="{{ $member->avatar_url }}" alt="">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">
                                                {{ substr($member->name, 0, 2) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $member->pivot->role }}</p>
                                </div>
                            </div>
                            @if(auth()->user()->can('manage', $team))
                                <div class="flex items-center space-x-3">
                                    <button type="button"
                                            onclick="updateMemberRole('{{ $member->id }}')"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        Change Role
                                    </button>
                                    @unless($member->id === $team->owner_id)
                                        <form action="{{ route('teams.members.remove', [$team, $member]) }}" 
                                              method="POST"
                                              class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to remove this member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Remove
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Pending Invitations -->
        @if(auth()->user()->can('manage', $team))
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pending Invitations</h3>
                    <ul role="list" class="divide-y divide-gray-200">
                        @forelse($pendingInvitations as $invitation)
                            <li class="py-4 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $invitation->email }}</p>
                                    <p class="text-xs text-gray-500">Invited {{ $invitation->created_at->diffForHumans() }}</p>
                                </div>
                                <form action="{{ route('teams.invitations.cancel', [$team, $invitation]) }}" 
                                      method="POST"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        Cancel
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="py-4 text-sm text-gray-500">
                                No pending invitations
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Invite Members Modal -->
<div x-data="inviteMembers"
     x-init="init()"
     x-show="isOpen"
     class="fixed z-50 inset-0 overflow-y-auto"
     x-cloak>
    <!-- Modal content -->
</div>

@push('scripts')
<script>
    window.inviteMembers = function() {
        return {
            isOpen: false,
            form: {
                emails: '',
                role: 'member'
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
                    emails: '',
                    role: 'member'
                };
                this.errors = {};
            },
            async submit() {
                try {
                    const response = await fetch('{{ route("teams.invitations.store", $team) }}', {
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

    async function updateMemberRole(memberId) {
        const role = prompt('Enter new role (member/admin):');
        if (!role) return;

        try {
            const response = await fetch(`{{ route('teams.members.update', $team) }}/${memberId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ role })
            });

            if (!response.ok) {
                throw new Error('Failed to update member role');
            }

            window.location.reload();
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update member role. Please try again.');
        }
    }
</script>
@endpush
