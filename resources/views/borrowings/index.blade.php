<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Borrowings</h2>
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
                           {{ $active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrow Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">By</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($borrowings as $borrowing)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $borrowing->borrower_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $borrowing->borrowingDetails->count() }} item(s)
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $borrowing->borrow_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $borrowing->due_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusBadge = match($borrowing->status) {
                                            'borrowed' => 'bg-yellow-100 text-yellow-800',
                                            'returned' => 'bg-green-100 text-green-800',
                                            'overdue'  => 'bg-red-100 text-red-800',
                                            default    => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize {{ $statusBadge }}">
                                        {{ $borrowing->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    {{ $borrowing->user?->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('borrowings.show', $borrowing) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-gray-400 text-sm">No borrowing records found.</p>
                                    @can('create', App\Models\Borrowing::class)
                                        <a href="{{ route('borrowings.create') }}" class="mt-2 inline-block text-indigo-600 hover:underline text-sm">Create first borrowing</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if ($borrowings->hasPages())
                    <div class="px-6 py-4 border-t">{{ $borrowings->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
