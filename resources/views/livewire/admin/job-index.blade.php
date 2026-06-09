<div>
    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search"
                   type="text" placeholder="Cari lowongan..."
                   class="input w-60">
            <select wire:model.live="schoolFilter" class="input w-auto">
                <option value="">Semua Unit</option>
                @foreach($schools as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="input w-auto">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="open">Dibuka</option>
                <option value="closed">Ditutup</option>
            </select>
        </div>
        <button wire:click="openCreate" class="btn-primary">
            <x-icons.plus /> Buat Lowongan
        </button>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Lowongan</th>
                    <th class="hidden md:table-cell">Unit</th>
                    <th class="text-center hidden lg:table-cell">Tipe</th>
                    <th class="text-center">Pelamar</th>
                    <th class="text-center hidden lg:table-cell">Deadline</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                <tr>
                    <td class="text-gray-400 text-xs">{{ $jobs->firstItem() + $loop->index }}</td>
                    <td>
                        <p class="font-medium text-gray-800">{{ $job->title }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $job->position->name }} — {{ $job->department->name }}
                        </p>
                    </td>
                    <td class="text-sm text-gray-500 hidden md:table-cell">
                        {{ $job->school->name }}
                    </td>
                    <td class="text-center hidden lg:table-cell">
                        <span class="badge-purple">{{ $job->employment_type_label }}</span>
                    </td>
                    <td class="text-center">
                        <span class="font-semibold text-gray-700">{{ $job->applicants_count }}</span>
                        <span class="text-xs text-gray-400 ml-0.5">pelamar</span>
                    </td>
                    <td class="text-center text-xs hidden lg:table-cell">
                        @if($job->close_date)
                            <span class="{{ $job->is_expired ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                                {{ $job->close_date->format('d M Y') }}
                                @if($job->is_expired)
                                    <span class="block text-red-400">(Lewat)</span>
                                @endif
                            </span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <select wire:change="changeStatus({{ $job->id }}, $event.target.value)"
                                class="text-xs rounded-lg border border-gray-200 py-1 px-2 focus:outline-none focus:ring-1 focus:ring-violet-400 cursor-pointer
                                {{ $job->status === 'open'   ? 'bg-green-50 text-green-700'  :
                                   ($job->status === 'draft' ? 'bg-gray-50 text-gray-600'    :
                                                               'bg-red-50 text-red-600') }}">
                            <option value="draft"  {{ $job->status==='draft'  ? 'selected':'' }}>Draft</option>
                            <option value="open"   {{ $job->status==='open'   ? 'selected':'' }}>Dibuka</option>
                            <option value="closed" {{ $job->status==='closed' ? 'selected':'' }}>Ditutup</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Link ke halaman publik --}}
                            @if($job->status === 'open')
                            <a href="{{ route('careers.show', $job) }}" target="_blank"
                               class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition"
                               title="Lihat halaman publik">
                                <x-icons.eye />
                            </a>
                            @endif
                            <button wire:click="openEdit({{ $job->id }})"
                                    class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition">
                                <x-icons.pencil />
                            </button>
                            <button wire:click="confirmDelete({{ $job->id }})"
                                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition">
                                <x-icons.trash />
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-16 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-400">Belum ada lowongan</p>
                        <p class="text-xs text-gray-300 mt-1">Buat lowongan untuk mulai menerima pelamar</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($jobs->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form --}}
    @if($showModal)
    <div class="modal-backdrop" wire:click="$set('showModal',false)">
        <div class="modal-box max-w-2xl max-h-[90vh] overflow-y-auto" wire:click.stop>
            <div class="modal-header sticky top-0 z-10">
                <h3>{{ $editingId ? 'Edit Lowongan' : 'Buat Lowongan Baru' }}</h3>
                <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white">
                    <x-icons.x-mark />
                </button>
            </div>
            <div class="modal-body">
                {{-- Judul --}}
                <div>
                    <label class="form-label">Judul Lowongan <span class="text-red-500">*</span></label>
                    <input wire:model="title" type="text"
                           class="input @error('title') input-error @enderror"
                           placeholder="Contoh: Guru Matematika SMK Fatahillah">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Unit --}}
                <div>
                    <label class="form-label">Unit / Sekolah <span class="text-red-500">*</span></label>
                    <select wire:model.live="school_id"
                            class="input @error('school_id') input-error @enderror">
                        <option value="">-- Pilih Unit --</option>
                        @foreach($schools as $sc)
                            <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                        @endforeach
                    </select>
                    @error('school_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Dept + Jabatan --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Departemen <span class="text-red-500">*</span></label>
                        <select wire:model.live="department_id"
                                class="input @error('department_id') input-error @enderror"
                                {{ empty($school_id) ? 'disabled' : '' }}>
                            <option value="">{{ empty($school_id) ? '-- Pilih unit dulu --' : '-- Pilih Departemen --' }}</option>
                            @foreach($modalDepts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Jabatan <span class="text-red-500">*</span></label>
                        <select wire:model="position_id"
                                class="input @error('position_id') input-error @enderror"
                                {{ empty($department_id) ? 'disabled' : '' }}>
                            <option value="">{{ empty($department_id) ? '-- Pilih dept dulu --' : '-- Pilih Jabatan --' }}</option>
                            @foreach($modalPositions as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('position_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Tipe + Kuota + Status --}}
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Tipe Kontrak</label>
                        <select wire:model="employment_type" class="input">
                            <option value="permanent">Tetap</option>
                            <option value="contract">Kontrak</option>
                            <option value="intern">Magang</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Kuota</label>
                        <input wire:model="quota" type="number" min="1" class="input">
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select wire:model="status" class="input">
                            <option value="draft">Draft</option>
                            <option value="open">Buka Sekarang</option>
                            <option value="closed">Tutup</option>
                        </select>
                    </div>
                </div>

                {{-- Tanggal --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tanggal Buka <span class="text-red-500">*</span></label>
                        <input wire:model="open_date" type="date"
                               class="input @error('open_date') input-error @enderror">
                        @error('open_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Tanggal Tutup</label>
                        <input wire:model="close_date" type="date"
                               class="input @error('close_date') input-error @enderror">
                        @error('close_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="form-label">Deskripsi Pekerjaan</label>
                    <textarea wire:model="description" rows="3"
                              class="input resize-none"
                              placeholder="Tugas dan tanggung jawab posisi ini..."></textarea>
                </div>

                {{-- Persyaratan --}}
                <div>
                    <label class="form-label">Persyaratan</label>
                    <textarea wire:model="requirements" rows="3"
                              class="input resize-none"
                              placeholder="Kualifikasi yang dibutuhkan..."></textarea>
                </div>
            </div>
            <div class="modal-footer sticky bottom-0">
                <button wire:click="$set('showModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Hapus --}}
    @if($showDeleteModal)
    <div class="modal-backdrop" wire:click="$set('showDeleteModal',false)">
        <div class="modal-box max-w-sm" wire:click.stop>
            <div class="modal-header" style="background:linear-gradient(to right,#ef4444,#b91c1c)">
                <h3>Hapus Lowongan?</h3>
                <button wire:click="$set('showDeleteModal',false)" class="text-white/70 hover:text-white">
                    <x-icons.x-mark />
                </button>
            </div>
            <div class="modal-body text-center">
                <x-icons.warning class="w-10 h-10 text-red-400 mx-auto mb-2" />
                <p class="text-sm text-gray-600">
                    Semua data pelamar yang terhubung dengan lowongan ini juga akan ikut terhapus.
                </p>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showDeleteModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="delete" class="btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>
    @endif
</div>
