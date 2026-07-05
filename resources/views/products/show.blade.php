<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('products.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700">← Products</a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">{{ $product->name }}</h2>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $product)
                    <a href="{{ route('products.edit', $product) }}">
                        <x-secondary-button>Edit</x-secondary-button>
                    </a>
                @endcan
                @can('delete', $product)
                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                          onsubmit="return confirm('Delete this product?')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button type="submit">Delete</x-danger-button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Product detail card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                    {{-- Image --}}
                    <div class="sm:col-span-1">
                        @if ($product->image)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                class="w-full h-48 object-cover rounded-lg border dark:border-gray-700" />
                        @else
                            <div class="w-full h-48 bg-gray-100 dark:bg-gray-700 rounded-lg border dark:border-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm">
                                No image
                            </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="sm:col-span-2 space-y-4">
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Code</dt>
                                <dd class="font-mono text-gray-900 dark:text-gray-100">{{ $product->code }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Category</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $product->category?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Total Stock</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $product->stock }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Available</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $product->stock_available }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Condition</dt>
                                <dd>
                                    <x-status-badge type="condition" :value="$product->condition" />
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Location</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $product->location ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Created</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $product->created_at->format('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400 font-medium">Last Updated</dt>
                                <dd class="text-gray-900 dark:text-gray-100">{{ $product->updated_at->format('d M Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Borrowing history --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Borrowing History</h3>

                @if ($product->borrowingDetails->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-gray-500">No borrowing records for this product.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Borrower</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Borrow Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Condition After</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($product->borrowingDetails as $detail)
                                <tr>
                                    <td class="px-4 py-2">{{ $detail->borrowing->borrower_name }}</td>
                                    <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-2">{{ $detail->borrowing->borrow_date->format('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ $detail->borrowing->due_date->format('d M Y') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $detail->item_status === 'returned' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
                                            {{ $detail->item_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $detail->condition_after ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
