@props(['method', 'class' => ''])

@php
    $methodStr = strtoupper($method);
    $colors = match($methodStr) {
        'GET'    => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
        'POST'   => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
        'PUT', 'PATCH' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
        'DELETE' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20',
        default  => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-500/10 dark:text-slate-400 dark:border-slate-500/20',
    };
@endphp

<span class="inline-flex items-center justify-center text-[10px] font-bold font-mono tracking-widest px-2 py-0.5 rounded border shadow-sm transition-colors duration-200 {{ $colors }} {{ $class }}">
    {{ $methodStr }}
</span>
