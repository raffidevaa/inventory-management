<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('borrowings.index') }}" class="text-gray-500 hover:text-gray-700">← Borrowings</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Borrowing</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('borrowings.store') }}"
                      x-data="{
                          items: [{ product_id: '', quantity: 1 }],
                          products: {{ Js::from($products) }}
                      }">
                    @csrf

                    {{-- Borrower info --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
                        <div class="sm:col-span-2">
                            <x-input-label for="borrower_name" value="Borrower Name" />
                            <x-text-input id="borrower_name" name="borrower_name" type="text"
                                class="mt-1 block w-full" value="{{ old('borrower_name') }}" required maxlength="100" />
                            <x-input-error :messages="$errors->get('borrower_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="borrow_date" value="Borrow Date" />
                            <x-text-input id="borrow_date" name="borrow_date" type="date"
                                class="mt-1 block w-full" value="{{ old('borrow_date', now()->toDateString()) }}" required />
                            <x-input-error :messages="$errors->get('borrow_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="due_date" value="Due Date" />
                            <x-text-input id="due_date" name="due_date" type="date"
                                class="mt-1 block w-full" value="{{ old('due_date', now()->addWeek()->toDateString()) }}" required />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="notes" value="Notes (optional)" />
                            <textarea id="notes" name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                maxlength="500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-700">Items to Borrow</h3>
                            <button type="button" @click="items.push({ product_id: '', quantity: 1 })"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                + Add Item
                            </button>
                        </div>

                        {{-- Show any item-level errors returned from controller --}}
                        @if ($errors->hasAny(array_merge(['items'], array_keys($errors->toArray()))))
                            @foreach ($errors->all() as $error)
                                @if (str_starts_with($error, "'") || str_contains($error, 'stock') || str_contains($error, 'damaged') || str_contains($error, 'borrow'))
                                    <div class="mb-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2">{{ $error }}</div>
                                @endif
                            @endforeach
                        @endif

                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex gap-3 mb-3 items-start">
                                <div class="flex-1">
                                    <select :name="'items[' + index + '][product_id]'"
                                        x-model="item.product_id" required
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        <option value="">— Select product —</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id"
                                                x-text="p.name + ' (' + p.category?.name + ') — Stock: ' + p.stock_available">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-24">
                                    <input type="number" :name="'items[' + index + '][quantity]'"
                                        x-model="item.quantity" min="1" required
                                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                        placeholder="Qty" />
                                </div>
                                <button type="button" @click="items.splice(index, 1)"
                                    x-show="items.length > 1"
                                    class="mt-1 text-red-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6 pt-4 border-t">
                        <a href="{{ route('borrowings.index') }}">
                            <x-secondary-button type="button">Cancel</x-secondary-button>
                        </a>
                        <x-primary-button type="submit">Submit Borrowing</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
