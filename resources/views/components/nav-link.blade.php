@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3.5 py-1.5 rounded-xl bg-emerald-50 text-sm font-semibold text-emerald-800 border border-emerald-200/80 shadow-sm transition-all duration-200 active:scale-95'
            : 'inline-flex items-center px-3.5 py-1.5 rounded-xl text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 transition-all duration-200 active:scale-95';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
