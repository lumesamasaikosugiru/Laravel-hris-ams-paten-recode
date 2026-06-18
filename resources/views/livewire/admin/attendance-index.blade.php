<div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="card p-4">
            <p class="text-2xl font-bold text-green-600">{{ $todaySummary['present'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Hadir Tepat Waktu</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-yellow-600">{{ $todaySummary['late'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Terlambat</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-red-500">{{ $todaySummary['absent'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Tidak Hadir</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-600">{{ $todaySummary['total'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Pegawai Aktif</p>
        </div>
    </div>

    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live="dateFilter" type="date" class="input w-auto">
            <select wire:model.live="schoolFilter" class="input w-auto">
                <option value="">Semua Unit</option>
                @foreach ($schools as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="input w-auto">
                <option value="">Semua Status</option>
                <option value="present">Hadir</option>
                <option value="late">Terlambat</option>
                <option value="absent">Tidak Hadir</option>
                <option value="leave">Cuti/Izin</option>
            </select>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari pegawai..."
                class="input w-48">
        </div>

        {{-- Hanya tampil jika punya izin INPUT (create), bukan sekadar lihat (view) --}}
        @can('attendance.create')
            <button wire:click="openModal" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Input Absensi
            </button>
        @endcan
    </div>

    <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
        Jam kerja standar: {{ \App\Models\Attendance::WORK_START }} — {{ \App\Models\Attendance::WORK_END }} WIB
        · Terlambat jika check-in setelah {{ \App\Models\Attendance::WORK_START }}
    </div>

    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Pegawai</th>
                    <th class="hidden md:table-cell">Jabatan</th>
                    <th class="text-center">Check In</th>
                    <th class="text-center">Check Out</th>
                    <th class="text-center hidden lg:table-cell">Jam Kerja</th>
                    <th class="text-center hidden lg:table-cell">Terlambat</th>
                    <th class="text-center">Status</th>
                    {{-- Kolom Aksi hanya relevan jika ada hak edit --}}
                    @can('attendance.edit')
                        <th class="text-center">Aksi</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $attendances->firstItem() + $loop->index }}</td>
                        <td>
                            <p class="font-medium text-gray-800">{{ $att->employee->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">
                                {{ $att->employee->nipy ?? $att->employee->nik }}</p>
                        </td>
                        <td class="text-sm text-gray-500 hidden md:table-cell">
                            {{ $att->employee->activeAssignment?->position->name ?? '—' }}</td>
                        <td
                            class="text-center text-sm font-medium {{ $att->status === 'late' ? 'text-yellow-600' : 'text-gray-700' }}">
                            {{ $att->check_in ?? '—' }}</td>
                        <td class="text-center text-sm text-gray-600">{{ $att->check_out ?? '—' }}</td>
                        <td class="text-center text-sm text-gray-600 hidden lg:table-cell">{{ $att->work_hours }}</td>
                        <td class="text-center hidden lg:table-cell">
                            @if ($att->late_minutes > 0)
                                <span class="text-xs text-yellow-600 font-medium">+{{ $att->late_minutes }}m</span>
                            @else<span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="text-center"><span
                                class="badge {{ $att->status_color }}">{{ $att->status_label }}</span></td>
                        {{-- Tombol edit hanya tampil jika punya izin attendance.edit --}}
                        @can('attendance.edit')
                            <td class="text-center">
                                <button wire:click="openEdit({{ $att->id }})"
                                    class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </button>
                            </td>
                        @endcan
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-16 text-center">
                            <p class="text-sm font-medium text-gray-400">Belum ada data absensi untuk tanggal
                                {{ \Carbon\Carbon::parse($dateFilter)->translatedFormat('d F Y') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($attendances->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $attendances->links() }}</div>
        @endif
    </div>

    {{-- ══ MODAL: Input Absensi Manual ══ --}}
    @can('attendance.create')
        @if ($showModal)
            <div class="modal-backdrop" wire:click="$set('showModal',false)">
                <div class="modal-box max-w-md" wire:click.stop>
                    <div class="modal-header">
                        <h3>Input Absensi Manual</h3>
                        <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">

                        {{-- Alpine Combobox Pegawai --}}
                        <div x-data="{
                            open: false,
                            search: '',
                            selected: null,
                            activeIndex: -1,
                            employees: {{ Js::from($activeEmployees->map(fn($e) => ['id' => $e->id, 'name' => $e->name, 'code' => $e->nipy ?? $e->nik])) }},
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
                                    const el = document.getElementById('combo-item-' + this.activeIndex);
                                    if (el) el.scrollIntoView({ block: 'nearest' });
                                });
                            },
                            confirmSelection() {
                                if (this.activeIndex >= 0 && this.filtered[this.activeIndex]) {
                                    this.select(this.filtered[this.activeIndex]);
                                } else if (this.filtered.length === 1) {
                                    this.select(this.filtered[0]);
                                }
                            }
                        }"x-on:click.outside="open = false">
                            <label class="form-label">Pegawai <span class="text-red-500">*</span></label>

                            <div class="relative">
                                <input type="text" x-model="search" x-on:focus="open = true; activeIndex = -1"
                                    x-on:input="open = true; activeIndex = -1"
                                    x-on:keydown.arrow-down.prevent="moveDown()" x-on:keydown.arrow-up.prevent="moveUp()"
                                    x-on:keydown.enter.prevent="confirmSelection()"
                                    x-on:keydown.escape="open = false; activeIndex = -1"
                                    placeholder="Ketik nama atau NIK..."
                                    class="input pr-8 @error('selectedEmployeeId') input-error @enderror"
                                    autocomplete="off">

                                {{-- Clear button --}}
                                <button x-show="search" x-on:click="clear()" type="button"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                {{-- Dropdown hasil --}}
                                <div x-show="open && filtered.length > 0" x-transition
                                    class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-52 overflow-y-auto">
                                    <template x-for="(emp, index) in filtered" :key="emp.id">
                                        <div :id="'combo-item-' + index" x-on:click="select(emp)"
                                            x-on:mouseover="activeIndex = index"
                                            :class="activeIndex === index ?
                                                'bg-violet-100 text-violet-800' :
                                                'hover:bg-violet-50'"
                                            class="px-3 py-2.5 cursor-pointer border-b border-gray-50 last:border-0 flex items-center justify-between transition-colors">
                                            <span class="text-sm font-medium" x-text="emp.name"></span>
                                            <span class="text-xs text-gray-400 font-mono ml-2" x-text="emp.code"></span>
                                        </div>
                                    </template>
                                </div>

                                {{-- No results --}}
                                <div x-show="open && search && filtered.length === 0"
                                    class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 px-3 py-3 text-sm text-gray-400 text-center">
                                    Tidak ditemukan
                                </div>
                            </div>

                            {{-- Konfirmasi terpilih --}}
                            <div x-show="selected" class="mt-1.5 flex items-center gap-2 text-xs text-green-600">
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

                        <div>
                            <label class="form-label">Tanggal <span class="text-red-500">*</span></label>
                            <input wire:model="manualDate" type="date" class="input">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Jam Masuk <span class="text-red-500">*</span></label>
                                <input wire:model="manualCheckIn" type="time" class="input">
                            </div>
                            <div>
                                <label class="form-label">Jam Keluar</label>
                                <input wire:model="manualCheckOut" type="time" class="input">
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Catatan</label>
                            <input wire:model="manualNotes" type="text" class="input"
                                placeholder="Keterangan (opsional)">
                        </div>

                        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
                            Status dihitung otomatis. Terlambat jika check-in setelah
                            <strong>{{ \App\Models\Attendance::WORK_START }}</strong>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="$set('showModal',false)" class="btn-ghost">Batal</button>
                        <button wire:click="saveManual" wire:loading.attr="disabled" class="btn-primary">
                            <span wire:loading.remove wire:target="saveManual">Simpan</span>
                            <span wire:loading wire:target="saveManual">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endcan

    {{-- ══ MODAL: Edit Absensi ══ --}}
    @can('attendance.edit')
        @if ($showEditModal)
            <div class="modal-backdrop" wire:click="$set('showEditModal',false)">
                <div class="modal-box max-w-md" wire:click.stop>
                    <div class="modal-header">
                        <h3>Edit Absensi — {{ $editingEmployee?->name }}</h3>
                        <button wire:click="$set('showEditModal',false)" class="text-white/70 hover:text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div><label class="form-label">Tanggal</label><input wire:model="manualDate" type="date"
                                class="input" disabled></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="form-label">Jam Masuk</label><input wire:model="manualCheckIn"
                                    type="time" class="input"></div>
                            <div><label class="form-label">Jam Keluar</label><input wire:model="manualCheckOut"
                                    type="time" class="input"></div>
                        </div>
                        <div><label class="form-label">Catatan</label><input wire:model="manualNotes" type="text"
                                class="input"></div>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="$set('showEditModal',false)" class="btn-ghost">Batal</button>
                        <button wire:click="updateAttendance" wire:loading.attr="disabled" class="btn-primary">
                            <span wire:loading.remove wire:target="updateAttendance">Simpan</span>
                            <span wire:loading wire:target="updateAttendance">Menyimpan...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endcan
</div>
