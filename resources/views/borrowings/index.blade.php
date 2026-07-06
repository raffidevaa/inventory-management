<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Borrowings</h2>
            <div class="flex items-center gap-2">
                @can('view-reports')
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
                            <a href="{{ route('exports.borrowings.pdf', array_filter(['status' => request('status')])) }}"
                                class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>PDF Document</span>
                            </a>
                            <a href="{{ route('exports.borrowings.excel', array_filter(['status' => request('status')])) }}"
                                class="flex items-center gap-2.5 px-3 py-2.5 text-xs text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-700 dark:hover:text-green-400 transition-colors">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Excel Spreadsheet</span>
                            </a>
                        </div>
                    </div>
                @endcan
                @can('create', App\Models\Borrowing::class)
                    <a href="{{ route('borrowings.create') }}">
                        <x-primary-button>+ New Borrowing</x-primary-button>
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Status filter tabs --}}
            <div class="flex gap-2">
                @foreach (['all' => 'All', 'borrowed' => 'Borrowed', 'returned' => 'Returned', 'overdue' => 'Overdue'] as $value => $label)
                    @php $active = request('status', 'all') === $value; @endphp
                    <a href="{{ route('borrowings.index', $value !== 'all' ? ['status' => $value] : []) }}"
                       class="px-4 py-1.5 rounded-full text-sm font-medium transition
                           {{ $active ? 'bg-brand-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 border dark:border-gray-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Borrow Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">By</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($borrowings as $borrowing)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $borrowing->borrower_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $borrowing->borrowingDetails->count() }} item(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $borrowing->borrow_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $borrowing->due_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-status-badge type="borrowing-status" :value="$borrowing->status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 dark:text-gray-500">
                                    {{ $borrowing->user?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('borrowings.show', $borrowing) }}" class="text-brand-600 hover:text-brand-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-gray-400 dark:text-gray-500 text-sm">No borrowing records found.</p>
                                    @can('create', App\Models\Borrowing::class)
                                        <a href="{{ route('borrowings.create') }}" class="mt-2 inline-block text-brand-600 hover:underline text-sm">Create first borrowing</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                @if ($borrowings->hasPages())
                    <div class="px-6 py-4 border-t dark:border-gray-700">{{ $borrowings->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
