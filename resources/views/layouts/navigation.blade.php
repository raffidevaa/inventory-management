<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800 dark:text-gray-200">
                        MyInventoryManagement
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Dashboard
                    </x-nav-link>
                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                        Products
                    </x-nav-link>
                    @if (Auth::user()->hasRole('admin'))
                        <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                            Categories
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('borrowings.index')" :active="request()->routeIs('borrowings.*')">
                        Borrowings
                    </x-nav-link>
                    @if (Auth::user()->hasRole('admin'))
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            Users
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if (Auth::user()->hasAnyRole(['admin', 'staff']))
                    @php
                        $unreadCount = Auth::user()->unreadNotifications()->count();
                        $recentNotifications = Auth::user()->unreadNotifications()->latest()->take(5)->get();
                    @endphp
                    <div
                        x-data="{ open: false }"
                        class="relative me-3"
                        @click.outside="open = false"
                    >
                        <button
                            @click="open = !open"
                            class="relative p-2 rounded-md text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition"
                            aria-label="Notifikasi"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($unreadCount > 0)
                                <span class="absolute top-1 right-1 inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-500 rounded-full">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50"
                            style="display: none;"
                        >
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notifikasi</h3>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium">
                                            Tandai semua dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-72 overflow-y-auto">
                                @forelse ($recentNotifications as $notification)
                                    <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                        <div class="flex items-start gap-3">
                                            <div class="shrink-0 mt-0.5">
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                                                    Stok Menipis: {{ $notification->data['product_name'] }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    Sisa {{ $notification->data['stock_available'] }} / {{ $notification->data['stock'] }} unit
                                                    ({{ round($notification->data['ratio'] * 100) }}%)
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                                @csrf
                                                <button type="submit" class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Tandai dibaca">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Tidak ada notifikasi baru.
                                    </div>
                                @endforelse
                            </div>

                            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('notifications.index') }}" class="block text-center text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium py-1">
                                    Lihat semua notifikasi
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <x-dark-mode-toggle class="me-2" />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex flex-col items-end">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="text-xs text-brand-500 capitalize">{{ Auth::user()->role?->name ?? 'user' }}</span>
                            </div>
                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                Products
            </x-responsive-nav-link>
            @if (Auth::user()->hasRole('admin'))
                <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                    Categories
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('borrowings.index')" :active="request()->routeIs('borrowings.*')">
                Borrowings
            </x-responsive-nav-link>
            @if (Auth::user()->hasRole('admin'))
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    Users
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="px-4 flex items-center justify-between">
                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-brand-500 capitalize">{{ Auth::user()->role?->name ?? 'user' }}</div>
                    <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                </div>
                <x-dark-mode-toggle />
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
