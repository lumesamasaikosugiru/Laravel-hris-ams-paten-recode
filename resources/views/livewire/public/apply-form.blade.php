<div class="max-w-3xl mx-auto px-4 py-10">

    @if ($submitted)
        {{-- ── Success State ── --}}
        <div class="card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Lamaran Terkirim!</h2>
            <p class="text-gray-500 text-sm">
                Terima kasih telah melamar posisi <strong>{{ $job->title }}</strong>.
            </p>
            <p class="text-gray-400 text-xs mt-1">
                Tim HR kami akan menghubungi kamu melalui email atau telepon jika lolos seleksi administrasi.
            </p>
            <a href="{{ route('careers.index') }}" class="btn-primary inline-flex mt-6">
                Lihat Lowongan Lainnya
            </a>
        </div>
    @else
        {{-- ── Info Lowongan ── --}}
        <div class="card p-4 mb-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-sidebar flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <div>
                <h1 class="text-base font-bold text-gray-800">{{ $job->title }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $job->school->name }} · {{ $job->employment_type_label }}
                    @if ($job->close_date)
                        · Deadline {{ $job->close_date->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>

        {{-- ── Tab Navigation ── --}}
        <div class="card overflow-hidden mb-1">
            <div class="flex border-b border-gray-200 overflow-x-auto">
                @foreach ([['biodata', '1', 'Data Diri'], ['education', '2', 'Pendidikan'], ['experience', '3', 'Pengalaman'], ['document', '4', 'Dokumen']] as [$tab, $num, $label])
                    <button wire:click="$set('activeTab','{{ $tab }}')"
                        class="flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 transition whitespace-nowrap shrink-0
                           {{ $activeTab === $tab
                               ? 'border-violet-600 text-violet-700 bg-violet-50/50'
                               : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
                        <span
                            class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold
                             {{ $activeTab === $tab ? 'bg-violet-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                            {{ $num }}
                        </span>
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- ══ TAB: Biodata ══ --}}
            @if ($activeTab === 'biodata')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="input @error('name') input-error @enderror"
                            placeholder="Sesuai KTP">
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Email <span class="text-red-500">*</span></label>
                            <input wire:model="email" type="email" class="input @error('email') input-error @enderror"
                                placeholder="email@contoh.com">
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">No. HP / WhatsApp</label>
                            <input wire:model="phone" type="text" class="input" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="form-label">Jenis Kelamin</label>
                            <select wire:model="gender" class="input">
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Tempat Lahir</label>
                            <input wire:model="place_of_birth" type="text" class="input"
                                placeholder="Kota kelahiran">
                        </div>
                        <div>
                            <label class="form-label">Tanggal Lahir</label>
                            <input wire:model="date_of_birth" type="date" class="input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Alamat Domisili</label>
                        <textarea wire:model="address" rows="2" class="input resize-none" placeholder="Alamat lengkap saat ini"></textarea>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Pendidikan Terakhir
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Jenjang <span class="text-red-500">*</span></label>
                                <select wire:model="last_education" class="input">
                                    <option value="sma">SMA/SMK</option>
                                    <option value="d3">D3</option>
                                    <option value="s1">S1</option>
                                    <option value="s2">S2</option>
                                    <option value="s3">S3</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Jurusan</label>
                                <input wire:model="last_education_major" type="text" class="input"
                                    placeholder="Program studi">
                            </div>
                            <div>
                                <label class="form-label">Institusi</label>
                                <input wire:model="last_education_institution" type="text" class="input"
                                    placeholder="Nama universitas/sekolah">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══ TAB: Pendidikan ══ --}}
            @if ($activeTab === 'education')
                <div class="p-6 space-y-4">
                    <p class="text-xs text-gray-400">Isi riwayat pendidikan mulai dari yang terakhir.</p>

                    @foreach ($educations as $i => $edu)
                        <div class="border border-gray-200 rounded-xl p-4 relative">
                            @if (count($educations) > 1)
                                <button wire:click="removeEducation({{ $i }})"
                                    class="absolute top-3 right-3 text-red-400 hover:text-red-600 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif

                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="form-label">Jenjang <span class="text-red-500">*</span></label>
                                    <select wire:model="educations.{{ $i }}.level" class="input">
                                        <option value="sd">SD</option>
                                        <option value="smp">SMP</option>
                                        <option value="sma">SMA/SMK</option>
                                        <option value="d3">D3</option>
                                        <option value="s1">S1</option>
                                        <option value="s2">S2</option>
                                        <option value="s3">S3</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">IPK / Nilai Akhir</label>
                                    <input wire:model="educations.{{ $i }}.gpa" type="number"
                                        step="0.01" min="0" max="4" class="input"
                                        placeholder="3.50">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Institusi <span class="text-red-500">*</span></label>
                                <input wire:model="educations.{{ $i }}.institution" type="text"
                                    class="input @error('educations.' . $i . '.institution') input-error @enderror"
                                    placeholder="Universitas / Sekolah">
                                @error('educations.' . $i . '.institution')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-1">
                                    <label class="form-label">Jurusan</label>
                                    <input wire:model="educations.{{ $i }}.major" type="text"
                                        class="input" placeholder="Program studi">
                                </div>
                                <div>
                                    <label class="form-label">Tahun Masuk <span class="text-red-500">*</span></label>
                                    <input wire:model="educations.{{ $i }}.start_year" type="number"
                                        placeholder="{{ now()->year - 4 }}"
                                        class="input @error('educations.' . $i . '.start_year') input-error @enderror">
                                    @error('educations.' . $i . '.start_year')
                                        <p class="form-error">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="form-label">Tahun Lulus</label>
                                    <input wire:model="educations.{{ $i }}.end_year" type="number"
                                        placeholder="{{ now()->year }}" class="input">
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <button wire:click="addEducation"
                        class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-sm text-gray-500
                           hover:border-violet-400 hover:text-violet-600 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Riwayat Pendidikan
                    </button>
                </div>
            @endif

            {{-- ══ TAB: Pengalaman ══ --}}
            @if ($activeTab === 'experience')
                <div class="p-6 space-y-4">
                    <p class="text-xs text-gray-400">Isi pengalaman kerja jika ada. Boleh dikosongkan.</p>

                    @foreach ($experiences as $i => $exp)
                        <div class="border border-gray-200 rounded-xl p-4 relative">
                            @if (count($experiences) > 1)
                                <button wire:click="removeExperience({{ $i }})"
                                    class="absolute top-3 right-3 text-red-400 hover:text-red-600 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif

                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="form-label">Nama Perusahaan</label>
                                    <input wire:model="experiences.{{ $i }}.company_name" type="text"
                                        class="input" placeholder="PT. Contoh">
                                </div>
                                <div>
                                    <label class="form-label">Jabatan</label>
                                    <input wire:model="experiences.{{ $i }}.position" type="text"
                                        class="input" placeholder="Staff / Guru / dll">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="form-label">Mulai Bekerja</label>
                                    <input wire:model="experiences.{{ $i }}.start_date" type="date"
                                        class="input">
                                </div>
                                <div>
                                    <label class="form-label">Selesai Bekerja</label>
                                    <input wire:model="experiences.{{ $i }}.end_date" type="date"
                                        class="input" {{ $experiences[$i]['is_current'] ? 'disabled' : '' }}>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mb-3">
                                <input wire:model.live="experiences.{{ $i }}.is_current" type="checkbox"
                                    id="current_{{ $i }}"
                                    class="rounded border-gray-300 text-violet-600 w-4 h-4">
                                <label for="current_{{ $i }}"
                                    class="text-sm text-gray-600 cursor-pointer">
                                    Masih bekerja di sini
                                </label>
                            </div>

                            <div>
                                <label class="form-label">Deskripsi Pekerjaan</label>
                                <textarea wire:model="experiences.{{ $i }}.description" rows="2" class="input resize-none"
                                    placeholder="Tugas dan tanggung jawab..."></textarea>
                            </div>
                        </div>
                    @endforeach

                    <button wire:click="addExperience"
                        class="w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-sm text-gray-500
                           hover:border-violet-400 hover:text-violet-600 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Pengalaman Kerja
                    </button>
                </div>
            @endif

            {{-- ══ TAB: Dokumen ══ --}}
            @if ($activeTab === 'document')
                <div class="p-6 space-y-4">
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-violet-400 transition">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <label class="form-label">Upload CV / Resume <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-400 mb-3">Format: PDF, DOC, DOCX · Maks. 5MB · <span
                                class="text-red-500">Wajib</span></p>
                        <input wire:model="cv_file" type="file" accept=".pdf,.doc,.docx"
                            class="text-sm text-gray-500
                              file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0
                              file:text-xs file:font-medium
                              file:bg-violet-50 file:text-violet-700
                              hover:file:bg-violet-100 file:transition">
                        @error('cv_file')
                            <p class="form-error mt-2">{{ $message }}</p>
                        @enderror
                        <div wire:loading wire:target="cv_file" class="text-xs text-violet-600 mt-2">
                            Mengupload...
                        </div>
                        @if ($cv_file)
                            <p class="text-xs text-green-600 mt-2 font-medium">
                                ✓ {{ $cv_file->getClientOriginalName() }}
                            </p>
                        @endif
                    </div>

                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-700 space-y-1">
                        <p class="font-semibold">Sebelum mengirim, pastikan:</p>
                        <p>· Semua data di tab sebelumnya sudah terisi dengan benar</p>
                        <p>· Email dan nomor HP yang kamu isi aktif dan dapat dihubungi</p>
                        <p>· File CV sudah terupload (jika ada)</p>
                    </div>
                </div>
            @endif

            {{-- Error summary -- tampil jika ada error validasi --}}
            @if ($errors->any())
                <div class="mx-6 mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-xs font-semibold text-red-600 mb-1">
                        Mohon perbaiki kesalahan berikut:
                    </p>
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-red-500">· {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- ── Footer Tab ── --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                <button wire:click="prevTab" class="btn-ghost {{ $activeTab === 'biodata' ? 'invisible' : '' }}">
                    ← Sebelumnya
                </button>

                @if ($activeTab !== 'document')
                    <button wire:click="nextTab" class="btn-primary">
                        Selanjutnya →
                    </button>
                @else
                    <button wire:click="submit" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="submit">Kirim Lamaran</span>
                        <span wire:loading wire:target="submit">Mengirim...</span>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
