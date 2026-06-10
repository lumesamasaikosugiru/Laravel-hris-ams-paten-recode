<div>
    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari pegawai..." class="input w-56">
            <select wire:model.live="schoolFilter" class="input w-auto">
                <option value="">Semua Unit</option>
                @foreach($schools as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="typeFilter" class="input w-auto">
                <option value="">Semua Jenis Cuti</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="yearFilter" class="input w-auto">
                @for($y = now()->year; $y >= now()->year - 2; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
        <button wire:click="$set('showGenerateModal',true)" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Generate Saldo {{ $yearFilter }}
        </button>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr>
                <th class="w-8">#</th>
                <th>Pegawai</th>
                <th class="hidden md:table-cell">Jabatan</th>
                @foreach($leaveTypes as $lt)
                    @if(!$typeFilter || $typeFilter == $lt->id)
                    <th class="text-center text-xs">{{ $lt->name }}</th>
                    @endif
                @endforeach
            </tr></thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td class="text-gray-400 text-xs">{{ $employees->firstItem() + $loop->index }}</td>
                    <td>
                        <p class="font-medium text-gray-800">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-400">{{ $emp->school->name }}</p>
                    </td>
                    <td class="text-sm text-gray-500 hidden md:table-cell">
                        {{ $emp->activeAssignment?->position->name ?? '—' }}
                    </td>
                    @foreach($leaveTypes as $lt)
                        @if(!$typeFilter || $typeFilter == $lt->id)
                        @php $bal = $balances->get($emp->id)?->firstWhere('leave_type_id', $lt->id); @endphp
                        <td class="text-center">
                            @if($bal)
                            <div class="text-xs">
                                <span class="font-semibold {{ $bal->remaining <= 0 ? 'text-red-500' : 'text-green-600' }}">
                                    {{ $bal->remaining }}
                                </span>
                                <span class="text-gray-400">/{{ $bal->quota }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1 mt-1">
                                <div class="{{ $bal->remaining <= 0 ? 'bg-red-400' : 'bg-green-400' }} h-1 rounded-full"
                                     style="width:{{ $bal->quota > 0 ? round(($bal->remaining/$bal->quota)*100) : 0 }}%"></div>
                            </div>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        @endif
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="99" class="py-12 text-center text-sm text-gray-400">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($employees->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $employees->links() }}</div>
        @endif
    </div>

    {{-- Modal Generate --}}
    @if($showGenerateModal)
    <div class="modal-backdrop" wire:click="$set('showGenerateModal',false)">
        <div class="modal-box max-w-sm" wire:click.stop>
            <div class="modal-header">
                <h3>Generate Saldo Cuti</h3>
                <button wire:click="$set('showGenerateModal',false)" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <label class="form-label">Tahun</label>
                    <select wire:model="generateYear" class="input">
                        @for($y = now()->year; $y >= now()->year - 1; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
                    Akan membuat saldo cuti untuk semua pegawai aktif berdasarkan konfigurasi jenis cuti.
                    Saldo yang sudah ada tidak akan di-overwrite.
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showGenerateModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="generateBalances" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="generateBalances">Generate</span>
                    <span wire:loading wire:target="generateBalances">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
