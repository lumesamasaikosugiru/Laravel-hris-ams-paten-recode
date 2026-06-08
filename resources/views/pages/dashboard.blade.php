<x-layouts.admin>
    <x-slot:title>Dashboard</x-slot:title>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Sekolah Aktif',  \App\Models\School::where('is_active',true)->count(),  'bg-violet-50',  'text-violet-700', 'border-violet-100'],
            ['Total Pegawai',  \App\Models\Employee::where('status','active')->count(), 'bg-green-50',   'text-green-700',  'border-green-100'],
            ['Masa Percobaan', \App\Models\Employee::where('status','probation')->count(),'bg-amber-50', 'text-amber-700',  'border-amber-100'],
            ['Jenis Cuti',     \App\Models\LeaveType::where('is_active',true)->count(), 'bg-blue-50',   'text-blue-700',   'border-blue-100'],
        ] as [$label,$val,$bg,$txt,$border])
        <div class="card {{ $bg }} border {{ $border }}">
            <div class="p-5">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</p>
                <p class="text-3xl font-bold {{ $txt }} mt-1">{{ $val }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Progress --}}
    <div class="card">
        <div class="card-body">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Progress Development</h2>
            <div class="space-y-3">
                @foreach([
                    ['Phase 1', 'Fondasi & Master Data', 80, 'bg-green-500'],
                    ['Phase 2', 'Rekrutmen & Masa Percobaan', 0, 'bg-violet-500'],
                    ['Phase 3', 'Manajemen Pegawai', 0, 'bg-violet-500'],
                    ['Phase 4', 'Absensi Harian', 0, 'bg-violet-500'],
                    ['Phase 5', 'Cuti & Izin', 0, 'bg-violet-500'],
                    ['Phase 6', 'Dashboard & Laporan', 0, 'bg-violet-500'],
                ] as [$phase,$label,$pct,$color])
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium text-gray-400 w-16 shrink-0">{{ $phase }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                        <div class="{{ $color }} h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-28 shrink-0 truncate">{{ $label }}</span>
                    <span class="text-xs font-semibold w-8 text-right {{ $pct > 0 ? 'text-green-600' : 'text-gray-300' }}">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.admin>
