@props(['route', 'label'])

@php $active = request()->routeIs($route) || request()->routeIs($route.'.*'); @endphp

<a href="{{ route($route) }}" class="nav-link {{ $active ? 'active' : '' }}">
    <span class="shrink-0 w-4 h-4 flex items-center justify-center">
        {{ $icon ?? '' }}
    </span>
    <span>{{ $label }}</span>
</a>
