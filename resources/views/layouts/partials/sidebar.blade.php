<!-- Sidebar container -->
<div class="flex flex-col flex-1 min-h-0 bg-white border-r">
    <!-- Logo -->
    <div class="flex items-center justify-center flex-shrink-0 h-16 px-4 bg-gray-50 border-b">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <i class="text-2xl text-indigo-600 fas fa-tasks"></i>
            <span class="text-xl font-bold text-gray-900">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Sidebar content -->
    <div class="flex flex-col flex-1 pt-5 pb-4 overflow-y-auto">
        <nav class="flex-1 px-2 space-y-1" x-data="{ openMenus: {} }">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-2 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md group">
                <i class="w-6 h-6 mr-3 fas fa-home"></i>
                Dashboard
            </a>

            <!-- Teams Section -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-2 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 group">
                    <div class="flex items-center">
                        <i class="w-6 h-6 mr-3 fas fa-users"></i>
                        Teams
                    </div>
                    <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>
                <div x-show="open" x-collapse>
                    <a href="{{ route('teams.index') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('teams.index') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        All Teams
                    </a>
                    <a href="{{ route('teams.create') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('teams.create') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        Create Team
                    </a>
                </div>
            </div>

            <!-- Tasks Section -->
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-2 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 group">
                    <div class="flex items-center">
                        <i class="w-6 h-6 mr-3 fas fa-tasks"></i>
                        Tasks
                    </div>
                    <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>
                <div x-show="open" x-collapse>
                    <a href="{{ route('tasks.index') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('tasks.index') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        All Tasks
                    </a>
                    <a href="{{ route('tasks.create') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('tasks.create') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        Create Task
                    </a>
                    <a href="{{ route('tasks.assigned') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('tasks.assigned') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        My Tasks
                    </a>
                </div>
            </div>

            <!-- Admin Section (Only visible to admins) -->
            @can('access_admin_panel')
            <div x-data="{ open: false }">
                <button @click="open = !open" 
                        class="flex items-center justify-between w-full px-2 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 group">
                    <div class="flex items-center">
                        <i class="w-6 h-6 mr-3 fas fa-shield-alt"></i>
                        Admin
                    </div>
                    <i class="fas" :class="open ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                </button>
                <div x-show="open" x-collapse>
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        Users
                    </a>
                    <a href="{{ route('admin.roles.index') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('admin.roles.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        Roles
                    </a>
                    <a href="{{ route('admin.settings') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('admin.settings') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        Settings
                    </a>
                    <a href="{{ route('admin.logs.activity') }}" 
                       class="flex items-center px-11 py-2 text-sm font-medium {{ request()->routeIs('admin.logs.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md">
                        Logs
                    </a>
                </div>
            </div>
            @endcan

            <!-- Settings -->
            <a href="{{ route('profile.show') }}" 
               class="flex items-center px-2 py-2 text-sm font-medium {{ request()->routeIs('profile.*') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md group">
                <i class="w-6 h-6 mr-3 fas fa-user-circle"></i>
                Profile
            </a>

            <a href="{{ route('settings') }}" 
               class="flex items-center px-2 py-2 text-sm font-medium {{ request()->routeIs('settings') ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-md group">
                <i class="w-6 h-6 mr-3 fas fa-cog"></i>
                Settings
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                @csrf
                <button type="submit" 
                        class="flex items-center w-full px-2 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 group">
                    <i class="w-6 h-6 mr-3 fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </form>
        </nav>
    </div>
</div>
