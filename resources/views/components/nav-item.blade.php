@props(['route', 'label', 'icon' => null])
@php $active = request()->routeIs($route) || request()->routeIs($route.'.*'); @endphp
<a href="{{ route($route) }}" class="nav-link {{ $active ? 'active' : '' }}">
    @if($icon)
        <span class="shrink-0 w-4 h-4">{!! $icon !!}</span>
    @else
        <span class="shrink-0 w-1.5 h-1.5 rounded-full {{ $active ? 'bg-green-400' : 'bg-white/25' }}"></span>
    @endif
    <span>{{ $label }}</span>
</a>
