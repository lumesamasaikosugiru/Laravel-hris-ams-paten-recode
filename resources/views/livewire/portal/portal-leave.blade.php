<div class="p-4 space-y-4">

    {{-- ════════════════════════════════════════════════════════
         APPROVAL KEPALA SEKOLAH (tahap 1, hanya tampil untuk role
         kepala_sekolah, scoped ke sekolahnya sendiri)
    ════════════════════════════════════════════════════════ --}}
    @if ($schoolApprovals->count() > 0)
        <div class="portal-card overflow-hidden border-2 border-violet-200">
            <div class="px-5 py-3 border-b border-gray-100 bg-violet-50">
                <p class="text-xs font-semibold text-violet-700 uppercase tracking-wide">
                    Menunggu Persetujuan Anda ({{ $schoolApprovals->count() }})
                </p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($schoolApprovals as $appr)
                    <div class="px-5 py-3">
                        <p class="text-sm font-semibold text-gray-700">{{ $appr->employee->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $appr->leaveType->name }} ·
                            {{ $appr->start_date->format('d M') }}
                            @if ($appr->start_date->ne($appr->end_date))
                                — {{ $appr->end_date->format('d M Y') }}
                            @else
                                {{ $appr->start_date->format('Y') }}
                            @endif
                            · {{ $appr->days }} hari
                        </p>
                        <p class="text-xs text-gray-400 mt-1 italic">{{ $appr->reason }}</p>
                        <div class="flex gap-2 mt-2">
                            <button wire:click="openSchoolApproveModal({{ $appr->id }}, 'approved')"
                                class="flex-1 text-xs font-semibold py-2 rounded-lg bg-green-100 text-green-700">
                                ✓ Setujui
                            </button>
                            <button wire:click="openSchoolApproveModal({{ $appr->id }}, 'rejected')"
                                class="flex-1 text-xs font-semibold py-2 rounded-lg bg-red-100 text-red-700">
                                ✕ Tolak
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Modal konfirmasi approval Kepala Sekolah --}}
    @if ($showSchoolApproveModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-5 w-full max-w-sm space-y-3">
                <p class="font-semibold text-gray-800">
                    {{ $schoolApproveAction === 'approved' ? 'Setujui pengajuan ini?' : 'Tolak pengajuan ini?' }}
                </p>
                @if ($schoolApproveAction === 'approved')
                    <p class="text-xs text-gray-500">Setelah disetujui, pengajuan diteruskan ke Admin SDM untuk
                        persetujuan akhir.</p>
                @else
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1.5">Alasan
                            Penolakan *</label>
                        <textarea wire:model="schoolRejectionNote" rows="3"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none @error('schoolRejectionNote') border-red-400 @enderror"></textarea>
                        @error('schoolRejectionNote')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
                <div class="flex gap-2 pt-1">
                    <button wire:click="processSchoolApproval" wire:loading.attr="disabled"
                        class="flex-1 text-sm font-semibold py-2.5 rounded-xl {{ $schoolApproveAction === 'approved' ? 'bg-green-600' : 'bg-red-600' }} text-white">
                        Konfirmasi
                    </button>
                    <button wire:click="$set('showSchoolApproveModal', false)"
                        class="flex-1 text-sm font-semibold py-2.5 rounded-xl bg-gray-100 text-gray-600">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Saldo Cuti --}}
    @if ($balances->count() > 0)
        <div class="portal-card p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Saldo Cuti {{ now()->year }}
            </p>
            <div class="space-y-3">
                @foreach ($balances as $bal)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700">{{ $bal->leaveType->name }}</span>
                            <span class="font-bold {{ $bal->remaining <= 0 ? 'text-red-500' : 'text-green-600' }}">
                                {{ $bal->remaining }}/{{ $bal->quota }} hari
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $bal->remaining <= 0 ? 'bg-red-400' : 'bg-green-400' }} h-2 rounded-full transition-all"
                                style="width: {{ $bal->quota > 0 ? round(($bal->remaining / $bal->quota) * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tombol Ajukan --}}
    @if (!$showForm)
        <button wire:click="$set('showForm', true)" class="portal-btn-primary">
            + Ajukan Cuti Baru
        </button>
    @else
        {{-- Form Pengajuan --}}
        <div class="portal-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <p class="font-semibold text-gray-800">Ajukan Cuti</p>
                <button wire:click="$set('showForm', false)" class="text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Jenis Cuti --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1.5">
                    Jenis Cuti *
                </label>
                <select wire:model.live="leave_type_id"
                    class="w-full border border-gray-200 rounded-xl px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 @error('leave_type_id') border-red-400 @enderror">
                    <option value="">-- Pilih Jenis Cuti --</option>
                    @foreach ($leaveTypes as $lt)
                        <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->quota }} hari)</option>
                    @endforeach
                </select>
                @error('leave_type_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info saldo --}}
            @if ($selectedBalance)
                <div
                    class="p-3 {{ $selectedBalance['remaining'] > 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded-xl text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sisa saldo</span>
                        <span
                            class="font-bold {{ $selectedBalance['remaining'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $selectedBalance['remaining'] }} hari
                        </span>
                    </div>
                </div>
            @endif

            {{-- Tanggal --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1.5">Mulai
                        *</label>
                    <input wire:model.live="start_date" type="date"
                        min="{{ \App\Services\LeaveService::minStartDate() }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 @error('start_date') border-red-400 @enderror">
                    @error('start_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1.5">Selesai
                        *</label>
                    <input wire:model.live="end_date" type="date" min="{{ $start_date }}"
                        max="{{ $maxEndDate }}"
                        class="w-full border border-gray-200 rounded-xl px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 @error('end_date') border-red-400 @enderror">
                    @error('end_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if ($calculatedDays > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700 text-center">
                    <strong>{{ $calculatedDays }} hari kerja</strong> (Senin–Jumat)
                </div>
            @endif

            {{-- Alasan --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1.5">Alasan *</label>
                <textarea wire:model="reason" rows="3" placeholder="Jelaskan alasan pengajuan cuti..."
                    class="w-full border border-gray-200 rounded-xl px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 resize-none @error('reason') border-red-400 @enderror"></textarea>
                @error('reason')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Upload Dokumen --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide block mb-1.5">
                    Dokumen Pendukung
                </label>
                <input wire:model="document_file" type="file" accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700">
                <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG · maks 5MB</p>
                @error('document_file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 space-y-2">
                <button wire:click="save" wire:loading.attr="disabled" class="portal-btn-primary">
                    <span wire:loading.remove wire:target="save">Kirim Pengajuan</span>
                    <span wire:loading wire:target="save">Mengirim...</span>
                </button>
                <button wire:click="$set('showForm', false)" class="portal-btn-ghost w-full">Batal</button>
            </div>
        </div>
    @endif

    {{-- Riwayat Pengajuan --}}
    @if ($requests->count() > 0)
        <div class="portal-card overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Riwayat Pengajuan</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach ($requests as $req)
                    <div class="px-5 py-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-700">{{ $req->leaveType->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $req->start_date->format('d M') }}
                                    @if ($req->start_date->ne($req->end_date))
                                        — {{ $req->end_date->format('d M Y') }}
                                    @else
                                        {{ $req->start_date->format('Y') }}
                                    @endif
                                    · {{ $req->days }} hari
                                </p>
                            </div>
                            <span
                                class="status-chip text-xs
                        {{ $req->status === 'approved'
                            ? 'bg-green-100 text-green-700'
                            : ($req->status === 'pending'
                                ? 'bg-amber-100 text-amber-700'
                                : 'bg-red-100 text-red-700') }}">
                                @if ($req->status === 'approved')
                                    ✓ Disetujui
                                @elseif($req->status === 'rejected')
                                    ✕ Ditolak
                                @elseif($req->requires_school_approval && $req->school_status === 'pending')
                                    ⏳ Menunggu Kepala Sekolah
                                @elseif($req->requires_school_approval && $req->school_status === 'approved')
                                    ⏳ Menunggu Admin SDM
                                @else
                                    ⏳ Menunggu
                                @endif
                            </span>
                        </div>
                        @if ($req->approver_notes)
                            <p class="text-xs text-gray-400 mt-1 italic">{{ $req->approver_notes }}</p>
                        @endif
                        @if ($req->status === 'rejected' && $req->school_status === 'rejected' && $req->school_rejection_note)
                            <p class="text-xs text-red-400 mt-1 italic">Kepala Sekolah:
                                {{ $req->school_rejection_note }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
