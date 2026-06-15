<div>
    {{-- ── Section Tugas Tambahan ── --}}
    <div class="card p-5 mt-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Tugas Tambahan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Jabatan aktif di luar unit induk</p>
            </div>
            @if(!$additional)
            <button wire:click="openAddModal"
                    class="btn-primary text-xs py-1.5 px-3 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Tugas
            </button>
            @endif
        </div>

        {{-- Tugas tambahan aktif --}}
        @if($additional)
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-200 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-blue-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-blue-800">{{ $additional->position->name }}</p>
                        <p class="text-xs text-blue-600">{{ $additional->department->name }}</p>
                        <p class="text-xs text-blue-500 mt-0.5">{{ $additional->school->name }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Mulai {{ $additional->start_date->translatedFormat('d M Y') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="badge bg-blue-100 text-blue-700 text-xs">Aktif</span>
                    <button wire:click="openEndModal"
                            class="text-xs text-red-400 hover:text-red-600 transition px-2 py-1 rounded-lg hover:bg-red-50">
                        Akhiri
                    </button>
                </div>
            </div>
            @if($additional->notes)
            <p class="text-xs text-blue-500 mt-2 pl-12">{{ $additional->notes }}</p>
            @endif
        </div>
        @else
        <div class="text-center py-6 text-xs text-gray-400">
            Tidak ada tugas tambahan aktif
        </div>
        @endif

        {{-- Riwayat tugas tambahan --}}
        @if($history->count() > 0)
        <div class="mt-4">
            <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">Riwayat</p>
            <div class="space-y-2">
                @foreach($history as $h)
                @if(!$h->is_active)
                <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                    <div class="w-1.5 h-1.5 rounded-full bg-gray-300 shrink-0 ml-1"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-600">{{ $h->position->name }}</p>
                        <p class="text-xs text-gray-400">{{ $h->school->name }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-400">
                            {{ $h->start_date->format('d/m/Y') }}
                            —
                            {{ $h->end_date?->format('d/m/Y') ?? 'Sekarang' }}
                        </p>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── MODAL: Tambah Tugas Tambahan ── --}}
    @if($showAddModal)
    <div class="modal-backdrop" wire:click="$set('showAddModal',false)">
        <div class="modal-box max-w-md" wire:click.stop>
            <div class="modal-header" style="background:linear-gradient(to right,#1d4ed8,#1e40af)">
                <h3>Tambah Tugas Tambahan</h3>
                <button wire:click="$set('showAddModal',false)" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700 mb-2">
                    Unit induk: <strong>{{ $employee->school->name }}</strong> — tugas tambahan harus di unit berbeda.
                </div>

                {{-- Unit --}}
                <div>
                    <label class="form-label">Unit <span class="text-red-500">*</span></label>
                    <select wire:model.live="school_id"
                            class="input @error('school_id') input-error @enderror">
                        <option value="">-- Pilih Unit --</option>
                        @foreach($schools as $sc)
                            <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                        @endforeach
                    </select>
                    @error('school_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Departemen --}}
                <div>
                    <label class="form-label">Departemen <span class="text-red-500">*</span></label>
                    <select wire:model.live="department_id"
                            class="input @error('department_id') input-error @enderror"
                            {{ !$school_id ? 'disabled' : '' }}>
                        <option value="">{{ $school_id ? '-- Pilih Departemen --' : '-- Pilih unit dulu --' }}</option>
                        @foreach($depts as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Jabatan --}}
                <div>
                    <label class="form-label">Jabatan <span class="text-red-500">*</span></label>
                    <select wire:model="position_id"
                            class="input @error('position_id') input-error @enderror"
                            {{ !$department_id ? 'disabled' : '' }}>
                        <option value="">{{ $department_id ? '-- Pilih Jabatan --' : '-- Pilih departemen dulu --' }}</option>
                        @foreach($positions as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('position_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal Mulai --}}
                <div>
                    <label class="form-label">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input wire:model="start_date" type="date"
                           class="input @error('start_date') input-error @enderror">
                    @error('start_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="form-label">Catatan</label>
                    <input wire:model="notes" type="text"
                           class="input" placeholder="Keterangan tugas tambahan (opsional)">
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showAddModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="saveAdditional" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="saveAdditional">Simpan</span>
                    <span wire:loading wire:target="saveAdditional">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── MODAL: Akhiri Tugas Tambahan ── --}}
    @if($showEndModal)
    <div class="modal-backdrop" wire:click="$set('showEndModal',false)">
        <div class="modal-box max-w-sm" wire:click.stop>
            <div class="modal-header" style="background:linear-gradient(to right,#dc2626,#b91c1c)">
                <h3>Akhiri Tugas Tambahan</h3>
                <button wire:click="$set('showEndModal',false)" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600 mb-3">
                    Tugas tambahan <strong>{{ $additional?->position->name }}</strong>
                    di <strong>{{ $additional?->school->name }}</strong> akan diakhiri hari ini.
                </p>
                <div>
                    <label class="form-label">Alasan / Catatan</label>
                    <input wire:model="end_notes" type="text"
                           class="input" placeholder="Opsional">
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showEndModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="endAdditional" wire:loading.attr="disabled" class="btn-danger">
                    <span wire:loading.remove wire:target="endAdditional">Ya, Akhiri</span>
                    <span wire:loading wire:target="endAdditional">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
