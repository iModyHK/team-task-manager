@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('header', 'Admin Dashboard')

@section('header-actions')
    <div class="flex space-x-3">
        <a href="{{ route('admin.settings') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <i class="fas fa-cog mr-2"></i>
            Settings
        </a>
        <a href="{{ route('admin.logs.activity') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <i class="fas fa-history mr-2"></i>
            Activity Logs
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- System Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Users Stats -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Users
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['total_users'] }}
                                </div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold">
                                    <span class="text-green-600">
                                        {{ $stats['active_users'] }} active
                                    </span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.users.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Manage users
                    </a>
                </div>
            </div>
        </div>

        <!-- Teams Stats -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-user-friends text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Teams
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['total_teams'] }}
                                </div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold">
                                    <span class="text-blue-600">
                                        {{ $stats['active_teams'] }} active
                                    </span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.teams.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Manage teams
                    </a>
                </div>
            </div>
        </div>

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
                                Total Tasks
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
                    <a href="{{ route('admin.tasks.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View all tasks
                    </a>
                </div>
            </div>
        </div>

        <!-- System Health -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-heartbeat text-gray-400 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                System Health
                            </dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $stats['system_health'] }}%
                                </div>
                                <div class="ml-2 flex items-baseline text-sm font-semibold">
                                    <span class="text-gray-600">
                                        CPU: {{ $stats['cpu_usage'] }}%
                                    </span>
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('admin.system.health') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Security Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Recent Activity
                </h3>
                <a href="{{ route('admin.logs.activity') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all
                </a>
            </div>
            <div class="border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($recentActivity as $activity)
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

        <!-- Security Events -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Security Events
                </h3>
                <a href="{{ route('admin.logs.security') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all
                </a>
            </div>
            <div class="border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($securityEvents as $event)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-alt text-{{ $event->level_color }}-400"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $event->event }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $event->description }}
                                    </p>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $event->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 sm:px-6 text-center text-gray-500">
                            No security events
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                System Information
            </h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">
                        PHP Version
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $systemInfo['php_version'] }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">
                        Laravel Version
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $systemInfo['laravel_version'] }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">
                        Server Load
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $systemInfo['server_load'] }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">
                        Memory Usage
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $systemInfo['memory_usage'] }}
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">
                        Storage Usage
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $systemInfo['storage_usage_percentage'] }}%"></div>
                        </div>
                        <p class="mt-2">
                            {{ $systemInfo['storage_usage'] }} of {{ $systemInfo['storage_total'] }} used ({{ $systemInfo['storage_usage_percentage'] }}%)
                        </p>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
