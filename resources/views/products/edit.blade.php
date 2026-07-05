<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.show', $product) }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700">← {{ $product->name }}</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Edit Product</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data"
                      x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    @method('PUT')

                    @include('products._form')

                    {{-- Current image preview --}}
                    @if ($product->image)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Current image (uploading a new one will replace it):</p>
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                class="h-32 w-32 object-cover rounded-md border dark:border-gray-700" />
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-4 mt-6 pt-4 border-t dark:border-gray-700">
                        <a href="{{ route('products.show', $product) }}">
                            <x-secondary-button type="button">Cancel</x-secondary-button>
                        </a>
                        <x-primary-button type="submit" x-bind:disabled="loading" x-bind:class="loading ? 'opacity-50 cursor-not-allowed' : ''">
                            <span x-show="!loading">Update Product</span>
                            <span x-show="loading">Saving…</span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
