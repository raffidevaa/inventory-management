<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('borrowings.index') }}" class="text-gray-500 hover:text-gray-700">← Borrowings</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Borrowing — {{ $borrowing->borrower_name }}
                </h2>
            </div>
            @php
                $statusBadge = match($borrowing->status) {
                    'borrowed' => 'bg-yellow-100 text-yellow-800',
                    'returned' => 'bg-green-100 text-green-800',
                    'overdue'  => 'bg-red-100 text-red-800',
                    default    => 'bg-gray-100 text-gray-700',
                };
            @endphp
            <span class="px-3 py-1 rounded-full text-sm font-semibold capitalize {{ $statusBadge }}">
                {{ $borrowing->status }}
            </span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Transaction Info --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-400 font-medium">Borrower</dt>
                        <dd class="text-gray-800 font-semibold mt-1">{{ $borrowing->borrower_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-medium">Processed by</dt>
                        <dd class="text-gray-800 mt-1">{{ $borrowing->user?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-medium">Borrow Date</dt>
                        <dd class="text-gray-800 mt-1">{{ $borrowing->borrow_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-medium">Due Date</dt>
                        <dd class="mt-1 {{ $borrowing->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-800' }}">
                            {{ $borrowing->due_date->format('d M Y') }}
                            @if ($borrowing->isOverdue()) <span class="text-xs">(overdue)</span> @endif
                        </dd>
                    </div>
                    @if ($borrowing->return_date)
                        <div>
                            <dt class="text-gray-400 font-medium">Return Date</dt>
                            <dd class="text-gray-800 mt-1">{{ $borrowing->return_date->format('d M Y') }}</dd>
                        </div>
                    @endif
                    @if ($borrowing->notes)
                        <div class="col-span-2 sm:col-span-4">
                            <dt class="text-gray-400 font-medium">Notes</dt>
                            <dd class="text-gray-800 mt-1">{{ $borrowing->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Items --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-700 mb-4">Items</h3>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Condition Before</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Condition After</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($borrowing->borrowingDetails as $detail)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('products.show', $detail->product) }}" class="text-indigo-600 hover:underline">
                                        {{ $detail->product->name }}
                                    </a>
                                    <div class="text-xs text-gray-400 font-mono">{{ $detail->product->code }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $detail->quantity }}</td>
                                <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $detail->condition_before) }}</td>
                                <td class="px-4 py-3 capitalize">{{ $detail->condition_after ? str_replace('_', ' ', $detail->condition_after) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize
                                        {{ $detail->item_status === 'returned' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $detail->item_status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Process Return Form --}}
            @can('processReturn', $borrowing)
                @php $borrowedItems = $borrowing->borrowingDetails->where('item_status', 'borrowed'); @endphp
                @if ($borrowedItems->isNotEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-4">Process Return</h3>
                        <form method="POST" action="{{ route('borrowings.return', $borrowing) }}">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-3">
                                @foreach ($borrowedItems as $detail)
                                    <input type="hidden" name="items[{{ $loop->index }}][borrowing_detail_id]" value="{{ $detail->id }}" />
                                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1 text-sm">
                                            <span class="font-medium text-gray-800">{{ $detail->product->name }}</span>
                                            <span class="text-gray-400 ml-1">× {{ $detail->quantity }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-input-label :for="'condition_' . $detail->id" value="Condition after return:" class="text-xs whitespace-nowrap" />
                                            <select :id="'condition_' . $detail->id" name="items[{{ $loop->index }}][condition_after]" required
                                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                <option value="good">Good</option>
                                                <option value="lightly_damaged">Lightly Damaged</option>
                                                <option value="heavily_damaged">Heavily Damaged</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 flex justify-end">
                                <x-primary-button type="submit">Confirm Return</x-primary-button>
                            </div>
                        </form>
                    </div>
                @endif
            @endcan

        </div>
    </div>
</x-app-layout>
