<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700">← Products</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Product</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @csrf

                    @include('products._form')

                    <div class="flex items-center justify-end gap-4 mt-6 pt-4 border-t">
                        <a href="{{ route('products.index') }}">
                            <x-secondary-button type="button">Cancel</x-secondary-button>
                        </a>
                        <x-primary-button type="submit">Save Product</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
