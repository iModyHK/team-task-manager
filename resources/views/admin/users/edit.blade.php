@extends('layouts.app')

@section('title', 'Edit User')

@section('header', 'Edit User')

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white shadow rounded-lg">
            <!-- User Status Banner -->
            @if($user->status !== 'active')
                <div class="p-4 @if($user->status === 'suspended') bg-red-50 @else bg-yellow-50 @endif">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas @if($user->status === 'suspended') fa-ban text-red-400 @else fa-exclamation-triangle text-yellow-400 @endif"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium @if($user->status === 'suspended') text-red-800 @else text-yellow-800 @endif">
                                Account {{ ucfirst($user->status) }}
                            </h3>
                            <div class="mt-2 text-sm @if($user->status === 'suspended') text-red-700 @else text-yellow-700 @endif">
                                <p>
                                    This account is currently {{ $user->status }}. 
                                    @if($user->status === 'suspended')
                                        User cannot access the system until the suspension is lifted.
                                    @else
                                        User has limited access to the system.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Basic Information -->
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Basic Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Update the user's basic account information.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Full Name
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            Username
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   name="username" 
                                   id="username" 
                                   value="{{ old('username', $user->username) }}"
                                   required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        @error('username')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email Address
                        </label>
                        <div class="mt-1">
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New Password
                        </label>
                        <div class="mt-1">
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                   placeholder="Leave blank to keep current password">
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Role and Permissions -->
            <div class="border-t border-gray-200 p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Role and Permissions
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage the user's role and specific permissions.
                    </p>
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">
                        Role
                    </label>
                    <div class="mt-1">
                        <select id="role" 
                                name="role" 
                                required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('role')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Additional Permissions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Additional Permissions
                    </label>
                    <div class="mt-2 space-y-2">
                        @foreach($permissions as $permission)
                            <div class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $permission->id }}"
                                           {{ in_array($permission->id, old('permissions', $user->permissions->pluck('id')->toArray())) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="permissions_{{ $permission->id }}" class="font-medium text-gray-700">
                                        {{ $permission->name }}
                                    </label>
                                    <p class="text-gray-500">{{ $permission->description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Account Settings -->
            <div class="border-t border-gray-200 p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Account Settings
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Manage account status and security settings.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Account Status
                        </label>
                        <select id="status" 
                                name="status" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>

                    <!-- Force Password Change -->
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   name="force_password_change" 
                                   id="force_password_change"
                                   {{ old('force_password_change', $user->force_password_change) ? 'checked' : '' }}
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="force_password_change" class="font-medium text-gray-700">
                                Force Password Change
                            </label>
                            <p class="text-gray-500">User will be required to change their password on next login.</p>
                        </div>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   name="require_2fa" 
                                   id="require_2fa"
                                   {{ old('require_2fa', $user->two_factor_required) ? 'checked' : '' }}
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="require_2fa" class="font-medium text-gray-700">
                                Require Two-Factor Authentication
                            </label>
                            <p class="text-gray-500">User will be required to set up 2FA before accessing the system.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                <div>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" 
                              method="POST" 
                              class="inline-block"
                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                Delete User
                            </button>
                        </form>
                    @endif
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
