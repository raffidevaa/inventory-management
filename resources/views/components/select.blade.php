@props(['disabled' => false])
<select
    @if($disabled) disabled @endif
    {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 focus:ring-brand-500 dark:focus:border-brand-500 dark:focus:ring-brand-500 rounded-md shadow-sm']) }}
>
    {{ $slot }}
</select>
