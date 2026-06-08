@props(['route', 'label', 'icon' => null])
@php $active = request()->routeIs($route) || request()->routeIs($route . '.*'); @endphp
<a href="{{ route($route) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ $active ? 'nav-active' : 'nav-inactive' }}">
    @if($icon)
        <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center">{!! $icon !!}</span>
    @else
        <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full {{ $active ? 'bg-green-400' : 'bg-white/30' }}"></span>
    @endif
    <span>{{ $label }}</span>
</a>
