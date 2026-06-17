{{-- resources/views/partials/auth-illustration.blade.php --}}
{{-- Ilustrasi tema "akses aman" — gerbang/shield dengan checkmark, warna brand --}}
<svg viewBox="0 0 400 340" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">

    {{-- Lingkaran latar lembut --}}
    <circle cx="200" cy="170" r="150" fill="white" fill-opacity="0.04" />
    <circle cx="200" cy="170" r="115" fill="white" fill-opacity="0.05" />

    {{-- Platform / dasar --}}
    <ellipse cx="200" cy="285" rx="110" ry="14" fill="black" fill-opacity="0.12" />

    {{-- Pilar kiri --}}
    <rect x="95" y="150" width="22" height="135" rx="4" fill="white" fill-opacity="0.18" />
    {{-- Pilar kanan --}}
    <rect x="283" y="150" width="22" height="135" rx="4" fill="white" fill-opacity="0.18" />

    {{-- Gerbang lengkung (arch) --}}
    <path d="M95 150 C95 90 130 50 200 50 C270 50 305 90 305 150" stroke="white" stroke-opacity="0.35" stroke-width="10"
        stroke-linecap="round" fill="none" />

    {{-- Pintu — dua daun --}}
    <rect x="150" y="120" width="46" height="165" rx="6" fill="#34D399" fill-opacity="0.85" />
    <rect x="204" y="120" width="46" height="165" rx="6" fill="#10B981" fill-opacity="0.9" />

    {{-- Garis tengah pintu --}}
    <line x1="200" y1="120" x2="200" y2="285" stroke="#065F46" stroke-width="2"
        stroke-opacity="0.4" />

    {{-- Lampu/handle pintu --}}
    <circle cx="188" cy="205" r="4" fill="#ECFDF5" />
    <circle cx="212" cy="205" r="4" fill="#ECFDF5" />

    {{-- Shield dengan check — melayang di depan gerbang --}}
    <g transform="translate(200, 175)">
        <circle r="46" fill="#FAF8F1" />
        <circle r="46" fill="white" fill-opacity="0.9" />
        <path d="M0 -28 L24 -18 L24 6 C24 22 12 32 0 38 C-12 32 -24 22 -24 6 L-24 -18 Z" fill="#065F46" />
        <path d="M-11 1 L-3 10 L13 -10" stroke="#ECFDF5" stroke-width="4.5" stroke-linecap="round"
            stroke-linejoin="round" fill="none" />
    </g>

    {{-- Partikel dekoratif --}}
    <circle cx="80" cy="90" r="5" fill="#6EE7B7" fill-opacity="0.6" />
    <circle cx="325" cy="110" r="4" fill="#34D399" fill-opacity="0.5" />
    <circle cx="340" cy="200" r="6" fill="#6EE7B7" fill-opacity="0.4" />
    <circle cx="65" cy="220" r="4" fill="#34D399" fill-opacity="0.5" />

    {{-- Garis orbit putus-putus --}}
    <circle cx="200" cy="170" r="95" stroke="#6EE7B7" stroke-opacity="0.25" stroke-width="1.5"
        stroke-dasharray="4 6" fill="none" />
</svg>
