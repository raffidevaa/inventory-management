<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Borrowings</h2>
            @can('create', App\Models\Borrowing::class)
                <a href="{{ route('borrowings.create') }}">
                    <x-primary-button>+ New Borrowing</x-primary-button>
                </a>
            @endcan
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
