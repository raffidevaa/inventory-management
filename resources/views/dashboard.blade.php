<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stats Cards (2x2) --}}
            <div class="grid grid-cols-2 gap-4">

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 flex flex-col items-center justify-center text-center">
                    <div class="p-2 bg-blue-50 rounded-full">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ number_format($stats['total_products']) }}</p>
                    <p class="text-sm text-gray-500">Total Products</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 flex flex-col items-center justify-center text-center">
                    <div class="p-2 bg-yellow-50 rounded-full">
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ number_format($stats['total_borrowed']) }}</p>
                    <p class="text-sm text-gray-500">Being Borrowed</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 flex flex-col items-center justify-center text-center">
                    <div class="p-2 bg-red-50 rounded-full">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-red-600 mb-1">{{ number_format($stats['total_overdue']) }}</p>
                    <p class="text-sm text-gray-500">Overdue</p>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 flex flex-col items-center justify-center text-center">
                    <div class="p-2 bg-green-50 rounded-full">
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold text-gray-800 mb-1">{{ number_format($stats['stock_available']) }}</p>
                    <p class="text-sm text-gray-500">Stock Available</p>
                </div>

            </div>

            {{-- Monthly Borrowing Chart --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-base font-semibold text-gray-700 mb-4">Monthly Borrowings — {{ now()->year }}</h3>
                <canvas id="borrowingChart" height="80"></canvas>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"
            integrity="sha384-e6nUZLBkQ86NJ6TVVKAeSaK8jWa3NhkYWZFomE39AvDbQWeie9PlQqM3pmYW5d1g"
            crossorigin="anonymous"></script>
    <script>
        const monthlyRaw = @json($monthlyBorrowings);
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const data = months.map((_, i) => monthlyRaw[i + 1] ?? 0);

        new Chart(document.getElementById('borrowingChart'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Borrowings',
                    data: data,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
    @endpush
</x-app-layout>
