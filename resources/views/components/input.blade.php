@props(['label' => null, 'required' => false, 'type' => 'text', 'description' => null, 'theme' => 'light', 'enumValues' => null])

@php
$isDark = $theme === 'dark';
$labelClass = $isDark ? 'text-gray-300' : 'text-gray-700';
$descClass = $isDark ? 'text-gray-400' : 'text-gray-500';
$inputClass = $isDark 
    ? 'bg-slate-800 border-slate-700 text-white focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] placeholder-slate-500'
    : 'bg-white border-gray-300 text-gray-900 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] placeholder-gray-400';
@endphp

<div class="mb-4">
    @if($label)
        <label class="block text-sm font-medium {{ $labelClass }} mb-1.5 flex items-center justify-between">
            <span>
                <span class="font-mono">{{ $label }}</span>
                @if($required)
                    <span class="text-red-500 ml-0.5">*</span>
                @endif
            </span>
            @if($attributes->has('type-hint'))
                <span class="text-xs {{ $isDark ? 'text-slate-500' : 'text-gray-400' }} font-mono">{{ $attributes->get('type-hint') }}</span>
            @endif
        </label>
    @endif
    @if(is_array($enumValues) && count($enumValues) > 0)
        <select {{ $attributes->except(['type-hint', 'theme']) }} class="w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm outline-none transition-colors appearance-none {{ $inputClass }}">
            @if(!$required)
                <option value="">(None)</option>
            @endif
            @foreach($enumValues as $val)
                <option value="{{ $val }}">{{ $val }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" {{ $attributes->except(['type-hint', 'theme']) }} class="w-full px-3 py-2 border rounded-md shadow-sm sm:text-sm outline-none transition-colors {{ $inputClass }}">
    @endif
    @if($description)
        <p class="mt-1.5 text-xs {{ $descClass }}">{{ $description }}</p>
    @endif
</div>
