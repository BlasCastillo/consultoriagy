@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-md border-b-2 border-white bg-slate-800 px-3 py-2 text-sm font-semibold leading-5 text-white transition-all duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70'
            : 'inline-flex items-center rounded-md border-b-2 border-transparent px-3 py-2 text-sm font-medium leading-5 text-slate-300 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-slate-800 hover:text-white hover:border-slate-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus:text-white active:translate-y-0';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
