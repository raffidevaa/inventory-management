<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700">← Products</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $product->name }}</h2>
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

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Product detail card --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                    {{-- Image --}}
                    <div class="sm:col-span-1">
                        @if ($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                class="w-full h-48 object-cover rounded-lg border" />
                        @else
                            <div class="w-full h-48 bg-gray-100 rounded-lg border flex items-center justify-center text-gray-400 text-sm">
                                No image
                            </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="sm:col-span-2 space-y-4">
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                            <div>
                                <dt class="text-gray-500 font-medium">Code</dt>
                                <dd class="font-mono text-gray-900">{{ $product->code }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Category</dt>
                                <dd class="text-gray-900">{{ $product->category?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Total Stock</dt>
                                <dd class="text-gray-900">{{ $product->stock }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Available</dt>
                                <dd class="text-gray-900">{{ $product->stock_available }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Condition</dt>
                                <dd>
                                    @php
                                        $badge = match($product->condition) {
                                            'good'            => 'bg-green-100 text-green-800',
                                            'lightly_damaged' => 'bg-yellow-100 text-yellow-800',
                                            'heavily_damaged' => 'bg-red-100 text-red-800',
                                            default           => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $badge }}">
                                        {{ str_replace('_', ' ', $product->condition) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Location</dt>
                                <dd class="text-gray-900">{{ $product->location ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Created</dt>
                                <dd class="text-gray-900">{{ $product->created_at->format('d M Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-medium">Last Updated</dt>
                                <dd class="text-gray-900">{{ $product->updated_at->format('d M Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Borrowing history --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Borrowing History</h3>

                @if ($product->borrowingDetails->isEmpty())
                    <p class="text-sm text-gray-400">No borrowing records for this product.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Borrower</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Borrow Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Condition After</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($product->borrowingDetails as $detail)
                                <tr>
                                    <td class="px-4 py-2">{{ $detail->borrowing->borrower_name }}</td>
                                    <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                    <td class="px-4 py-2">{{ $detail->borrowing->borrow_date->format('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ $detail->borrowing->due_date->format('d M Y') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $detail->item_status === 'returned' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
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
