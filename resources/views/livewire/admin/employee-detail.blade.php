<div class="space-y-5">

    {{-- ── Header Card ── --}}
    <div class="card p-6">
        <div class="flex flex-col sm:flex-row sm:items-start gap-5">

            {{-- Avatar --}}
            <div
                class="w-20 h-20 rounded-full shrink-0 overflow-hidden flex items-center justify-center
                        text-white text-2xl font-bold
                        {{ $employee->gender === 'female' ? 'bg-pink-400' : 'bg-violet-500' }}">
                @if ($employee->photo)
                    <img src="{{ Storage::url($employee->photo) }}" class="w-full h-full object-cover" alt="">
                @else
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $employee->name }}</h2>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $employee->activeAssignment?->position->name ?? 'Belum ada jabatan' }}
                            @if ($employee->activeAssignment)
                                <span class="mx-1 text-gray-300">·</span>
                                {{ $employee->activeAssignment->department->name }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $employee->school->name }}</p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Probation badge + eval button --}}
                        @if ($employee->status === 'probation')
                            <span class="badge badge-amber">Masa Percobaan</span>
                            @if ($employee->is_probation_overdue || $employee->probation_days_left <= 7)
                                <button wire:click="openProbationModal" class="btn-primary text-xs py-1.5 px-3">
                                    Evaluasi Sekarang
                                </button>
                            @else
                                <button wire:click="openProbationModal"
                                    class="btn-ghost text-xs py-1.5 px-3 border border-gray-300">
                                    Evaluasi Lebih Awal
                                </button>
                            @endif
                        @else
                            <span class="badge {{ $employee->status_color }}">
                                {{ $employee->status_label }}
                            </span>
                        @endif

                        <a href="{{ route('admin.employees.edit', $employee) }}"
                            class="btn-ghost text-xs py-1.5 px-3 border border-gray-300 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                            Edit
                        </a>

                        <button wire:click="confirmDelete"
                            class="btn-ghost text-xs py-1.5 px-3 border border-red-200 text-red-500 hover:bg-red-50 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                            Hapus
                        </button>

                        {{-- Modal Hapus --}}
                        @if ($showDeleteModal)
                            <div class="modal-backdrop" wire:click="$set('showDeleteModal',false)">
                                <div class="modal-box max-w-sm" wire:click.stop>
                                    <div class="modal-header"
                                        style="background:linear-gradient(to right,#dc2626,#b91c1c)">
                                        <h3>Hapus Pegawai?</h3>
                                        <button wire:click="$set('showDeleteModal',false)"
                                            class="text-white/70 hover:text-white">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <svg class="w-10 h-10 text-red-400 mx-auto mb-3" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        <p class="text-sm font-semibold text-gray-800 mb-1">
                                            Hapus {{ $employee->name }}?
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Data pegawai akan disembunyikan dari sistem. Riwayat tetap tersimpan dan
                                            dapat dipulihkan oleh admin.
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <button wire:click="$set('showDeleteModal',false)"
                                            class="btn-ghost">Batal</button>
                                        <button wire:click="delete" wire:loading.attr="disabled" class="btn-danger">
                                            <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                                            <span wire:loading wire:target="delete">Menghapus...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ID Info --}}
                <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-gray-100 text-xs">

                    {{-- NIK / NIPY / ID Sementara --}}
                    <div>
                        @if ($employee->nipy)
                            <span class="text-gray-400 block">NIPY</span>
                            <span class="font-mono font-bold text-violet-700">{{ $employee->nipy }}</span>
                        @elseif ($employee->status === 'probation')
                            <span class="text-gray-400 block">ID Sementara</span>
                            <span class="font-mono font-medium text-amber-600">{{ $employee->nik }}</span>
                            <span class="text-amber-500 block mt-0.5">Belum punya NIPY</span>
                        @else
                            <span class="text-gray-400 block">NIK</span>
                            <span class="font-mono font-medium text-gray-700">{{ $employee->nik }}</span>
                        @endif
                    </div>

                    {{-- Tipe --}}
                    <div>
                        <span class="text-gray-400 block">Tipe</span>
                        <span class="font-medium text-gray-700">{{ $employee->employee_type_label }}</span>
                    </div>

                    {{-- Peran --}}
                    <div>
                        <span class="text-gray-400 block">Peran</span>
                        <span class="font-medium {{ $employee->is_guru ? 'text-violet-600' : 'text-gray-700' }}">
                            {{ $employee->role_label }}
                        </span>
                    </div>

                    {{-- Tanggal Masuk --}}
                    <div>
                        <span class="text-gray-400 block">Tanggal Masuk</span>
                        <span class="font-medium text-gray-700">{{ $employee->join_date->format('d M Y') }}</span>
                    </div>

                    {{-- Akhir Masa Percobaan (hanya saat probation) --}}
                    @if ($employee->status === 'probation' && $employee->probation_end_date)
                        <div>
                            <span class="text-gray-400 block">Akhir Percobaan</span>
                            <span
                                class="font-medium {{ $employee->is_probation_overdue ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $employee->probation_end_date->format('d M Y') }}
                                @if ($employee->is_probation_overdue)
                                    <span class="text-red-500">(Lewat!)</span>
                                @elseif ($employee->probation_days_left !== null)
                                    <span class="text-amber-500">({{ $employee->probation_days_left }} hari
                                        lagi)</span>
                                @endif
                            </span>
                        </div>
                    @endif

                </div>
                {{-- Setelah baris info NIK/Tipe/Peran/Tanggal Masuk --}}
                @if ($employee->date_of_birth)
                    <div class="flex flex-wrap gap-4 mt-3 pt-3 border-t border-gray-100 text-xs">
                        <div>
                            <span class="text-gray-400 block">Usia</span>
                            <span class="font-medium text-gray-700">{{ $employee->age }} tahun</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Tanggal Pensiun</span>
                            <span class="font-medium {{ $employee->is_retired ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $employee->retirement_date->translatedFormat('d M Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Status Pensiun</span>
                            @if ($employee->is_retired)
                                <span class="badge-red">Sudah Pensiun</span>
                            @elseif($employee->years_to_retirement <= 1)
                                <span class="badge bg-orange-100 text-orange-700">
                                    Pensiun {{ $employee->retirement_date->diffForHumans() }}
                                </span>
                            @elseif($employee->years_to_retirement <= 3)
                                <span class="badge bg-yellow-100 text-yellow-700">
                                    {{ $employee->years_to_retirement }} tahun lagi
                                </span>
                            @else
                                <span class="font-medium text-gray-700">
                                    {{ $employee->years_to_retirement }} tahun lagi
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Kolom Kiri: Biodata + Kontak ── --}}
        <div class="space-y-5">

            {{-- Biodata --}}
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Identitas</h3>
                <div class="space-y-3 text-sm">
                    @foreach ([['NIK KTP', $employee->national_id ?? '—'], ['Jenis Kelamin', $employee->gender_label], ['Tempat Lahir', $employee->place_of_birth ?? '—'], ['Tanggal Lahir', $employee->date_of_birth?->format('d M Y') ?? '—'], ['Agama', ucfirst($employee->religion ?? '—')], ['Status Nikah', ucfirst(str_replace('_', ' ', $employee->marital_status ?? '—'))], ['Kewarganegaraan', $employee->nationality]] as [$label, $val])
                        <div class="flex justify-between gap-2">
                            <span class="text-gray-400 shrink-0">{{ $label }}</span>
                            <span class="font-medium text-gray-700 text-right">{{ $val }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kontak --}}
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Kontak</h3>
                <div class="space-y-3 text-sm">
                    @foreach ([['Email', $employee->email ?? '—'], ['Telepon', $employee->phone ?? '—'], ['Alamat', $employee->address ?? '—']] as [$label, $val])
                        <div>
                            <span class="text-gray-400 text-xs block">{{ $label }}</span>
                            <span class="font-medium text-gray-700">{{ $val }}</span>
                        </div>
                    @endforeach

                    @if ($employee->emergency_contact_name)
                        <div class="border-t border-gray-100 pt-3">
                            <span class="text-gray-400 text-xs block mb-1">Kontak Darurat</span>
                            <p class="font-medium text-gray-700">{{ $employee->emergency_contact_name }}</p>
                            <p class="text-xs text-gray-500">{{ $employee->emergency_contact_phone }}
                                @if ($employee->emergency_contact_relation)
                                    · {{ $employee->emergency_contact_relation }}
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pendidikan --}}
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Pendidikan Terakhir</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-gray-400 text-xs block">Jenjang</span>
                        <span class="font-medium text-gray-700">{{ $employee->last_education_label }}</span>
                    </div>
                    @if ($employee->last_education_major)
                        <div>
                            <span class="text-gray-400 text-xs block">Jurusan</span>
                            <span class="font-medium text-gray-700">{{ $employee->last_education_major }}</span>
                        </div>
                    @endif
                    @if ($employee->last_education_institution)
                        <div>
                            <span class="text-gray-400 text-xs block">Institusi</span>
                            <span class="font-medium text-gray-700">{{ $employee->last_education_institution }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Kolom Kanan: Jabatan + Riwayat ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Jabatan Aktif + tombol mutasi --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jabatan Aktif</h3>
                    @if ($employee->status === 'active')
                        <button wire:click="openAssignModal" class="btn-primary text-xs py-1.5 px-3">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Mutasi / Promosi
                        </button>
                    @endif
                </div>

                @if ($employee->activeAssignment)
                    <div class="flex items-start gap-4 p-4 bg-violet-50 border border-violet-200 rounded-xl">
                        <div class="w-10 h-10 rounded-lg bg-violet-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $employee->activeAssignment->position->name }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $employee->activeAssignment->department->name }}
                                <span class="mx-1">·</span>
                                {{ $employee->school->name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                Mulai {{ $employee->activeAssignment->start_date->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6 text-gray-400">
                        <p class="text-sm">Belum ada jabatan aktif</p>
                    </div>
                @endif
            </div>
            @livewire('admin.additional-assignment', ['employee' => $employee])
            {{-- Timeline Riwayat Jabatan --}}
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-5">
                    Riwayat Jabatan
                </h3>

                @php
                    $assignments = $employee
                        ->positionAssignments()
                        ->with(['position', 'department'])
                        ->orderByDesc('start_date')
                        ->get();
                @endphp

                @if ($assignments->count())
                    <div class="relative">
                        {{-- Vertical line --}}
                        <div class="absolute left-3.5 top-0 bottom-0 w-px bg-gray-200"></div>

                        <div class="space-y-4">
                            @foreach ($assignments as $assign)
                                <div class="flex gap-4 relative">
                                    {{-- Dot --}}
                                    <div
                                        class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center z-10
                                        {{ $assign->is_active ? 'bg-violet-600 border-2 border-white shadow-md' : 'bg-gray-300 border-2 border-white' }}">
                                        @if ($assign->is_active)
                                            <div class="w-2 h-2 rounded-full bg-white"></div>
                                        @endif
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 pb-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="font-medium text-gray-800 text-sm">
                                                    {{ $assign->position->name }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $assign->department->name }}
                                                </p>
                                            </div>
                                            <span
                                                class="badge shrink-0 text-xs
                                                 {{ match ($assign->type) {
                                                     'promotion' => 'badge-green',
                                                     'mutation' => 'badge-blue',
                                                     'demotion' => 'badge-red',
                                                     default => 'badge-gray',
                                                 } }}">
                                                {{ $assign->type_label }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ $assign->start_date->format('d M Y') }}
                                            @if ($assign->end_date)
                                                — {{ $assign->end_date->format('d M Y') }}
                                            @else
                                                — <span class="text-green-600 font-medium">Sekarang</span>
                                            @endif
                                        </p>
                                        @if ($assign->notes)
                                            <p class="text-xs text-gray-400 italic mt-0.5">{{ $assign->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada riwayat jabatan</p>
                @endif
            </div>

            {{-- Riwayat Status --}}
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
                    Riwayat Status Kepegawaian
                </h3>
                @php
                    $histories = $employee->statusHistories()->with('recordedBy')->orderByDesc('effective_date')->get();
                @endphp

                @if ($histories->count())
                    <div class="space-y-2">
                        @foreach ($histories as $h)
                            <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 text-sm">
                                <div class="shrink-0 mt-0.5">
                                    <span
                                        class="badge {{ match ($h->status) {
                                            'active' => 'badge-green',
                                            'probation' => 'badge-amber',
                                            'terminated' => 'badge-red',
                                            default => 'badge-gray',
                                        } }}">
                                        {{ ucfirst(str_replace('_', ' ', $h->status)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-gray-600 text-xs">{{ $h->notes }}</p>
                                    <p class="text-gray-400 text-xs mt-0.5">
                                        {{ $h->effective_date->format('d M Y') }}
                                        @if ($h->recordedBy)
                                            · oleh {{ $h->recordedBy->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada riwayat</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ MODAL: Mutasi / Promosi ══ --}}
    @if ($showAssignModal)
        <div class="modal-backdrop" wire:click="$set('showAssignModal',false)">
            <div class="modal-box max-w-md" wire:click.stop>
                <div class="modal-header">
                    <h3>Perubahan Jabatan</h3>
                    <button wire:click="$set('showAssignModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <label class="form-label">Jenis Perubahan <span class="text-red-500">*</span></label>
                        <select wire:model="assign_type" class="input">
                            <option value="mutation">Mutasi (pindah jabatan setara)</option>
                            <option value="promotion">Promosi (naik jabatan)</option>
                            <option value="demotion">Demosi (turun jabatan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Departemen <span class="text-red-500">*</span></label>
                        <select wire:model.live="assign_dept_id"
                            class="input @error('assign_dept_id') input-error @enderror">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach ($assignDepts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('assign_dept_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Jabatan Baru <span class="text-red-500">*</span></label>
                        <select wire:model="assign_pos_id" class="input @error('assign_pos_id') input-error @enderror"
                            {{ empty($assign_dept_id) ? 'disabled' : '' }}>
                            <option value="">
                                {{ empty($assign_dept_id) ? '-- Pilih dept dulu --' : '-- Pilih Jabatan --' }}</option>
                            @foreach ($assignPositions as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('assign_pos_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Tanggal Berlaku <span class="text-red-500">*</span></label>
                        <input wire:model="assign_start_date" type="date"
                            class="input @error('assign_start_date') input-error @enderror">
                        @error('assign_start_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Catatan</label>
                        <textarea wire:model="assign_notes" rows="2" class="input resize-none"
                            placeholder="Alasan mutasi/promosi (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showAssignModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="saveAssignment" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="saveAssignment">Simpan</span>
                        <span wire:loading wire:target="saveAssignment">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL: Evaluasi Masa Percobaan ══ --}}
    @if ($showProbationModal)
        <div class="modal-backdrop" wire:click="$set('showProbationModal',false)">
            <div class="modal-box max-w-md" wire:click.stop>
                <div class="modal-header">
                    <h3>Evaluasi Masa Percobaan</h3>
                    <button wire:click="$set('showProbationModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Info pegawai --}}
                    <div class="p-3 bg-gray-50 rounded-lg text-sm">
                        <p class="font-medium text-gray-800">{{ $employee->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $employee->role_label }}
                            · Percobaan {{ $employee->probation_duration_label }}
                            · Berakhir {{ $employee->probation_end_date?->format('d M Y') ?? '—' }}
                        </p>
                    </div>

                    {{-- Keputusan --}}
                    <div>
                        <label class="form-label">Keputusan <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input wire:model="probation_decision" type="radio" value="passed"
                                    class="sr-only peer">
                                <div
                                    class="p-3 border-2 rounded-xl text-center text-sm transition
                                        peer-checked:border-green-500 peer-checked:bg-green-50
                                        border-gray-200 hover:border-gray-300">
                                    <p class="font-semibold text-green-700">✓ Lulus</p>
                                    <p class="text-xs text-gray-400 mt-0.5">NIPY akan diterbitkan</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input wire:model="probation_decision" type="radio" value="failed"
                                    class="sr-only peer">
                                <div
                                    class="p-3 border-2 rounded-xl text-center text-sm transition
                                        peer-checked:border-red-500 peer-checked:bg-red-50
                                        border-gray-200 hover:border-gray-300">
                                    <p class="font-semibold text-red-600">✗ Tidak Lulus</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Status diberhentikan</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Preview NIPY --}}
                    @if ($probation_decision === 'passed')
                        <div class="p-3 bg-violet-50 border border-violet-200 rounded-lg">
                            <p class="text-xs text-violet-700 font-semibold mb-1">Preview NIPY yang akan diterbitkan:
                            </p>
                            <p class="font-mono text-base font-bold text-violet-800">{{ $nipyPreview }}</p>
                            <p class="text-xs text-violet-500 mt-1">
                                {{ $employee->join_date->format('y') }} (tahun)
                                + {{ \App\Services\NipyGenerator::getEducationCode($employee->last_education) }}
                                (pendidikan)
                                +
                                {{ \App\Services\NipyGenerator::getEmploymentCode($employee->is_guru, $employee->employee_type) }}
                                (jenis)
                                + nomor urut otomatis
                            </p>
                        </div>
                    @endif

                    {{-- Catatan --}}
                    <div>
                        <label class="form-label">Catatan Evaluasi</label>
                        <textarea wire:model="probation_notes" rows="3" class="input resize-none"
                            placeholder="Catatan hasil evaluasi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showProbationModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="submitEvaluation" wire:loading.attr="disabled"
                        class="{{ $probation_decision === 'passed' ? 'bg-green-600 hover:bg-green-700' : 'btn-danger' }}
                               btn text-white">
                        <span wire:loading.remove wire:target="submitEvaluation">
                            {{ $probation_decision === 'passed' ? 'Luluskan & Terbitkan NIPY' : 'Tidak Luluskan' }}
                        </span>
                        <span wire:loading wire:target="submitEvaluation">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
