@extends('layouts.app')

@section('title', 'Dashboard')

@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Tasks Stats -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tasks text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                My Tasks
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['total_tasks'] }}
                                </div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold">
                                    <span class="text-green-600">
                                        {{ $stats['completed_tasks'] }} completed
                                    </span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('tasks.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View all tasks
                    </a>
                </div>
            </div>
        </div>

        <!-- Team Stats -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                My Teams
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['total_teams'] }}
                                </div>
                                @if($stats['team_lead_count'] > 0)
                                    <div class="ml-2 flex items-baseline text-sm font-semibold">
                                        <span class="text-blue-600">
                                            {{ $stats['team_lead_count'] }} as lead
                                        </span>
                                    </div>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('teams.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View all teams
                    </a>
                </div>
            </div>
        </div>

        <!-- Due Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Due Soon
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['due_soon_count'] }}
                                </div>
                                @if($stats['overdue_count'] > 0)
                                    <div class="ml-2 flex items-baseline text-sm font-semibold">
                                        <span class="text-red-600">
                                            {{ $stats['overdue_count'] }} overdue
                                        </span>
                                    </div>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('tasks.due') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View due tasks
                    </a>
                </div>
            </div>
        </div>

        <!-- Activity -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Recent Activity
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['recent_activities'] }}
                                </div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold text-gray-600">
                                    last 24h
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('activities.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View activity
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tasks -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Recent Tasks
            </h3>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-plus mr-2"></i>
                New Task
            </a>
        </div>
        <div class="border-t border-gray-200">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($recentTasks as $task)
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($task->priority === 'high') bg-red-100 text-red-800
                                        @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800 @endif">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $task->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">
                                        {{ Str::limit($task->description, 100) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-alt mr-1.5"></i>
                                    <span>{{ $task->due_date->format('M j, Y') }}</span>
                                </div>
                                <div class="flex items-center">
                                    @foreach($task->assignees->take(3) as $assignee)
                                        <img class="h-6 w-6 rounded-full -ml-1 border-2 border-white" 
                                             src="{{ $assignee->avatar_url }}" 
                                             alt="{{ $assignee->name }}">
                                    @endforeach
                                    @if($task->assignees->count() > 3)
                                        <span class="h-6 w-6 rounded-full -ml-1 border-2 border-white bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                            +{{ $task->assignees->count() - 3 }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 sm:px-6 text-center text-gray-500">
                        No tasks found. 
                        <a href="{{ route('tasks.create') }}" class="text-indigo-600 hover:text-indigo-500">
                            Create your first task
                        </a>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Team Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- My Teams -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    My Teams
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($myTeams as $team)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($team->avatar)
                                            <img class="h-10 w-10 rounded-full" src="{{ $team->avatar_url }}" alt="">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-indigo-600 font-medium">
                                                    {{ substr($team->name, 0, 2) }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <a href="{{ route('teams.show', $team) }}" class="text-sm font-medium text-gray-900">
                                            {{ $team->name }}
                                        </a>
                                        <p class="text-sm text-gray-500">
                                            {{ $team->members_count }} members
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $team->open_tasks_count }} open tasks
                                    </span>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 sm:px-6 text-center text-gray-500">
                            No teams found.
                            <a href="{{ route('teams.create') }}" class="text-indigo-600 hover:text-indigo-500">
                                Create a team
                            </a>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Recent Activity
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($recentActivities as $activity)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex space-x-3">
                                <img class="h-6 w-6 rounded-full" 
                                     src="{{ $activity->user->avatar_url }}" 
                                     alt="">
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-medium">{{ $activity->user->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        {{ $activity->description }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 sm:px-6 text-center text-gray-500">
                            No recent activity
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
