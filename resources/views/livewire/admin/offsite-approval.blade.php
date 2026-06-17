{{-- resources/views/livewire/admin/offsite-approval.blade.php --}}
<div>

    {{-- Flash --}}
    @if (session('success'))
        <div
            class="mb-4 flex items-center gap-2 text-sm text-green-800 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── STAT BAR ──────────────────────────────────────────────────────── --}}
    <div class="mb-5">
        @if ($pendingCount > 0)
            <span
                class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-800 text-sm font-semibold px-3 py-1.5 rounded-full">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ $pendingCount }} menunggu persetujuan
            </span>
        @else
            <span
                class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-sm font-medium px-3 py-1.5 rounded-full">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Semua sudah diproses
            </span>
        @endif
    </div>

    {{-- ── FILTER BAR ────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-3 mb-4">

        <div class="flex-1 min-w-48">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama pegawai..."
                class="w-full rounded-xl border border-gray-200 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
        </div>

        <div class="flex rounded-xl border border-gray-200 overflow-hidden text-sm">
            @foreach ([
        'pending' => 'Pending',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'all' => 'Semua',
    ] as $val => $label)
                <button wire:click="$set('filterStatus','{{ $val }}')"
                    class="px-3 py-2.5 transition
                        {{ $filterStatus === $val ? 'bg-violet-600 text-white font-medium' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
            @endforeach
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
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi
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
                                    {{-- map-pin --}}
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

                        {{-- Status --}}
                        <td class="px-4 py-3">
                            @if ($rec->offsite_status === 'pending')
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                    {{-- clock --}}
                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Pending
                                </span>
                            @elseif ($rec->offsite_status === 'approved')
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    {{-- check-circle --}}
                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Disetujui
                                </span>
                                <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">
                                    oleh {{ $rec->approvedBy->name ?? '-' }}<br>
                                    {{ $rec->offsite_approved_at ? \Carbon\Carbon::parse($rec->offsite_approved_at)->diffForHumans() : '' }}
                                </p>
                            @elseif ($rec->offsite_status === 'rejected')
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    {{-- x-circle --}}
                                    <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Ditolak
                                </span>
                                @if ($rec->offsite_rejection_note)
                                    <p class="text-[10px] text-red-400 mt-0.5 max-w-[140px] leading-tight"
                                        title="{{ $rec->offsite_rejection_note }}">
                                        {{ Str::limit($rec->offsite_rejection_note, 60) }}
                                    </p>
                                @endif
                                <p class="text-[10px] text-gray-400 mt-0.5">
                                    oleh {{ $rec->approvedBy->name ?? '-' }}
                                </p>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3">
                            @if ($rec->offsite_status === 'pending')
                                <div class="flex gap-2">
                                    <button wire:click="approve({{ $rec->id }})" wire:loading.attr="disabled"
                                        wire:target="approve({{ $rec->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        <span wire:loading.remove
                                            wire:target="approve({{ $rec->id }})">Setujui</span>
                                        <span wire:loading wire:target="approve({{ $rec->id }})">...</span>
                                    </button>
                                    <button wire:click="openReject({{ $rec->id }})"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium rounded-lg transition">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        Tolak
                                    </button>
                                </div>
                            @elseif ($rec->offsite_status === 'approved')
                                <button wire:click="openReject({{ $rec->id }})"
                                    class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-red-600 transition">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                    Ubah ke Tolak
                                </button>
                            @elseif ($rec->offsite_status === 'rejected')
                                <button wire:click="approve({{ $rec->id }})" wire:loading.attr="disabled"
                                    wire:target="approve({{ $rec->id }})"
                                    class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-green-600 transition">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    <span wire:loading.remove wire:target="approve({{ $rec->id }})">Ubah ke
                                        Setujui</span>
                                    <span wire:loading wire:target="approve({{ $rec->id }})">...</span>
                                </button>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            {{-- map-pin icon --}}
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <p class="text-sm">
                                @if ($filterStatus === 'pending')
                                    Tidak ada pengajuan kegiatan luar yang menunggu persetujuan.
                                @else
                                    Tidak ada data kegiatan luar.
                                @endif
                            </p>
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

    {{-- ================================================================
         MODAL TOLAK
    ================================================================ --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 flex items-center justify-center p-4"
            style="background:rgba(0,0,0,0.5);z-index:99999;">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">

                {{-- Header modal --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-base font-bold text-gray-800">Tolak Kegiatan Luar</p>
                        <p class="text-xs text-gray-500">Catatan akan ditampilkan ke pegawai.</p>
                    </div>
                </div>

                <textarea wire:model="rejectionNote" rows="3" placeholder="Contoh: Tidak ada surat tugas yang dilampirkan..."
                    class="w-full rounded-xl border border-gray-200 text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none">
                </textarea>
                @error('rejectionNote')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror

                <div class="flex gap-3 mt-4">
                    <button wire:click="cancelReject"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button wire:click="confirmReject" wire:loading.attr="disabled"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" wire:loading.remove wire:target="confirmReject">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                        <span wire:loading.remove wire:target="confirmReject">Ya, Tolak</span>
                        <span wire:loading wire:target="confirmReject">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
