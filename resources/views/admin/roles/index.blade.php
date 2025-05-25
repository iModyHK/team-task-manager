@extends('layouts.app')

@section('title', 'Role Management')

@section('header', 'Role Management')

@section('header-actions')
    <div class="flex space-x-3">
        <button type="button" 
                onclick="window.createRole.show()"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>
            Add Role
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Roles List -->
    <div class="bg-white shadow rounded-lg">
        <div class="flex flex-col">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Users
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Permissions
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Created
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($roles as $role)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <i class="fas fa-user-shield text-indigo-600"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $role->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $role->description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $role->users_count }}</div>
                                            <div class="text-xs text-gray-500">users assigned</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($role->permissions->take(3) as $permission)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $permission->name }}
                                                    </span>
                                                @endforeach
                                                @if($role->permissions->count() > 3)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        +{{ $role->permissions->count() - 3 }} more
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $role->created_at->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-3">
                                                <button type="button"
                                                        onclick="window.editRole.show({{ $role->id }})"
                                                        class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </button>
                                                @if(!$role->is_system)
                                                    <form action="{{ route('admin.roles.destroy', $role) }}" 
                                                          method="POST" 
                                                          class="inline-block"
                                                          onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div x-data="createRole"
     x-init="init()"
     x-show="isOpen"
     class="fixed z-50 inset-0 overflow-y-auto"
     x-cloak>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity"
             aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form @submit.prevent="submit">
                <div>
                    <div class="mt-3 text-center sm:mt-0 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Create New Role
                        </h3>
                        <div class="mt-6 space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    Role Name
                                </label>
                                <div class="mt-1">
                                    <input type="text" 
                                           name="name" 
                                           id="name"
                                           x-model="form.name"
                                           required
                                           class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <p class="mt-1 text-xs text-red-600" x-show="errors.name" x-text="errors.name"></p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <div class="mt-1">
                                    <textarea id="description"
                                              name="description"
                                              rows="3"
                                              x-model="form.description"
                                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                <p class="mt-1 text-xs text-red-600" x-show="errors.description" x-text="errors.description"></p>
                            </div>

                            <!-- Permissions -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Permissions
                                </label>
                                <div class="mt-2 space-y-2">
                                    @foreach($permissions as $permission)
                                        <div class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox"
                                                       id="permission_{{ $permission->id }}"
                                                       name="permissions[]"
                                                       value="{{ $permission->id }}"
                                                       x-model="form.permissions"
                                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="permission_{{ $permission->id }}" class="font-medium text-gray-700">
                                                    {{ $permission->name }}
                                                </label>
                                                <p class="text-gray-500">{{ $permission->description }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-1 text-xs text-red-600" x-show="errors.permissions" x-text="errors.permissions"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                            :disabled="loading">
                        <span x-show="!loading">Create Role</span>
                        <span x-show="loading">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Creating...
                        </span>
                    </button>
                    <button type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                            @click="close()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modal -->
<div x-data="editRole"
     x-init="init()"
     x-show="isOpen"
     class="fixed z-50 inset-0 overflow-y-auto"
     x-cloak>
    <!-- Similar structure to Create Role Modal, but with edit functionality -->
</div>

@push('scripts')
<script>
    window.createRole = function() {
        return {
            isOpen: false,
            loading: false,
            form: {
                name: '',
                description: '',
                permissions: []
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
                    name: '',
                    description: '',
                    permissions: []
                };
                this.errors = {};
            },
            async submit() {
                this.loading = true;
                this.errors = {};

                try {
                    const response = await fetch('{{ route("admin.roles.store") }}', {
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

                    // Success
                    window.location.reload();
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }

    window.editRole = function() {
        return {
            isOpen: false,
            loading: false,
            roleId: null,
            form: {
                name: '',
                description: '',
                permissions: []
            },
            errors: {},
            init() {
                // Initialize any required data
            },
            async show(id) {
                this.roleId = id;
                this.isOpen = true;
                await this.loadRole();
            },
            close() {
                this.isOpen = false;
                this.roleId = null;
                this.form = {
                    name: '',
                    description: '',
                    permissions: []
                };
                this.errors = {};
            },
            async loadRole() {
                try {
                    const response = await fetch(`/admin/roles/${this.roleId}`);
                    const data = await response.json();
                    
                    this.form = {
                        name: data.name,
                        description: data.description,
                        permissions: data.permissions.map(p => p.id)
                    };
                } catch (error) {
                    console.error('Error:', error);
                }
            },
            async submit() {
                this.loading = true;
                this.errors = {};

                try {
                    const response = await fetch(`/admin/roles/${this.roleId}`, {
                        method: 'PUT',
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

                    // Success
                    window.location.reload();
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
