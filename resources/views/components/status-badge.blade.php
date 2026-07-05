@props(['type', 'value'])
@php
    $classes = match($type) {
        'condition' => match($value) {
            'good'            => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            'lightly_damaged' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
            'heavily_damaged' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
            default           => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        },
        'borrowing-status' => match($value) {
            'borrowed' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
            'returned' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
            'overdue'  => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
            default    => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        },
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
@endphp
<span {{ $attributes->merge(['class' => "px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize $classes"]) }}>
    {{ str_replace('_', ' ', $value) }}
</span>
