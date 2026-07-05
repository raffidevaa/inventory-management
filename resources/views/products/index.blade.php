<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Products') }}
            </h2>
            @can('create', App\Models\Product::class)
                <a href="{{ route('products.create') }}">
                    <x-primary-button>+ Add Product</x-primary-button>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Search + Filter --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 space-y-3">
                {{-- Search bar --}}
                <form method="GET" action="{{ route('products.index') }}" class="flex gap-2">
                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <x-text-input name="search" value="{{ request('search') }}" placeholder="Search by name or code…" class="flex-1" />
                    <x-primary-button type="submit">Search</x-primary-button>
                    @if (request('search') || request('category'))
                        <a href="{{ route('products.index') }}">
                            <x-secondary-button type="button">Clear All</x-secondary-button>
                        </a>
                    @endif
                </form>

                {{-- Category pills --}}
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('products.index', array_filter(['search' => request('search')])) }}"
                        class="px-3 py-1 rounded-full text-xs font-medium transition-colors
                            {{ ! request('category') ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        All
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('products.index', array_filter(['search' => request('search'), 'category' => $cat->id])) }}"
                            class="px-3 py-1 rounded-full text-xs font-medium transition-colors
                                {{ request('category') == $cat->id ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Card Grid --}}
            @if ($products->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-16 text-center">
                    <svg class="mx-auto w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                    </svg>
                    <p class="text-gray-400 dark:text-gray-500">No products found.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    @foreach ($products as $product)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden hover:shadow-md transition-shadow duration-200">

                            {{-- Image --}}
                            <a href="{{ route('products.show', $product) }}" class="block relative">
                                @if ($product->image)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                        class="w-full h-44 object-cover" />
                                @else
                                    <div class="w-full h-44 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                {{-- Condition badge over image --}}
                                <div class="absolute top-2 start-2">
                                    <x-status-badge type="condition" :value="$product->condition" />
                                </div>
                            </a>

                            {{-- Body --}}
                            <div class="flex flex-col flex-1 p-4 gap-2">

                                {{-- Category --}}
                                <span class="text-xs font-medium text-brand-600 dark:text-brand-400 uppercase tracking-wide">
                                    {{ $product->category?->name ?? '—' }}
                                </span>

                                {{-- Name + code --}}
                                <a href="{{ route('products.show', $product) }}" class="block">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 leading-snug line-clamp-2 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-xs font-mono text-gray-400 dark:text-gray-500 mt-0.5">{{ $product->code }}</p>
                                </a>

                                {{-- Stock + location --}}
                                <div class="flex items-center justify-between text-sm mt-auto pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                                        </svg>
                                        <span>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $product->stock_available }}</span>
                                            <span class="text-gray-400 dark:text-gray-500">/ {{ $product->stock }}</span>
                                        </span>
                                    </div>
                                    @if ($product->location)
                                        <span class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-[6rem]" title="{{ $product->location }}">
                                            {{ $product->location }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-2 pt-1">
                                    <a href="{{ route('products.show', $product) }}"
                                        class="flex-1 text-center text-xs font-medium py-1.5 rounded-md bg-brand-600 hover:bg-brand-700 text-white transition-colors">
                                        View
                                    </a>
                                    @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}"
                                            class="flex-1 text-center text-xs font-medium py-1.5 rounded-md border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            Edit
                                        </a>
                                    @endcan
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing <span class="font-medium text-gray-700 dark:text-gray-300">{{ $products->firstItem() ?? 0 }}</span>–<span class="font-medium text-gray-700 dark:text-gray-300">{{ $products->lastItem() ?? 0 }}</span>
                        of <span class="font-medium text-gray-700 dark:text-gray-300">{{ $products->total() }}</span> products
                    </p>
                    @if ($products->hasPages())
                        {{ $products->links() }}
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
