<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Notification</h2>
            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-secondary-button type="submit">
                        Tandai semua dibaca
                    </x-secondary-button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($notifications as $notification)
                    <div class="px-6 py-4 flex items-start gap-4 {{ is_null($notification->read_at) ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                        <div class="shrink-0 mt-0.5">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full {{ is_null($notification->read_at) ? 'bg-yellow-100 dark:bg-yellow-900' : 'bg-gray-100 dark:bg-gray-700' }}">
                                <svg class="w-5 h-5 {{ is_null($notification->read_at) ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                </svg>
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    Stok Menipis: {{ $notification->data['product_name'] }}
                                </p>
                                @if (is_null($notification->read_at))
                                    <span class="inline-block w-2 h-2 rounded-full bg-yellow-500 shrink-0"></span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">
                                Sisa stok tersedia: <strong>{{ $notification->data['stock_available'] }}</strong> dari
                                <strong>{{ $notification->data['stock'] }}</strong> unit
                                ({{ round($notification->data['ratio'] * 100) }}%)
                            </p>
                            @if (! empty($notification->data['product_code']))
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    Kode: {{ $notification->data['product_code'] }}
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                                @if ($notification->read_at)
                                    &middot; Dibaca {{ $notification->read_at->diffForHumans() }}
                                @endif
                            </p>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('products.show', $notification->data['product_id']) }}"
                               class="text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium">
                                Lihat produk
                            </a>
                            @if (is_null($notification->read_at))
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium">
                                        Tandai dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada notifikasi.</p>
                    </div>
                @endforelse
            </div>

            @if ($notifications->hasPages())
                <div class="mt-4">{{ $notifications->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
