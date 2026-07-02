<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Search --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('products.index') }}" class="flex gap-2">
                    <x-text-input name="search" value="{{ request('search') }}" placeholder="Search by name or code…" class="flex-1" />
                    <x-primary-button type="submit">Search</x-primary-button>
                    @if (request('search'))
                        <a href="{{ route('products.index') }}">
                            <x-secondary-button type="button">Clear</x-secondary-button>
                        </a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock / Available</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Condition</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">{{ $product->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $product->stock_available }} / {{ $product->stock }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badge = match($product->condition) {
                                            'good'             => 'bg-green-100 text-green-800',
                                            'lightly_damaged'  => 'bg-yellow-100 text-yellow-800',
                                            'heavily_damaged'  => 'bg-red-100 text-red-800',
                                            default            => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">
                                        {{ str_replace('_', ' ', $product->condition) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->location ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                    @endcan
                                    @can('delete', $product)
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline"
                                              onsubmit="return confirm('Delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($products->hasPages())
                    <div class="px-6 py-4 border-t">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
