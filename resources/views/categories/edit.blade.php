<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('categories.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700">← Categories</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Edit Category</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="name" value="Category Name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                value="{{ old('name', $category->name) }}" required maxlength="100" autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="description" value="Description (optional)" />
                            <textarea id="description" name="description" rows="3"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm text-sm"
                                maxlength="500">{{ old('description', $category->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6 pt-4 border-t dark:border-gray-700">
                        <a href="{{ route('categories.index') }}">
                            <x-secondary-button type="button">Cancel</x-secondary-button>
                        </a>
                        <x-primary-button type="submit">Update Category</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
