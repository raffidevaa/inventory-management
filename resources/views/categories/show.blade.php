<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('categories.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700">← Categories</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ $category->name }}</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Category Info --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $category->name }}</h3>
                        @if ($category->description)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $category->description }}</p>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('categories.edit', $category) }}">
                            <x-secondary-button>Edit</x-secondary-button>
                        </a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}"
                              onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="submit">Delete</x-danger-button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Products in this category --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    Products ({{ $category->products->count() }})
                </h3>
                @if ($category->products->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-gray-500">No products in this category yet.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Stock</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Condition</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($category->products as $product)
                                <tr>
                                    <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400">{{ $product->code }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('products.show', $product) }}" class="text-brand-600 hover:underline">
                                            {{ $product->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">{{ $product->stock_available }} / {{ $product->stock }}</td>
                                    <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $product->condition) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
