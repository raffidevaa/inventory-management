{{-- Shared form fields for create/edit --}}
{{-- Expects: $categories (collection), $product (optional, for old values on edit) --}}

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

    {{-- Code --}}
    <div>
        <x-input-label for="code" value="Product Code" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full"
            value="{{ old('code', $product->code ?? '') }}" required maxlength="50" />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>

    {{-- Name --}}
    <div>
        <x-input-label for="name" value="Product Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            value="{{ old('name', $product->name ?? '') }}" required maxlength="150" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    {{-- Category --}}
    <div>
        <x-input-label for="category_id" value="Category" />
        <select id="category_id" name="category_id" required
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">— Select category —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>

    {{-- Condition --}}
    <div>
        <x-input-label for="condition" value="Condition" />
        <select id="condition" name="condition" required
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="good" {{ old('condition', $product->condition ?? '') === 'good' ? 'selected' : '' }}>Good</option>
            <option value="lightly_damaged" {{ old('condition', $product->condition ?? '') === 'lightly_damaged' ? 'selected' : '' }}>Lightly Damaged</option>
            <option value="heavily_damaged" {{ old('condition', $product->condition ?? '') === 'heavily_damaged' ? 'selected' : '' }}>Heavily Damaged</option>
        </select>
        <x-input-error :messages="$errors->get('condition')" class="mt-2" />
    </div>

    {{-- Stock --}}
    <div>
        <x-input-label for="stock" value="Total Stock" />
        <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full"
            value="{{ old('stock', $product->stock ?? 0) }}" required min="0" />
        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
    </div>

    {{-- Location --}}
    <div>
        <x-input-label for="location" value="Location (optional)" />
        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
            value="{{ old('location', $product->location ?? '') }}" maxlength="100" />
        <x-input-error :messages="$errors->get('location')" class="mt-2" />
    </div>

    {{-- Image --}}
    <div class="sm:col-span-2">
        <x-input-label for="image" value="Product Image (optional, max 2MB)" />
        <input id="image" name="image" type="file" accept="image/*"
            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
        <x-input-error :messages="$errors->get('image')" class="mt-2" />
    </div>

</div>
