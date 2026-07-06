<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('borrowings.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700">← Borrowings</a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    Borrowing — {{ $borrowing->borrower_name }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <x-status-badge type="borrowing-status" :value="$borrowing->status" class="px-3 py-1 text-sm" />
                @can('view', $borrowing)
                    <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                        <button @click="open = !open"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Export
                            <svg class="w-3 h-3 transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-1.5 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-20 overflow-hidden"
                            style="display: none;">
                            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Export as</p>
                            </div>
                            <a href="{{ route('exports.borrowings.slip', $borrowing) }}"
                                class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>PDF Slip</span>
                            </a>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Transaction Info --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-400 dark:text-gray-500 font-medium">Borrower</dt>
                        <dd class="text-gray-800 dark:text-gray-100 font-semibold mt-1">{{ $borrowing->borrower_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 dark:text-gray-500 font-medium">Processed by</dt>
                        <dd class="text-gray-800 dark:text-gray-100 mt-1">{{ $borrowing->user?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 dark:text-gray-500 font-medium">Borrow Date</dt>
                        <dd class="text-gray-800 dark:text-gray-100 mt-1">{{ $borrowing->borrow_date->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 dark:text-gray-500 font-medium">Due Date</dt>
                        <dd class="mt-1 {{ $borrowing->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-800 dark:text-gray-100' }}">
                            {{ $borrowing->due_date->format('d M Y') }}
                            @if ($borrowing->isOverdue()) <span class="text-xs">(overdue)</span> @endif
                        </dd>
                    </div>
                    @if ($borrowing->return_date)
                        <div>
                            <dt class="text-gray-400 dark:text-gray-500 font-medium">Return Date</dt>
                            <dd class="text-gray-800 dark:text-gray-100 mt-1">{{ $borrowing->return_date->format('d M Y') }}</dd>
                        </div>
                    @endif
                    @if ($borrowing->notes)
                        <div class="col-span-2 sm:col-span-4">
                            <dt class="text-gray-400 dark:text-gray-500 font-medium">Notes</dt>
                            <dd class="text-gray-800 dark:text-gray-100 mt-1">{{ $borrowing->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Items --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Items</h3>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Condition Before</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Condition After</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($borrowing->borrowingDetails as $detail)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('products.show', $detail->product) }}" class="text-brand-600 hover:underline">
                                        {{ $detail->product->name }}
                                    </a>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ $detail->product->code }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $detail->quantity }}</td>
                                <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $detail->condition_before) }}</td>
                                <td class="px-4 py-3 capitalize">{{ $detail->condition_after ? str_replace('_', ' ', $detail->condition_after) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize
                                        {{ $detail->item_status === 'returned' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' }}">
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
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Process Return</h3>
                        <form method="POST" action="{{ route('borrowings.return', $borrowing) }}">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-3">
                                @foreach ($borrowedItems as $detail)
                                    <input type="hidden" name="items[{{ $loop->index }}][borrowing_detail_id]" value="{{ $detail->id }}" />
                                    <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="flex-1 text-sm">
                                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ $detail->product->name }}</span>
                                            <span class="text-gray-400 dark:text-gray-500 ml-1">× {{ $detail->quantity }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-input-label :for="'condition_' . $detail->id" value="Condition after return:" class="text-xs whitespace-nowrap" />
                                            <select :id="'condition_' . $detail->id" name="items[{{ $loop->index }}][condition_after]" required
                                                class="border-gray-300 dark:border-gray-600 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm">
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
