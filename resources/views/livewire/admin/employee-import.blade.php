<div>
    @if ($done)
        {{-- ── Result ── --}}
        <div class="card p-6 text-center">
            <div
                class="w-14 h-14 rounded-full {{ $errorCount === 0 ? 'bg-green-100' : 'bg-yellow-100' }} flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 {{ $errorCount === 0 ? 'text-green-600' : 'text-yellow-600' }}" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-gray-800 mb-1">Import Selesai</h2>
            <p class="text-sm text-gray-500">
                <span class="text-green-600 font-semibold">{{ $successCount }} berhasil</span>
                @if ($errorCount > 0)
                    · <span class="text-red-500 font-semibold">{{ $errorCount }} gagal</span>
                @endif
            </p>

            @if (count($errors) > 0)
                <div class="mt-4 text-left bg-red-50 border border-red-200 rounded-lg p-4 max-h-48 overflow-y-auto">
                    <p class="text-xs font-semibold text-red-600 mb-2">Detail Error:</p>
                    @foreach ($errors as $err)
                        <p class="text-xs text-red-600">· {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <div class="flex gap-3 justify-center mt-5">
                <button wire:click="reset_form" class="btn-ghost">Import Lagi</button>
                <a href="{{ route('admin.employees.index') }}" class="btn-primary">Lihat Data Pegawai</a>
            </div>
        </div>
    @elseif($previewing)
        {{-- ── Preview ── --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">Preview Data ({{ count($rows) }} baris)</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span class="text-green-600 font-medium">{{ collect($rows)->where('valid', true)->count() }}
                            valid</span>
                        @php $invalidCount = collect($rows)->where('valid',false)->count(); @endphp
                        @if ($invalidCount > 0)
                            · <span class="text-red-500 font-medium">{{ $invalidCount }} error</span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="reset_form" class="btn-ghost text-sm">Batal</button>
                    <button wire:click="import" wire:loading.attr="disabled" class="btn-primary text-sm"
                        @if (collect($rows)->where('valid', true)->count() === 0) disabled @endif>
                        <span wire:loading.remove wire:target="import">
                            Import {{ collect($rows)->where('valid', true)->count() }} Data
                        </span>
                        <span wire:loading wire:target="import">Mengimport...</span>
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="tbl text-xs">
                    <thead class="sticky top-0">
                        <tr>
                            <th class="w-8">Baris</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Unit</th>
                            <th>Jabatan</th>
                            <th>Tipe</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr class="{{ !$row['valid'] ? 'bg-red-50' : '' }}">
                                <td class="text-gray-400">{{ $row['row'] }}</td>
                                <td class="font-mono">{{ $row['nik'] }}</td>
                                <td class="font-medium">{{ $row['name'] }}</td>
                                <td>{{ $row['school_name'] }}</td>
                                <td>{{ $row['pos_name'] }}</td>
                                <td>{{ $row['employee_type'] }}</td>
                                <td class="text-center">
                                    @if ($row['valid'])
                                        <span class="badge-green">Valid</span>
                                    @else
                                        <div>
                                            <span class="badge-red">Error</span>
                                            <p class="text-red-500 text-xs mt-0.5">{{ implode(', ', $row['errors']) }}
                                            </p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- ── Upload Form ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Upload --}}
            <div class="card p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Upload File Excel</h3>

                <div
                    class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-violet-400 transition">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                    <p class="text-sm font-medium text-gray-500 mb-1">Pilih file Excel</p>
                    <p class="text-xs text-gray-400 mb-4">Format .xlsx atau .xls · Maks. 5MB</p>
                    <input wire:model="file" type="file" accept=".xlsx,.xls"
                        class="text-sm text-gray-500
                              file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0
                              file:text-xs file:font-medium
                              file:bg-violet-50 file:text-violet-700
                              hover:file:bg-violet-100 file:transition">
                    @if ($fileError)
                        <p class="form-error mt-2">{{ $fileError }}</p>
                    @endif
                    <div wire:loading wire:target="file" class="text-xs text-violet-600 mt-2">
                        Membaca file...
                    </div>
                </div>
            </div>

            {{-- Petunjuk + Download Template --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Petunjuk Pengisian</h3>
                    <a href="{{ route('admin.employees.template') }}" class="btn-primary text-xs py-1.5 px-3">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Download Template
                    </a>
                </div>

                <div class="space-y-2 text-xs text-gray-600">
                    @foreach ([['A', 'NIK *', 'Wajib · unik per pegawai'], ['B', 'Nama *', 'Wajib · nama lengkap'], ['C', 'Gender *', 'Wajib · isi: male / female'], ['D', 'Tempat Lahir', 'Opsional'], ['E', 'Tanggal Lahir', 'Opsional · format: YYYY-MM-DD'], ['F', 'Pendidikan', 'Opsional · sd/smp/sma/d3/s1/s2/s3'], ['G', 'No. HP', 'Opsional'], ['H', 'Unit/Sekolah *', 'Wajib · harus sama persis dengan nama di sistem'], ['I', 'Tanggal Masuk *', 'Wajib · format: YYYY-MM-DD'], ['J', 'Tipe Pegawai', 'permanent / contract / intern'], ['K', 'Guru?', 'ya / tidak'], ['L', 'Departemen *', 'Wajib · harus sama persis dengan nama di sistem'], ['M', 'Jabatan *', 'Wajib · harus sama persis dengan nama di sistem'], ['N', 'Email', 'Opsional'], ['O', 'Alamat', 'Opsional']] as [$col, $label, $desc])
                        <div class="flex items-start gap-2">
                            <span
                                class="shrink-0 w-6 h-5 rounded bg-violet-100 text-violet-700 text-xs font-bold flex items-center justify-center">{{ $col }}</span>
                            <div>
                                <span class="font-medium text-gray-700">{{ $label }}</span>
                                <span class="text-gray-400 ml-1">— {{ $desc }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700">
                    Nama Sekolah, Departemen, dan Jabatan harus sama persis dengan yang terdaftar di Master Data.
                    Download template untuk melihat contoh pengisian.
                </div>
            </div>
        </div>
    @endif
</div>
