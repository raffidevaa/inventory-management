@props(['disabled' => false])
<select
    @if($disabled) disabled @endif
    {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}
>
    {{ $slot }}
</select>
