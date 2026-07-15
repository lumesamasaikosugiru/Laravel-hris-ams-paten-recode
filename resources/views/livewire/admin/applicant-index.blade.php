<div>
    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..."
                class="input w-60">
            <select wire:model.live="jobFilter" class="input w-auto">
                <option value="">Semua Lowongan</option>
                @foreach ($jobs as $job)
                    <option value="{{ $job->id }}">{{ $job->title }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="input w-auto">
                <option value="">Semua Status</option>
                <option value="submitted">Lamaran Masuk</option>
                <option value="tes_berkas">Verifikasi Berkas</option>
                <option value="tes_potensi">Tes Potensi</option>
                <option value="diterima">Diterima</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>
        @can('recruitment.create')
            <button wire:click="openWalkInModal" class="btn-primary gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Pelamar
            </button>
        @endcan
    </div>

    {{-- Pipeline Count --}}
    <div class="grid grid-cols-5 gap-2 mb-5">
        @foreach ([['submitted', 'Lamaran Masuk', 'badge-blue'], ['tes_berkas', 'Verifikasi Berkas', 'badge-amber'], ['tes_potensi', 'Tes Potensi', 'badge-purple'], ['diterima', 'Diterima', 'badge-green'], ['ditolak', 'Ditolak', 'badge-red']] as [$status, $label, $badgeClass])
            <button wire:click="$set('statusFilter','{{ $status }}')"
                class="card p-3 text-center hover:border-violet-300 transition cursor-pointer
                       {{ $statusFilter === $status ? 'border-violet-400 bg-violet-50' : '' }}">
                <p class="text-xl font-bold text-gray-700">
                    {{ \App\Models\Applicant::where('status', $status)->count() }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Pelamar</th>
                    <th class="hidden md:table-cell">Lowongan</th>
                    <th class="text-center hidden lg:table-cell">Pendidikan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Pipeline</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applicants as $app)
                    <tr onclick="Livewire.all().find(c => c.name === 'admin.applicant-index')?.$wire.openDetail({{ $app->id }})"
                        class="cursor-pointer hover:bg-violet-50 transition-colors">
                        <td class="text-gray-400 text-xs">{{ $applicants->firstItem() + $loop->index }}</td>
                        <td>
                            <p class="font-medium text-gray-800">{{ $app->name }}</p>
                            <p class="text-xs text-gray-400">{{ $app->email }}</p>
                            @if ($app->is_walk_in)
                                <span class="badge badge-blue text-[10px] mt-0.5">Walk-in</span>
                            @endif
                            @if ($app->is_converted)
                                <span class="text-xs text-green-600 font-medium">✓ Sudah jadi pegawai</span>
                            @endif
                        </td>
                        <td class="hidden md:table-cell">
                            @if ($app->is_walk_in)
                                <p class="text-sm text-gray-700">{{ $app->applied_position }}</p>
                                <p class="text-xs text-gray-400 italic">Lamaran langsung</p>
                            @else
                                <p class="text-sm text-gray-700">{{ $app->jobVacancy?->title ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $app->jobVacancy?->school?->name ?? '-' }}</p>
                            @endif
                        </td>
                        <td class="text-center hidden lg:table-cell">
                            <span class="badge-code">{{ $app->last_education_label }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $app->status_color }}">{{ $app->status_label }}</span>
                        </td>
                        <td class="text-center">
                            @if (!$app->is_converted && $app->status !== 'ditolak')
                                @if ($app->is_walk_in)
                                    {{-- Walk-in: pipeline simpel, langsung Diterima/Ditolak --}}
                                    <select wire:change="updateStatus({{ $app->id }}, $event.target.value)"
                                        onclick="event.stopPropagation()" class="text-xs rounded-lg border ...">
                                        @foreach (['submitted' => 'Lamaran Masuk', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'] as $val => $lbl)
                                            <option value="{{ $val }}"
                                                {{ $app->status === $val ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    {{-- Public form: pipeline penuh 4 tahap --}}
                                    <select wire:change="updateStatus({{ $app->id }}, $event.target.value)"
                                        onclick="event.stopPropagation()" class="text-xs rounded-lg border ...">
                                        @foreach ([
        'submitted' => 'Lamaran Masuk',
        'tes_berkas' => 'Verifikasi Berkas',
        'tes_potensi' => 'Tes Potensi',
        'diterima' => 'Diterima',
        'ditolak' => 'Ditolak',
    ] as $val => $lbl)
                                            <option value="{{ $val }}"
                                                {{ $app->status === $val ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            @elseif($app->is_converted)
                                <span class="text-xs text-gray-400 italic">Selesai</span>
                            @else
                                <span class="text-xs text-red-400">Ditolak</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Detail --}}
                                <button wire:click="openDetail({{ $app->id }})" onclick="event.stopPropagation()"
                                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                                {{-- Convert --}}
                                @if ($app->status === 'diterima' && !$app->is_converted)
                                    <button wire:click="openConvert({{ $app->id }})"
                                        onclick="event.stopPropagation()"
                                        class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div
                                class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-400">Belum ada pelamar</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($applicants->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $applicants->links() }}
            </div>
        @endif
    </div>

    {{-- ── MODAL: Detail Pelamar ── --}}
    @if ($showDetailModal && $viewing)
        <div class="modal-backdrop" wire:click="$set('showDetailModal',false)">
            <div class="modal-box max-w-2xl max-h-[90vh] overflow-y-auto" wire:click.stop>
                <div class="modal-header sticky top-0 z-10">
                    <h3>Detail Pelamar</h3>
                    <button wire:click="$set('showDetailModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Biodata --}}
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Biodata</p>
                        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                            <div><span class="text-gray-400 text-xs block">Nama</span><span
                                    class="font-medium">{{ $viewing->name }}</span></div>
                            <div><span
                                    class="text-gray-400 text-xs block">Email</span><span>{{ $viewing->email }}</span>
                            </div>
                            <div><span class="text-gray-400 text-xs block">No.
                                    HP</span><span>{{ $viewing->phone ?? '—' }}</span></div>
                            <div><span class="text-gray-400 text-xs block">Jenis
                                    Kelamin</span><span>{{ $viewing->gender_label }}</span></div>
                            <div><span class="text-gray-400 text-xs block">Tempat, Tgl Lahir</span>
                                <span>{{ $viewing->place_of_birth ?? '—' }},
                                    {{ $viewing->date_of_birth?->format('d M Y') ?? '—' }}</span>
                            </div>
                            <div><span class="text-gray-400 text-xs block">Pendidikan Terakhir</span>
                                <span>{{ $viewing->last_education_label }}
                                    {{ $viewing->last_education_major ? '— ' . $viewing->last_education_major : '' }}</span>
                            </div>
                        </div>
                        @if ($viewing->address)
                            <div class="mt-2"><span class="text-gray-400 text-xs block">Alamat</span><span
                                    class="text-sm">{{ $viewing->address }}</span></div>
                        @endif
                    </div>

                    {{-- Riwayat Pendidikan --}}
                    @if ($viewing->educations->count())
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Riwayat
                                Pendidikan</p>
                            <div class="space-y-2">
                                @foreach ($viewing->educations as $edu)
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                        <span class="badge-code shrink-0 mt-0.5">{{ $edu->level_label }}</span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $edu->institution }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $edu->major ?? 'Umum' }}
                                                · {{ $edu->start_year }}–{{ $edu->end_year ?? 'sekarang' }}
                                                @if ($edu->gpa)
                                                    · IPK {{ $edu->gpa }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Pengalaman Kerja --}}
                    @if ($viewing->experiences->count())
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Pengalaman
                                Kerja</p>
                            <div class="space-y-2">
                                @foreach ($viewing->experiences as $exp)
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <p class="text-sm font-medium text-gray-800">{{ $exp->position ?? '—' }} —
                                            {{ $exp->company_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $exp->duration }}</p>
                                        @if ($exp->description)
                                            <p class="text-xs text-gray-500 mt-1">{{ $exp->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- CV --}}
                    @if ($viewing->cv_file)
                        <div class="border-t border-gray-100 pt-4">
                            <a href="{{ Storage::url($viewing->cv_file) }}" target="_blank"
                                class="inline-flex items-center gap-2 text-sm text-violet-600 hover:underline">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                Unduh CV / Resume
                            </a>
                        </div>
                    @endif

                    {{-- Catatan HR --}}
                    <div class="border-t border-gray-100 pt-4">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">
                            Catatan HR (Internal)
                        </label>
                        <textarea wire:model="hrNotes" rows="3" class="input resize-none"
                            placeholder="Catatan hasil screening, wawancara, dll..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showDetailModal',false)" class="btn-ghost">Tutup</button>
                    <button wire:click="saveNotes" class="btn-primary">Simpan Catatan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── MODAL: Konversi ke Pegawai ── --}}
    @if ($showConvertModal)
        <div class="modal-backdrop" wire:click="$set('showConvertModal',false)">
            <div class="modal-box max-w-md" wire:click.stop>
                <div class="modal-header" style="background:linear-gradient(to right,#16a34a,#15803d)">
                    <h3>Jadikan Pegawai</h3>
                    <button wire:click="$set('showConvertModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-xs text-green-700 mb-2">
                        Data pelamar akan otomatis dipindahkan ke sistem kepegawaian.
                        NIK sementara diberikan — NIPY resmi diterbitkan setelah lulus masa percobaan.
                    </div>

                    {{-- Sekolah: hanya untuk walk-in karena tidak ada jobVacancy --}}
                    @if ($convertIsWalkIn)
                        <div>
                            <label class="form-label">Unit / Sekolah <span class="text-red-500">*</span></label>
                            <select wire:model="convertSchoolId"
                                class="input @error('convertSchoolId') input-error @enderror">
                                <option value="">-- Pilih Sekolah/Unit --</option>
                                @foreach ($schools as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('convertSchoolId')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-amber-600 mt-1">
                                Jabatan & departemen bisa dilengkapi lewat halaman detail pegawai setelah ini.
                            </p>
                        </div>
                    @endif

                    {{-- Toggle Guru --}}
                    <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <input wire:model.live="convertIsGuru" type="checkbox" id="is_guru"
                            class="mt-0.5 rounded border-gray-300 text-violet-600 w-4 h-4">
                        <label for="is_guru" class="text-sm text-gray-700 cursor-pointer">
                            Pegawai ini adalah <strong>Guru</strong>
                            <span class="block text-xs text-gray-400 font-normal mt-0.5">
                                Guru = masa percobaan 6 bulan · Non-guru = 3 bulan
                            </span>
                        </label>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-lg text-xs text-gray-600 text-center">
                        Masa percobaan:
                        <strong
                            class="text-gray-800">{{ $convertIsGuru ? '6 bulan (Guru)' : '3 bulan (Non-Guru)' }}</strong>
                    </div>

                    <div>
                        <label class="form-label">NIK Sementara <span class="text-red-500">*</span></label>
                        <input wire:model="convertNik" type="text"
                            class="input @error('convertNik') input-error @enderror" placeholder="Auto-generated">
                        @error('convertNik')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Tanggal Masuk <span class="text-red-500">*</span></label>
                            <input wire:model="convertJoinDate" type="date"
                                class="input @error('convertJoinDate') input-error @enderror">
                            @error('convertJoinDate')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Tipe Pegawai</label>
                            <select wire:model="convertType" class="input">
                                <option value="permanent">Tetap</option>
                                <option value="contract">Kontrak</option>
                                <option value="intern">Magang</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Catatan</label>
                        <textarea wire:model="convertNote" rows="2" class="input resize-none"
                            placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showConvertModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="convertToEmployee" wire:loading.attr="disabled" class="btn text-white"
                        style="background:#16a34a" onmouseover="this.style.background='#15803d'"
                        onmouseout="this.style.background='#16a34a'">
                        <span wire:loading.remove wire:target="convertToEmployee">Konfirmasi & Jadikan Pegawai</span>
                        <span wire:loading wire:target="convertToEmployee">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── MODAL: Input Pelamar Walk-in ── --}}
    @if ($showWalkInModal)
        <div class="modal-backdrop" wire:click="$set('showWalkInModal',false)">
            <div class="modal-box max-w-lg" wire:click.stop>
                <div class="modal-header">
                    <h3>Tambah Pelamar Walk-in</h3>
                    <button wire:click="$set('showWalkInModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        Untuk pelamar yang datang langsung / tidak melalui portal lowongan publik.
                    </p>

                    {{-- Nama --}}
                    <div>
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input wire:model="wi_name" type="text"
                            class="input @error('wi_name') input-error @enderror" placeholder="Nama lengkap pelamar">
                        @error('wi_name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email + HP --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Email <span class="text-red-500">*</span></label>
                            <input wire:model="wi_email" type="email"
                                class="input @error('wi_email') input-error @enderror" placeholder="email@contoh.com">
                            @error('wi_email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">No. HP</label>
                            <input wire:model="wi_phone" type="text" class="input" placeholder="08xx...">
                        </div>
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label class="form-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select wire:model="wi_gender" class="input">
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>

                    {{-- Posisi yang dilamar --}}
                    <div>
                        <label class="form-label">Posisi yang Dilamar <span class="text-red-500">*</span></label>
                        <input wire:model="wi_applied_position" type="text"
                            class="input @error('wi_applied_position') input-error @enderror"
                            placeholder="contoh: Guru Matematika, Staf TU">
                        @error('wi_applied_position')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pendidikan --}}
                    <div>
                        <label class="form-label">Pendidikan Terakhir</label>
                        <select wire:model="wi_last_education" class="input">
                            <option value="">-- Pilih --</option>
                            <option value="sd">SD</option>
                            <option value="smp">SMP</option>
                            <option value="sma">SMA/SMK</option>
                            <option value="d3">D3</option>
                            <option value="s1">S1</option>
                            <option value="s2">S2</option>
                            <option value="s3">S3</option>
                        </select>
                    </div>

                    {{-- CV --}}
                    <div>
                        <label class="form-label">CV / Dokumen (opsional)</label>
                        <input wire:model="wi_cv_file" type="file" accept=".pdf,.jpg,.jpeg,.png"
                            class="input text-sm">
                        <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG -- maks 5MB</p>
                        @error('wi_cv_file')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan HR --}}
                    <div>
                        <label class="form-label">Catatan HR (opsional)</label>
                        <textarea wire:model="wi_hr_notes" rows="2" class="input resize-none"
                            placeholder="Catatan awal, referensi, dsb..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showWalkInModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="saveWalkIn" wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-wait" class="btn-primary">
                        <span wire:loading.remove wire:target="saveWalkIn">Simpan Pelamar</span>
                        <span wire:loading wire:target="saveWalkIn">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
