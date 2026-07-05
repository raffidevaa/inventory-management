<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700">← Users</a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">Edit User</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border dark:border-gray-700 border-red-200 rounded text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="name" value="Name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                value="{{ old('name', $user->name) }}" required maxlength="100" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" type="email" class="mt-1 block w-full bg-gray-50 dark:bg-gray-700"
                                value="{{ $user->email }}" disabled />
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Email cannot be changed here.</p>
                        </div>

                        <div>
                            <x-input-label for="role_id" value="Role" />
                            <select id="role_id" name="role_id" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}"
                                        {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6 pt-4 border-t dark:border-gray-700">
                        <a href="{{ route('users.index') }}">
                            <x-secondary-button type="button">Cancel</x-secondary-button>
                        </a>
                        <x-primary-button type="submit">Update User</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
