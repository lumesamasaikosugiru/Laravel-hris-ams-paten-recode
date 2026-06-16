<div class="p-4 space-y-4">
    @php $employee = App\Models\Employee::where('user_id', auth()->id())->with(['school','activeAssignment.position','activeAssignment.department'])->first(); @endphp

    @if(!$employee)
    <div class="portal-card p-8 text-center">
        <p class="text-gray-400">Data pegawai tidak ditemukan.</p>
        <p class="text-xs text-gray-300 mt-1">Hubungi Admin SDM</p>
    </div>
    @else

    {{-- Avatar & Nama --}}
    <div class="portal-card p-5 flex flex-col items-center text-center">
        @if($employee->photo)
        <img src="{{ Storage::url($employee->photo) }}"
             class="w-20 h-20 rounded-full object-cover mb-3 ring-4 ring-violet-100">
        @else
        <div class="w-20 h-20 rounded-full bg-violet-200 flex items-center justify-center mb-3 text-violet-700 text-2xl font-bold">
            {{ strtoupper(substr($employee->name, 0, 2)) }}
        </div>
        @endif
        <p class="text-lg font-bold text-gray-800">{{ $employee->name }}</p>
        <p class="text-sm text-gray-500">{{ $employee->activeAssignment?->position->name ?? '—' }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $employee->school->name }}</p>
        <div class="flex gap-2 mt-3">
            @if($employee->nipy)
            <span class="status-chip bg-violet-100 text-violet-700 text-xs">{{ $employee->nipy }}</span>
            @endif
            <span class="status-chip {{ $employee->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }} text-xs">
                {{ $employee->status === 'active' ? 'Aktif' : 'Masa Percobaan' }}
            </span>
        </div>
    </div>

    {{-- Info Identitas --}}
    <div class="portal-card overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Identitas</p>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach([
                ['NIK KTP',         $employee->national_id ?? '—'],
                ['Jenis Kelamin',   $employee->gender === 'male' ? 'Laki-laki' : 'Perempuan'],
                ['Tempat Lahir',    $employee->place_of_birth ?? '—'],
                ['Tanggal Lahir',   $employee->date_of_birth?->format('d M Y') ?? '—'],
                ['Agama',           ucfirst($employee->religion ?? '—')],
                ['Status Nikah',    ucfirst(str_replace('_',' ', $employee->marital_status ?? '—'))],
            ] as [$label, $val])
            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs text-gray-400 w-32 shrink-0">{{ $label }}</span>
                <span class="text-sm text-gray-700 font-medium text-right">{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Info Kepegawaian --}}
    <div class="portal-card overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Kepegawaian</p>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach([
                ['Jabatan',         $employee->activeAssignment?->position->name ?? '—'],
                ['Departemen',      $employee->activeAssignment?->department->name ?? '—'],
                ['Unit',            $employee->school->name],
                ['Tipe',            ucfirst($employee->employee_type ?? '—')],
                ['Tanggal Masuk',   $employee->join_date->format('d M Y')],
                ['Email',           $employee->email ?? '—'],
                ['No. HP',          $employee->phone ?? '—'],
            ] as [$label, $val])
            <div class="px-5 py-3 flex justify-between">
                <span class="text-xs text-gray-400 w-32 shrink-0">{{ $label }}</span>
                <span class="text-sm text-gray-700 font-medium text-right">{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Info Pensiun --}}
    @if($employee->date_of_birth)
    <div class="portal-card p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400">Usia Saat Ini</p>
                <p class="text-lg font-bold text-gray-800">{{ $employee->age }} tahun</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400">Pensiun</p>
                <p class="text-sm font-semibold {{ $employee->is_retired ? 'text-red-600' : 'text-gray-700' }}">
                    {{ $employee->retirement_date?->format('d M Y') }}
                </p>
                <p class="text-xs {{ $employee->is_retired ? 'text-red-500' : 'text-gray-400' }}">
                    @if($employee->is_retired) Sudah pensiun
                    @else {{ $employee->years_to_retirement }} tahun lagi
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
