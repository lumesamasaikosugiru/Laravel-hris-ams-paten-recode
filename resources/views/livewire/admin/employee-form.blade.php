<div>
    {{-- Tab Navigation --}}
    <div class="card overflow-hidden mb-5">
        <div class="flex border-b border-gray-200 overflow-x-auto">
            @foreach ([['identity', '1', 'Identitas'], ['contact', '2', 'Kontak'], ['employment', '3', 'Kepegawaian'], ['education', '4', 'Pendidikan'], ['assignment', '5', 'Jabatan']] as [$tab, $num, $label])
                <button wire:click="$set('activeTab','{{ $tab }}')"
                    class="flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 transition whitespace-nowrap shrink-0
                           {{ $activeTab === $tab
                               ? 'border-violet-600 text-violet-700 bg-violet-50/50'
                               : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                    <span
                        class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                             {{ $activeTab === $tab ? 'bg-violet-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                        {{ $num }}
                    </span>
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- ══ TAB: Identitas ══ --}}
        @if ($activeTab === 'identity')
            <div class="p-6 space-y-4">
                {{-- Foto --}}
                <div class="flex items-center gap-5">
                    <div
                        class="w-20 h-20 rounded-full bg-gray-100 border-2 border-gray-200 overflow-hidden shrink-0 flex items-center justify-center">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover" alt="">
                        @elseif($employee?->photo)
                            <img src="{{ Storage::url($employee->photo) }}" class="w-full h-full object-cover"
                                alt="">
                        @else
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <label class="form-label">Foto Pegawai</label>
                        <input wire:model="photo" type="file" accept="image/*"
                            class="text-xs text-gray-500
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-xs file:font-medium
                                  file:bg-violet-50 file:text-violet-700
                                  hover:file:bg-violet-100 file:transition">
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG — maks. 2MB</p>
                        @error('photo')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">NIK Karyawan <span class="text-red-500">*</span></label>
                        <input wire:model="nik" type="text" class="input @error('nik') input-error @enderror"
                            placeholder="Nomor Induk Karyawan">
                        @error('nik')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">NIK KTP</label>
                        <input wire:model="national_id" type="text" class="input" placeholder="16 digit NIK KTP"
                            maxlength="16">
                    </div>
                </div>

                <div>
                    <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="input @error('name') input-error @enderror"
                        placeholder="Nama sesuai KTP">
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select wire:model="gender" class="input">
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Agama</label>
                        <select wire:model="religion" class="input">
                            <option value="islam">Islam</option>
                            <option value="kristen">Kristen</option>
                            <option value="katolik">Katolik</option>
                            <option value="hindu">Hindu</option>
                            <option value="buddha">Buddha</option>
                            <option value="konghucu">Konghucu</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status Pernikahan</label>
                        <select wire:model="marital_status" class="input">
                            <option value="single">Belum Menikah</option>
                            <option value="married">Menikah</option>
                            <option value="divorced">Cerai Hidup</option>
                            <option value="widowed">Cerai Mati</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tempat Lahir</label>
                        <input wire:model="place_of_birth" type="text" class="input" placeholder="Kota kelahiran">
                    </div>
                    <div>
                        <label class="form-label">Tanggal Lahir</label>
                        <input wire:model="date_of_birth" type="date" class="input">
                    </div>
                </div>
            </div>
        @endif

        {{-- ══ TAB: Kontak ══ --}}
        @if ($activeTab === 'contact')
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Email</label>
                        <input wire:model="email" type="email" class="input @error('email') input-error @enderror"
                            placeholder="email@contoh.com">
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">No. Telepon</label>
                        <input wire:model="phone" type="text" class="input" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div>
                    <label class="form-label">Alamat Lengkap</label>
                    <textarea wire:model="address" rows="3" class="input resize-none" placeholder="Alamat domisili saat ini"></textarea>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Kontak Darurat</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Nama</label>
                            <input wire:model="emergency_contact_name" type="text" class="input"
                                placeholder="Nama kontak">
                        </div>
                        <div>
                            <label class="form-label">No. Telepon</label>
                            <input wire:model="emergency_contact_phone" type="text" class="input"
                                placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="form-label">Hubungan</label>
                            <input wire:model="emergency_contact_relation" type="text" class="input"
                                placeholder="Istri, Orang Tua...">
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══ TAB: Kepegawaian ══ --}}
        @if ($activeTab === 'employment')
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label">Unit / Sekolah <span class="text-red-500">*</span></label>
                    <select wire:model.live="school_id" class="input @error('school_id') input-error @enderror">
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($schools as $sc)
                            <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                        @endforeach
                    </select>
                    @error('school_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <input wire:model="is_guru" type="checkbox" id="is_guru"
                        class="mt-0.5 rounded border-gray-300 text-violet-600 w-4 h-4">
                    <label for="is_guru" class="text-sm text-gray-700 cursor-pointer">
                        Pegawai ini adalah <strong>Guru</strong>
                        <span class="block text-xs text-gray-400 font-normal mt-0.5">
                            Berpengaruh pada kode NIPY dan durasi masa percobaan
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tanggal Masuk <span class="text-red-500">*</span></label>
                        <input wire:model="join_date" type="date"
                            class="input @error('join_date') input-error @enderror">
                        @error('join_date')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Tipe Pegawai</label>
                        <select wire:model="employee_type" class="input">
                            <option value="permanent">Tetap</option>
                            <option value="contract">Kontrak</option>
                            <option value="intern">Magang</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Mulai Kontrak</label>
                        <input wire:model="contract_start" type="date" class="input">
                    </div>
                    <div>
                        <label class="form-label">Selesai Kontrak</label>
                        <input wire:model="contract_end" type="date"
                            class="input @error('contract_end') input-error @enderror">
                        @error('contract_end')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($isEdit)
                    <div>
                        <label class="form-label">Status Kepegawaian</label>
                        <select wire:model="status" class="input">
                            <option value="probation">Masa Percobaan</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                            <option value="resigned">Mengundurkan Diri</option>
                            <option value="terminated">Diberhentikan</option>
                        </select>
                    </div>
                @endif
            </div>
        @endif

        {{-- ══ TAB: Pendidikan ══ --}}
        @if ($activeTab === 'education')
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label">Pendidikan Terakhir</label>
                    <select wire:model="last_education" class="input">
                        <option value="sd">SD</option>
                        <option value="smp">SMP</option>
                        <option value="sma">SMA / SMK</option>
                        <option value="d3">D3</option>
                        <option value="s1">S1</option>
                        <option value="s2">S2</option>
                        <option value="s3">S3</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Jurusan / Program Studi</label>
                    <input wire:model="last_education_major" type="text" class="input"
                        placeholder="Contoh: Manajemen Pendidikan">
                </div>
                <div>
                    <label class="form-label">Nama Institusi</label>
                    <input wire:model="last_education_institution" type="text" class="input"
                        placeholder="Nama universitas / sekolah">
                </div>
            </div>
        @endif

        {{-- ══ TAB: Jabatan ══ --}}
        @if ($activeTab === 'assignment')
            @if ($isEdit)
                <div class="p-6 text-center py-10">
                    <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-500">Perubahan jabatan dikelola di halaman detail pegawai
                    </p>
                    <a href="{{ route('admin.employees.show', $employee) }}"
                        class="inline-flex items-center gap-1 text-sm text-violet-600 hover:underline mt-3">
                        Buka Halaman Detail
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="p-6 space-y-4">
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">
                        Jabatan awal saat pertama kali ditambahkan. Mutasi dan promosi dikelola di halaman detail
                        pegawai.
                    </div>
                    <div>
                        <label class="form-label">Departemen <span class="text-red-500">*</span></label>
                        <select wire:model.live="department_id"
                            class="input @error('department_id') input-error @enderror"
                            {{ empty($school_id) ? 'disabled' : '' }}>
                            <option value="">
                                {{ empty($school_id) ? '-- Pilih unit di tab Kepegawaian --' : '-- Pilih Departemen --' }}
                            </option>
                            @foreach ($modalDepts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Jabatan <span class="text-red-500">*</span></label>
                        <select wire:model="position_id" class="input @error('position_id') input-error @enderror"
                            {{ empty($department_id) ? 'disabled' : '' }}>
                            <option value="">
                                {{ empty($department_id) ? '-- Pilih departemen dulu --' : '-- Pilih Jabatan --' }}
                            </option>
                            @foreach ($modalPositions as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('position_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Tanggal Mulai Jabatan <span class="text-red-500">*</span></label>
                        <input wire:model="assignment_start" type="date"
                            class="input @error('assignment_start') input-error @enderror">
                        @error('assignment_start')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="form-label">Catatan</label>
                        <textarea wire:model="assignment_notes" rows="2" class="input resize-none"
                            placeholder="Keterangan penugasan (opsional)"></textarea>
                    </div>
                </div>
            @endif
        @endif

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
            <a href="{{ route('admin.employees.index') }}" class="btn-ghost">
                ← Kembali
            </a>
            <div class="flex items-center gap-2">
                {{-- Navigasi antar tab --}}
                @if ($activeTab !== 'identity')
                    <button wire:click="prevTab" class="btn-ghost">← Sebelumnya</button>
                @endif

                @if ($activeTab !== 'assignment')
                    <button wire:click="nextTab" class="btn-primary">Selanjutnya →</button>
                @else
                    {{-- Tombol simpan HANYA muncul di tab terakhir --}}
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="save">
                            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Pegawai' }}
                        </span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
