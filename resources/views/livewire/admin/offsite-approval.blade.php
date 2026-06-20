{{-- resources/views/livewire/admin/offsite-approval.blade.php --}}
{{--
    READ-ONLY sejak 20 Juni 2026 -- lihat catatan di OffsiteApproval.php.
    Halaman ini cuma log informasi kegiatan luar lokasi, tidak ada lagi
    tombol approve/reject/pending. Semua absensi offsite otomatis sah.
--}}
<div>

    <div class="mb-5">
        <span
            class="inline-flex items-center gap-1.5 bg-violet-100 text-violet-700 text-sm font-medium px-3 py-1.5 rounded-full">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            {{ $records->total() }} kegiatan luar tercatat
        </span>
    </div>

    {{-- ── FILTER BAR ────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <div class="flex-1 min-w-48">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama pegawai..."
                class="w-full rounded-xl border border-gray-200 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
        </div>
        <input wire:model.live="filterDate" type="date"
            class="rounded-xl border border-gray-200 text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
    </div>

    {{-- ── TABEL ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pegawai
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jam</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Alasan
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($records as $rec)
                    <tr class="hover:bg-gray-50 transition">

                        {{-- Pegawai --}}
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $rec->employee->name }}</p>
                            <p class="text-xs text-gray-400">{{ $rec->employee->school->name ?? '-' }}</p>
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-4 py-3">
                            <p class="text-gray-700">{{ \Carbon\Carbon::parse($rec->date)->translatedFormat('d M Y') }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($rec->date)->translatedFormat('l') }}</p>
                        </td>

                        {{-- Jam --}}
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            {{ $rec->check_in ? \Carbon\Carbon::parse($rec->check_in)->format('H:i') : '—' }}
                            @if ($rec->check_out)
                                <span class="text-gray-400">–</span>
                                {{ \Carbon\Carbon::parse($rec->check_out)->format('H:i') }}
                            @endif
                        </td>

                        {{-- Alasan + keterangan + link Maps --}}
                        <td class="px-4 py-3 max-w-xs">
                            <p class="font-medium text-gray-700">{{ $rec->offsite_reason }}</p>
                            @if ($rec->offsite_note)
                                <p class="text-xs text-gray-400 mt-0.5 truncate" title="{{ $rec->offsite_note }}">
                                    {{ $rec->offsite_note }}
                                </p>
                            @endif
                            @if ($rec->checkin_latitude)
                                <a href="https://maps.google.com/?q={{ $rec->checkin_latitude }},{{ $rec->checkin_longitude }}"
                                    target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 text-[10px] text-violet-500 hover:underline mt-0.5">
                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    Lihat di Maps
                                </a>
                            @endif
                        </td>

                        {{-- Status: selalu informasi, tidak ada lagi pending/rejected --}}
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Tercatat
                            </span>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <p class="text-sm">Tidak ada data kegiatan luar.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($records->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
