@extends('layouts.app')

@section('title', 'Create User')

@section('header', 'Create New User')

@section('content')
<div class="max-w-3xl mx-auto">
    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white shadow rounded-lg">
            <!-- Basic Information -->
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Basic Information
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Enter the user's basic account information.
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
                                   value="{{ old('name') }}"
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
                                   value="{{ old('username') }}"
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
                                   value="{{ old('email') }}"
                                   required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="mt-1">
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   required
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
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
                        Assign the user's role and specific permissions.
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
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
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
                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
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
                        Configure additional account settings.
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
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Email Verification -->
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   name="skip_verification" 
                                   id="skip_verification"
                                   {{ old('skip_verification') ? 'checked' : '' }}
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="skip_verification" class="font-medium text-gray-700">
                                Skip Email Verification
                            </label>
                            <p class="text-gray-500">Mark the email as verified without sending verification email.</p>
                        </div>
                    </div>

                    <!-- Require Password Change -->
                    <div class="relative flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   name="require_password_change" 
                                   id="require_password_change"
                                   {{ old('require_password_change', true) ? 'checked' : '' }}
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="require_password_change" class="font-medium text-gray-700">
                                Require Password Change
                            </label>
                            <p class="text-gray-500">User will be required to change password on first login.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Create User
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
