@props(['type', 'value'])
@php
    $classes = match($type) {
        'condition' => match($value) {
            'good'            => 'bg-green-100 text-green-800',
            'lightly_damaged' => 'bg-yellow-100 text-yellow-800',
            'heavily_damaged' => 'bg-red-100 text-red-800',
            default           => 'bg-gray-100 text-gray-800',
        },
        'borrowing-status' => match($value) {
            'borrowed' => 'bg-yellow-100 text-yellow-800',
            'returned' => 'bg-green-100 text-green-800',
            'overdue'  => 'bg-red-100 text-red-800',
            default    => 'bg-gray-100 text-gray-700',
        },
        default => 'bg-gray-100 text-gray-800',
    };
@endphp
<span {{ $attributes->merge(['class' => "px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize $classes"]) }}>
    {{ str_replace('_', ' ', $value) }}
</span>
