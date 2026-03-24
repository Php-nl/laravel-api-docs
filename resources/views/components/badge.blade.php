@props(['method'])

@php
    $colors = match(strtoupper($method)) {
        'GET' => 'bg-green-100 text-green-700',
        'POST' => 'bg-blue-100 text-blue-700',
        'PUT', 'PATCH' => 'bg-yellow-100 text-yellow-700',
        'DELETE' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "text-xs font-bold uppercase px-1.5 py-0.5 rounded {$colors}"]) }}>
    {{ $method }}
</span>
