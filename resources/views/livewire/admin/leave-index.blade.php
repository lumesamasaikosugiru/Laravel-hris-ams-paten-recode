<div>
    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="card p-4 cursor-pointer hover:border-violet-300 transition {{ $statusFilter === 'pending' ? 'border-violet-400 bg-violet-50' : '' }}"
            wire:click="$set('statusFilter','pending')">
            <p class="text-2xl font-bold text-yellow-600">{{ $summary['pending'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Menunggu Persetujuan</p>
        </div>
        <div class="card p-4 cursor-pointer hover:border-violet-300 transition {{ $statusFilter === 'approved' ? 'border-violet-400 bg-violet-50' : '' }}"
            wire:click="$set('statusFilter','approved')">
            <p class="text-2xl font-bold text-green-600">{{ $summary['approved'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Disetujui ({{ now()->year }})</p>
        </div>
        <div class="card p-4 cursor-pointer hover:border-violet-300 transition {{ $statusFilter === '' ? 'border-violet-400 bg-violet-50' : '' }}"
            wire:click="$set('statusFilter','')">
            <p class="text-2xl font-bold text-gray-600">
                {{ $summary['pending'] + $summary['approved'] + $summary['rejected'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Pengajuan</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama pegawai..."
                class="input w-56">
            <select wire:model.live="statusFilter" class="input w-auto">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
            </select>
            <select wire:model.live="schoolFilter" class="input w-auto">
                <option value="">Semua Unit</option>
                @foreach ($schools as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                @endforeach
            </select>
            <input wire:model.live="monthFilter" type="month" class="input w-auto">
        </div>
        <button wire:click="openRequestModal" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Ajukan Cuti
        </button>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Pegawai</th>
                    <th class="hidden md:table-cell">Jenis Cuti</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center hidden lg:table-cell">Hari</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $requests->firstItem() + $loop->index }}</td>
                        <td>
                            <p class="font-medium text-gray-800">{{ $req->employee->name }}</p>
                            <p class="text-xs text-gray-400">{{ $req->employee->school->name }}</p>
                        </td>
                        <td class="hidden md:table-cell">
                            <span class="badge-purple">{{ $req->leaveType->name }}</span>
                        </td>
                        <td class="text-center text-sm text-gray-600">
                            <p>{{ $req->start_date->format('d M Y') }}</p>
                            @if ($req->start_date->ne($req->end_date))
                                <p class="text-xs text-gray-400">s/d {{ $req->end_date->format('d M Y') }}</p>
                            @endif
                        </td>
                        <td class="text-center hidden lg:table-cell">
                            <span class="badge-green font-semibold">{{ $req->days }} hari</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $req->status_color }}">{{ $req->status_label }}</span>
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Detail --}}
                                <button wire:click="openDetail({{ $req->id }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                                {{-- Approve --}}
                                @if ($req->status === 'pending')
                                    <button wire:click="openApproveModal({{ $req->id }},'approved')"
                                        class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 transition"
                                        title="Setujui">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </button>
                                    <button wire:click="openApproveModal({{ $req->id }},'rejected')"
                                        class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition" title="Tolak">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <p class="text-sm font-medium text-gray-400">Belum ada pengajuan cuti</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($requests->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $requests->links() }}</div>
        @endif
    </div>

    {{-- ══ MODAL: Ajukan Cuti ══ --}}
    @if ($showRequestModal)
        <div class="modal-backdrop" wire:click="$set('showRequestModal',false)">
            <div class="modal-box max-w-lg max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="modal-header sticky top-0 z-10">
                    <h3>Ajukan Cuti</h3>
                    <button wire:click="$set('showRequestModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Pilih Pegawai --}}
                    <div x-data="{
                        open: false,
                        search: '',
                        selected: null,
                        activeIndex: -1,
                        employees: {{ Js::from($employees) }},
                        get filtered() {
                            if (!this.search) return this.employees.slice(0, 50);
                            const q = this.search.toLowerCase();
                            return this.employees.filter(e =>
                                e.name.toLowerCase().includes(q) || e.code.toLowerCase().includes(q)
                            ).slice(0, 50);
                        },
                        select(emp) {
                            this.selected = emp;
                            this.search = emp.name;
                            this.open = false;
                            this.activeIndex = -1;
                            $wire.set('selectedEmployeeId', emp.id);
                            $wire.set('selectedGender', emp.gender);
                        },
                        clear() {
                            this.selected = null;
                            this.search = '';
                            this.activeIndex = -1;
                            $wire.set('selectedEmployeeId', null);
                        },
                        moveDown() {
                            if (!this.open) { this.open = true; return; }
                            this.activeIndex = Math.min(this.activeIndex + 1, this.filtered.length - 1);
                            this.scrollActive();
                        },
                        moveUp() {
                            this.activeIndex = Math.max(this.activeIndex - 1, 0);
                            this.scrollActive();
                        },
                        scrollActive() {
                            this.$nextTick(() => {
                                const el = document.getElementById('leave-item-' + this.activeIndex);
                                if (el) el.scrollIntoView({ block: 'nearest' });
                            });
                        }
                    }" x-on:click.outside="open = false">
                        <label class="form-label">Pegawai <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" x-model="search" x-on:focus="open = true; activeIndex = -1"
                                x-on:input="open = true; activeIndex = -1"
                                x-on:keydown.arrow-down.prevent="moveDown()" x-on:keydown.arrow-up.prevent="moveUp()"
                                x-on:keydown.enter.prevent="filtered[activeIndex] ? select(filtered[activeIndex]) : (filtered.length === 1 ? select(filtered[0]) : null)"
                                x-on:keydown.escape="open = false" placeholder="Ketik nama atau NIK..."
                                class="input pr-8 @error('selectedEmployeeId') input-error @enderror"
                                autocomplete="off">
                            <button x-show="search" x-on:click="clear()" type="button"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div x-show="open && filtered.length > 0" x-transition
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-52 overflow-y-auto">
                                <template x-for="(emp, index) in filtered" :key="emp.id">
                                    <div :id="'leave-item-' + index" x-on:click="select(emp)"
                                        x-on:mouseover="activeIndex = index"
                                        :class="activeIndex === index ? 'bg-violet-100 text-violet-800' : 'hover:bg-violet-50'"
                                        class="px-3 py-2.5 cursor-pointer border-b border-gray-50 last:border-0 flex items-center justify-between transition-colors">
                                        <span class="text-sm font-medium" x-text="emp.name"></span>
                                        <span class="text-xs text-gray-400 font-mono ml-2" x-text="emp.code"></span>
                                    </div>
                                </template>
                            </div>
                            <div x-show="open && search && filtered.length === 0"
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 px-3 py-3 text-sm text-gray-400 text-center">
                                Tidak ditemukan
                            </div>
                        </div>
                        <div x-show="selected" class="mt-1.5 flex items-center gap-1.5 text-xs text-green-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span x-text="selected ? selected.name + ' (' + selected.code + ')' : ''"></span>
                        </div>
                        @error('selectedEmployeeId')
                            <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jenis Cuti --}}
                    <div>
                        <label class="form-label">Jenis Cuti <span class="text-red-500">*</span></label>
                        <select wire:model.live="leave_type_id"
                            class="input @error('leave_type_id') input-error @enderror">
                            <option value="">-- Pilih Jenis Cuti --</option>
                            @foreach ($leaveTypes as $lt)
                                {{-- Filter berdasarkan gender --}}
                                @if ($lt->gender === 'all' || $lt->gender === $selectedGender || $selectedGender === '')
                                    <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->quota }}
                                        hari/tahun)</option>
                                @endif
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info saldo --}}
                    @if ($selectedBalance)
                        <div
                            class="p-3 {{ $selectedBalance['remaining'] > 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded-lg text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Kuota</span>
                                <span class="font-medium">{{ $selectedBalance['quota'] }} hari</span>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-gray-600">Sudah dipakai</span>
                                <span class="font-medium text-red-600">{{ $selectedBalance['used'] }} hari</span>
                            </div>
                            <div class="flex justify-between mt-1 border-t border-gray-200 pt-1">
                                <span class="font-semibold text-gray-700">Sisa</span>
                                <span
                                    class="font-bold {{ $selectedBalance['remaining'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $selectedBalance['remaining'] }} hari
                                </span>
                            </div>
                        </div>
                    @elseif($selectedEmployeeId && $leave_type_id)
                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-700">
                            Saldo belum di-generate. Buat saldo terlebih dahulu di menu Saldo Cuti.
                        </div>
                    @endif

                    {{-- Tanggal --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input wire:model.live="start_date" type="date"
                                class="input @error('start_date') input-error @enderror">
                            <p class="text-xs text-gray-400 mt-1">
                                Pengajuan minimal H-{{ \App\Services\LeaveService::MIN_DAYS_BEFORE }}.
                                Paling cepat:
                                <strong>{{ \Carbon\Carbon::parse(\App\Services\LeaveService::minStartDate())->translatedFormat('d M Y') }}</strong>
                            </p>
                            @error('start_date')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input wire:model.live="end_date" type="date"
                                class="input @error('end_date') input-error @enderror" min="{{ $start_date }}"
                                max="{{ $maxEndDate }}">
                            @error('end_date')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            @if ($maxEndDate && $leave_type_id)
                                <p class="text-xs text-amber-600 mt-1">
                                    Maksimal hingga {{ \Carbon\Carbon::parse($maxEndDate)->translatedFormat('d M Y') }}
                                    (berdasarkan sisa saldo)
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($calculatedDays > 0)
                        <div
                            class="p-2 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700 text-center">
                            <strong>{{ $calculatedDays }} hari kerja</strong> (Senin–Jumat, tidak termasuk weekend)
                        </div>
                    @endif
                    @error('days')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    {{-- Alasan --}}
                    <div>
                        <label class="form-label">Alasan <span class="text-red-500">*</span></label>
                        <textarea wire:model="reason" rows="3" class="input resize-none @error('reason') input-error @enderror"
                            placeholder="Jelaskan alasan pengajuan cuti..."></textarea>
                        @error('reason')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Upload dokumen --}}
                    <div>
                        <label class="form-label">Dokumen Pendukung</label>
                        <input wire:model="document_file" type="file" accept=".pdf,.jpg,.jpeg,.png"
                            class="text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 file:transition">
                        <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG — maks. 5MB</p>
                        @error('document_file')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showRequestModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="saveRequest" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="saveRequest">Kirim Pengajuan</span>
                        <span wire:loading wire:target="saveRequest">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL: Approve/Reject ══ --}}
    @if ($showApproveModal)
        <div class="modal-backdrop" wire:click="$set('showApproveModal',false)">
            <div class="modal-box max-w-sm" wire:click.stop>
                <div class="modal-header"
                    style="background: linear-gradient(to right, {{ $approveAction === 'approved' ? '#16a34a,#15803d' : '#dc2626,#b91c1c' }})">
                    <h3>{{ $approveAction === 'approved' ? 'Setujui Cuti' : 'Tolak Cuti' }}</h3>
                    <button wire:click="$set('showApproveModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <label class="form-label">
                            Catatan {{ $approveAction === 'rejected' ? '(wajib)' : '(opsional)' }}
                        </label>
                        <textarea wire:model="approverNotes" rows="3"
                            class="input resize-none @error('approverNotes') input-error @enderror"
                            placeholder="{{ $approveAction === 'rejected' ? 'Alasan penolakan...' : 'Catatan tambahan (opsional)...' }}"></textarea>
                        @error('approverNotes')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    @if ($approveAction === 'approved')
                        <div class="p-2.5 bg-green-50 border border-green-200 rounded-lg text-xs text-green-700">
                            Saldo cuti akan dipotong otomatis dan absensi akan diupdate.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showApproveModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="processLeave" wire:loading.attr="disabled"
                        class="{{ $approveAction === 'approved' ? 'btn' : 'btn-danger' }} text-white"
                        style="{{ $approveAction === 'approved' ? 'background:#16a34a' : '' }}">
                        <span wire:loading.remove wire:target="processLeave">
                            {{ $approveAction === 'approved' ? 'Ya, Setujui' : 'Ya, Tolak' }}
                        </span>
                        <span wire:loading wire:target="processLeave">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL: Detail ══ --}}
    @if ($showDetailModal && $viewing)
        <div class="modal-backdrop" wire:click="$set('showDetailModal',false)">
            <div class="modal-box max-w-md" wire:click.stop>
                <div class="modal-header">
                    <h3>Detail Pengajuan Cuti</h3>
                    <button wire:click="$set('showDetailModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">Pegawai</span><span
                            class="font-medium">{{ $viewing->employee->name }}</span></div>
                    <div class="flex justify-between"><span
                            class="text-gray-400">Unit</span><span>{{ $viewing->employee->school->name }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Jenis Cuti</span><span
                            class="badge-purple">{{ $viewing->leaveType->name }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Tanggal
                            Mulai</span><span>{{ $viewing->start_date->format('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Tanggal
                            Selesai</span><span>{{ $viewing->end_date->format('d M Y') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Jumlah Hari</span><span
                            class="badge-green font-semibold">{{ $viewing->days }} hari kerja</span></div>
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-gray-400 text-xs mb-1">Alasan</p>
                        <p class="text-gray-700">{{ $viewing->reason }}</p>
                    </div>
                    @if ($viewing->document_file)
                        <div>
                            <a href="{{ Storage::url($viewing->document_file) }}" target="_blank"
                                class="text-xs text-violet-600 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                Lihat Dokumen Pendukung
                            </a>
                        </div>
                    @endif
                    <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                        <span class="text-gray-400">Status</span>
                        <span class="badge {{ $viewing->status_color }}">{{ $viewing->status_label }}</span>
                    </div>
                    @if ($viewing->approvedBy)
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>{{ $viewing->status === 'approved' ? 'Disetujui' : 'Ditolak' }} oleh</span>
                            <span>{{ $viewing->approvedBy->name }} ·
                                {{ $viewing->approved_at?->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if ($viewing->approver_notes)
                        <div class="p-2.5 bg-gray-50 rounded-lg text-xs text-gray-600">
                            <p class="font-semibold mb-0.5">Catatan:</p>
                            {{ $viewing->approver_notes }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showDetailModal',false)" class="btn-ghost">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
