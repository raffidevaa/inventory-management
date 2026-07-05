<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Categories</h2>
            @can('manage-categories')
                <a href="{{ route('categories.create') }}">
                    <x-primary-button>+ Add Category</x-primary-button>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Products</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $category->description ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    <a href="{{ route('products.index', ['category' => $category->id]) }}"
                                       class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-300 hover:bg-brand-200">
                                        {{ $category->products_count }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    @can('manage-categories')
                                        <a href="{{ route('categories.edit', $category) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline"
                                              onsubmit="return confirm('Delete this category? Products will not be deleted.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <p class="text-gray-400 dark:text-gray-500 text-sm">No categories yet.</p>
                                    @can('manage-categories')
                                        <a href="{{ route('categories.create') }}" class="mt-2 inline-block text-brand-600 hover:underline text-sm">Add first category</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($categories->hasPages())
                    <div class="px-6 py-4 border-t dark:border-gray-700">{{ $categories->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
