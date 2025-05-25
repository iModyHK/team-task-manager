@extends('layouts.app')

@section('title', 'User Details')

@section('header', 'User Details')

@section('header-actions')
    <div class="flex space-x-3">
        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            <i class="fas fa-edit mr-2"></i>
            Edit User
        </a>
        <a href="{{ route('admin.users.activity', $user) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <i class="fas fa-history mr-2"></i>
            Activity Log
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- User Profile -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center space-x-5">
                <div class="flex-shrink-0">
                    @if($user->avatar)
                        <img class="h-16 w-16 rounded-full" src="{{ $user->avatar_url }}" alt="">
                    @else
                        <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-2xl font-medium text-indigo-600">
                                {{ substr($user->name, 0, 2) }}
                            </span>
                        </div>
                    @endif
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ $user->name }}
                    </h2>
                    <div class="mt-1 flex items-center space-x-2 text-sm text-gray-500">
                        <span>{{ $user->email }}</span>
                        <span>•</span>
                        <span>{{ $user->username }}</span>
                        <span>•</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($user->status === 'active') bg-green-100 text-green-800
                            @elseif($user->status === 'inactive') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900">
                Account Information
            </h3>
            <div class="mt-6 border-t border-gray-200">
                <dl class="divide-y divide-gray-200">
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Role</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $user->role->name }}
                        </dd>
                    </div>
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Joined</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $user->created_at->format('F j, Y') }}
                        </dd>
                    </div>
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Last Active</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $user->last_active_at?->diffForHumans() ?? 'Never' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($user->email_verified_at)
                                <span class="text-green-600">
                                    <i class="fas fa-check-circle"></i>
                                    Verified on {{ $user->email_verified_at->format('F j, Y') }}
                                </span>
                            @else
                                <span class="text-red-600">
                                    <i class="fas fa-times-circle"></i>
                                    Not verified
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Two-Factor Auth</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($user->two_factor_enabled)
                                <span class="text-green-600">
                                    <i class="fas fa-check-circle"></i>
                                    Enabled
                                </span>
                            @else
                                <span class="text-gray-500">
                                    <i class="fas fa-times-circle"></i>
                                    Not enabled
                                    @if($user->two_factor_required)
                                        <span class="text-red-600 ml-2">(Required)</span>
                                    @endif
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900">
                Permissions
            </h3>
            <div class="mt-6 border-t border-gray-200">
                <div class="py-4">
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Role Permissions</h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse($user->role->permissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500">No role permissions</span>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Additional Permissions</h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse($user->permissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500">No additional permissions</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teams -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900">
                Teams
            </h3>
            <div class="mt-6 border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($user->teams as $team)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if($team->avatar)
                                        <img class="h-8 w-8 rounded-full" src="{{ $team->avatar_url }}" alt="">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">
                                                {{ substr($team->name, 0, 2) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $team->name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $team->pivot->role }}
                                    </p>
                                </div>
                                <div>
                                    <a href="{{ route('admin.teams.show', $team) }}" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                        View Team
                                    </a>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="py-4">
                            <div class="text-sm text-gray-500 text-center">
                                Not a member of any teams
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Recent Activity
                </h3>
                <a href="{{ route('admin.users.activity', $user) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View all activity
                </a>
            </div>
            <div class="mt-6 border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($recentActivity as $activity)
                        <li class="py-4">
                            <div class="flex space-x-3">
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-medium">{{ $activity->description }}</h3>
                                        <p class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if($activity->properties)
                                        <p class="text-sm text-gray-500">
                                            {{ $activity->properties['details'] ?? '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="py-4">
                            <div class="text-sm text-gray-500 text-center">
                                No recent activity
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
