<!-- Top Navigation -->
<nav class="sticky top-0 z-10 flex-shrink-0 bg-white shadow">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left side -->
            <div class="flex">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" 
                        class="px-4 text-gray-500 border-r border-gray-200 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 lg:hidden">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Search bar -->
                <div class="flex items-center px-4">
                    <form class="flex w-full lg:ml-0" action="{{ route('search') }}" method="GET">
                        <label for="search-field" class="sr-only">Search</label>
                        <div class="relative w-full text-gray-400 focus-within:text-gray-600">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                                <i class="fas fa-search"></i>
                            </div>
                            <input id="search-field"
                                   class="block w-full h-full py-2 pl-8 pr-3 text-gray-900 placeholder-gray-500 border-0 focus:outline-none focus:placeholder-gray-400 focus:ring-0 sm:text-sm"
                                   placeholder="Search tasks, teams, or members"
                                   type="search"
                                   name="query"
                                   value="{{ request('query') }}">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center">
                <!-- Notifications dropdown -->
                <div x-data="{ open: false }" class="relative ml-3">
                    <button @click="open = !open"
                            class="flex items-center p-1 text-gray-400 bg-white rounded-full hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span class="sr-only">View notifications</span>
                        <div class="relative">
                            <i class="text-xl fas fa-bell"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 block w-2 h-2 bg-red-400 rounded-full ring-2 ring-white"></span>
                            @endif
                        </div>
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 w-80 mt-2 origin-top-right bg-white divide-y divide-gray-100 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                         role="menu"
                         aria-orientation="vertical"
                         aria-labelledby="user-menu-button"
                         tabindex="-1">
                        <div class="py-1" role="none">
                            <div class="px-4 py-2 text-sm font-medium text-gray-900 border-b">
                                Notifications
                            </div>
                            @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                                <a href="{{ route('notifications.show', $notification) }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $notification->read_at ? '' : 'bg-blue-50' }}"
                                   role="menuitem">
                                    <p class="font-medium">{{ $notification->data['title'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <div class="px-4 py-2 text-sm text-gray-500">
                                    No notifications
                                </div>
                            @endforelse
                            <a href="{{ route('notifications.index') }}"
                               class="block px-4 py-2 text-sm text-center text-indigo-600 border-t hover:bg-gray-100">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile dropdown -->
                <div x-data="{ open: false }" class="relative ml-3">
                    <button @click="open = !open"
                            class="flex items-center max-w-xs text-sm bg-white rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            id="user-menu-button"
                            aria-expanded="false"
                            aria-haspopup="true">
                        <span class="sr-only">Open user menu</span>
                        @if(auth()->user()->avatar)
                            <img class="w-8 h-8 rounded-full" src="{{ auth()->user()->avatar_url }}" alt="User avatar">
                        @else
                            <div class="flex items-center justify-center w-8 h-8 text-white bg-indigo-600 rounded-full">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 w-48 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                         role="menu"
                         aria-orientation="vertical"
                         aria-labelledby="user-menu-button"
                         tabindex="-1">
                        <div class="py-1" role="none">
                            <div class="px-4 py-2 text-sm text-gray-900 border-b">
                                <p class="font-medium">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>

                            <a href="{{ route('profile.show') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                               role="menuitem">
                                Your Profile
                            </a>

                            <a href="{{ route('settings') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                               role="menuitem">
                                Settings
                            </a>

                            @can('access_admin_panel')
                                <a href="{{ route('admin.dashboard') }}"
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                   role="menuitem">
                                    Admin Panel
                                </a>
                            @endcan

                            <form method="POST" action="{{ route('logout') }}" role="none">
                                @csrf
                                <button type="submit"
                                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100"
                                        role="menuitem">
                                    Sign out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
