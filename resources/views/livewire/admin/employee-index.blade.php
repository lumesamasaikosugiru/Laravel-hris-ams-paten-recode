<div>
    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <button wire:click="$set('statusFilter','active')"
            class="card p-4 text-left hover:border-violet-300 transition
                       {{ $statusFilter === 'active' ? 'border-violet-400 bg-violet-50' : '' }}">
            <p class="text-2xl font-bold text-green-600">{{ $counts['active'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Pegawai Aktif</p>
        </button>
        <button wire:click="$set('statusFilter','probation')"
            class="card p-4 text-left hover:border-violet-300 transition
                       {{ $statusFilter === 'probation' ? 'border-violet-400 bg-violet-50' : '' }}">
            <p class="text-2xl font-bold text-amber-600">{{ $counts['probation'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Masa Percobaan</p>
        </button>
        <button wire:click="$set('statusFilter','')"
            class="card p-4 text-left hover:border-violet-300 transition
                       {{ $statusFilter === '' ? 'border-violet-400 bg-violet-50' : '' }}">
            <p class="text-2xl font-bold text-gray-600">
                {{ $counts['active'] + $counts['probation'] + $counts['inactive'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Semua Pegawai</p>
        </button>
    </div>

    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, NIK, NIPY..."
                class="input w-64">
            <select wire:model.live="schoolFilter" class="input w-auto">
                <option value="">Semua Unit</option>
                @foreach ($schools as $sc)
                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="typeFilter" class="input w-auto">
                <option value="">Semua Tipe</option>
                <option value="permanent">Tetap</option>
                <option value="contract">Kontrak</option>
                <option value="intern">Magang</option>
            </select>
            <select wire:model.live="statusFilter" class="input w-auto">
                <option value="active">Aktif</option>
                <option value="probation">Masa Percobaan</option>
                <option value="">Semua Status</option>
                <option value="inactive">Nonaktif</option>
                <option value="resigned">Mengundurkan Diri</option>
                <option value="terminated">Diberhentikan</option>
            </select>
        </div>
        {{-- TAMBAH DATA PEGAWAI --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.employees.import') }}"
                class="btn-ghost border border-gray-300 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Import Excel
            </a>
            <a href="{{ route('admin.employees.create') }}" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Pegawai
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Pegawai</th>
                    <th class="hidden md:table-cell">Jabatan</th>
                    <th class="hidden lg:table-cell">Unit</th>
                    <th class="text-center hidden lg:table-cell">Tipe</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr onclick="window.location='{{ route('admin.employees.show', $emp) }}'"
                        class="cursor-pointer hover:bg-violet-50 transition-colors">
                        <td class="text-gray-400 text-xs">{{ $employees->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                {{-- Avatar --}}
                                <div
                                    class="w-9 h-9 rounded-full shrink-0 overflow-hidden flex items-center justify-center font-semibold text-sm text-white
                                        {{ $emp->gender === 'female' ? 'bg-pink-400' : 'bg-violet-500' }}">
                                    @if ($emp->photo)
                                        <img src="{{ Storage::url($emp->photo) }}" class="w-full h-full object-cover"
                                            alt="">
                                    @else
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">{{ $emp->name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">
                                        {{ $emp->nipy ?? $emp->nik }}
                                        @if (!$emp->nipy)
                                            <span class="text-amber-500 font-sans">(sementara)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="hidden md:table-cell">
                            @if ($emp->activeAssignment)
                                <p class="text-sm text-gray-700">{{ $emp->activeAssignment->position->name }}</p>
                                <p class="text-xs text-gray-400">{{ $emp->activeAssignment->department->name }}</p>
                            @else
                                <span class="text-xs text-gray-300 italic">Belum ada jabatan</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-500 hidden lg:table-cell">
                            {{ $emp->school->name }}
                        </td>
                        <td class="text-center hidden lg:table-cell">
                            <div>
                                <span
                                    class="badge {{ $emp->employee_type === 'permanent'
                                        ? 'badge-green'
                                        : ($emp->employee_type === 'contract'
                                            ? 'bg-yellow-100 text-yellow-700'
                                            : 'badge-blue') }}">
                                    {{ $emp->employee_type_label }}
                                </span>
                                <span
                                    class="block text-xs mt-0.5 {{ $emp->is_guru ? 'text-violet-500' : 'text-gray-400' }}">
                                    {{ $emp->role_label }}
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $emp->status_color }}">
                                {{ $emp->status_label }}
                            </span>
                            @if ($emp->is_probation_overdue)
                                <p class="text-xs text-red-500 mt-0.5 font-medium">Perlu evaluasi!</p>
                            @elseif($emp->is_on_probation && $emp->probation_days_left !== null)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $emp->probation_days_left }}h lagi</p>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.employees.show', $emp) }}"
                                    class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition" title="Detail">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.employees.edit', $emp) }}"
                                    class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div
                                class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-400">Belum ada data pegawai</p>
                            <p class="text-xs text-gray-300 mt-1">Tambah pegawai atau konversi dari pelamar yang
                                diterima</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($employees->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
