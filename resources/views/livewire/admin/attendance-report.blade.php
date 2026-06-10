<div>
    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live="monthFilter" type="month" class="input w-auto">
            <select wire:model.live="schoolFilter" class="input w-auto">
                <option value="">Semua Unit</option>
                @foreach($schools as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                @endforeach
            </select>
            <input wire:model.live.debounce.300ms="search"
                   type="text" placeholder="Cari pegawai..."
                   class="input w-48">
        </div>
        <a href="{{ route('admin.attendance.export', ['month' => $monthFilter, 'school' => $schoolFilter]) }}"
           class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Pegawai</th>
                    <th class="hidden md:table-cell">Jabatan</th>
                    <th class="text-center">Hadir</th>
                    <th class="text-center">Terlambat</th>
                    <th class="text-center">Tidak Hadir</th>
                    <th class="text-center">Cuti/Izin</th>
                    <th class="text-center">% Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                @php $s = $summary[$emp->id] ?? ['present'=>0,'late'=>0,'absent'=>0,'leave'=>0,'pct'=>0]; @endphp
                <tr>
                    <td class="text-gray-400 text-xs">{{ $employees->firstItem() + $loop->index }}</td>
                    <td>
                        <p class="font-medium text-gray-800">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $emp->nipy ?? $emp->nik }}</p>
                    </td>
                    <td class="text-sm text-gray-500 hidden md:table-cell">
                        {{ $emp->activeAssignment?->position->name ?? '—' }}
                    </td>
                    <td class="text-center">
                        <span class="font-semibold text-green-600">{{ $s['present'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="font-semibold text-yellow-600">{{ $s['late'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="font-semibold text-red-500">{{ $s['absent'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="font-semibold text-blue-500">{{ $s['leave'] }}</span>
                    </td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $s['pct'] >= 75 ? 'bg-green-500' : 'bg-red-400' }}"
                                     style="width:{{ $s['pct'] }}%"></div>
                            </div>
                            <span class="text-xs font-medium {{ $s['pct'] < 75 ? 'text-red-500' : 'text-gray-700' }}">
                                {{ $s['pct'] }}%
                            </span>
                            @if($s['pct'] < 75)
                                <span class="badge-red text-xs">!</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-gray-400 text-sm">
                        Belum ada data untuk periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($employees->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $employees->links() }}</div>
        @endif
    </div>
</div>
