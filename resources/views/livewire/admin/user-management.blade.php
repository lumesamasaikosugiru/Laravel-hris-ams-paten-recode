<div>
    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Cari nama atau email..." class="input w-64">
            <select wire:model.live="roleFilter" class="input w-auto">
                <option value="">Semua Role</option>
                @foreach($roles as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="openCreate" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah User
        </button>
    </div>

    {{-- Table --}}
    <div class="tbl-wrap">
        <table class="tbl">
            <thead><tr>
                <th class="w-8">#</th>
                <th>Nama & Email</th>
                <th>Role</th>
                <th class="hidden md:table-cell">Terhubung ke Pegawai</th>
                <th class="text-center">Status</th>
                <th class="text-center">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-gray-400 text-xs">{{ $users->firstItem() + $loop->index }}</td>
                    <td>
                        <p class="font-medium text-gray-800">{{ $user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                    </td>
                    <td>
                        @foreach($user->roles as $role)
                        <span class="badge-purple text-xs">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="hidden md:table-cell text-sm">
                        @if($user->employee)
                            <p class="font-medium text-gray-700">{{ $user->employee->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->employee->school->name }}</p>
                        @else
                            <span class="text-xs text-gray-300 italic">Belum terhubung</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($user->is_active ?? true)
                            <span class="badge-green">Aktif</span>
                        @else
                            <span class="badge-red">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Edit --}}
                            <button wire:click="openEdit({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"
                                    title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                </svg>
                            </button>
                            {{-- Link Pegawai --}}
                            <button wire:click="openLinkModal({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 transition"
                                    title="Hubungkan ke Pegawai">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                </svg>
                            </button>
                            {{-- Reset Password --}}
                            <button wire:click="openPasswordModal({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-amber-500 hover:bg-amber-50 transition"
                                    title="Reset Password">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                                </svg>
                            </button>
                            {{-- Toggle Aktif --}}
                            @if($user->id !== auth()->id())
                            <button wire:click="openToggleModal({{ $user->id }})"
                                    class="p-1.5 rounded-lg {{ ($user->is_active ?? true) ? 'text-red-400 hover:bg-red-50' : 'text-green-500 hover:bg-green-50' }} transition"
                                    title="{{ ($user->is_active ?? true) ? 'Nonaktifkan' : 'Aktifkan' }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    @if($user->is_active ?? true)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                    @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    @endif
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-16 text-center text-sm text-gray-400">Belum ada user</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- ══ MODAL: Tambah / Edit User ══ --}}
    @if($showModal)
    <div class="modal-backdrop" wire:click="$set('showModal',false)">
        <div class="modal-box max-w-md" wire:click.stop>
            <div class="modal-header">
                <h3>{{ $isEdit ? 'Edit User' : 'Tambah User Baru' }}</h3>
                <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                {{-- Nama --}}
                <div>
                    <label class="form-label">Nama <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="input @error('name') input-error @enderror"
                           placeholder="Nama lengkap">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="form-label">Email <span class="text-red-500">*</span></label>
                    <input wire:model="email" type="email" class="input @error('email') input-error @enderror"
                           placeholder="email@fatahillah.id">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="form-label">
                        Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '*' }}
                    </label>
                    <input wire:model="password" type="password"
                           class="input @error('password') input-error @enderror"
                           placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Min. 8 karakter' }}">
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Role --}}
                <div>
                    <label class="form-label">Role <span class="text-red-500">*</span></label>
                    <select wire:model="role" class="input @error('role') input-error @enderror">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Link ke Pegawai --}}
                <div>
                    <label class="form-label">Hubungkan ke Pegawai</label>
                    <select wire:model="employee_id" class="input">
                        <option value="">-- Pilih Pegawai (opsional) --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} — {{ $emp->nipy ?? $emp->nik }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        Hubungkan akun ini ke data pegawai agar bisa akses portal.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="save">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Buat Akun' }}
                    </span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MODAL: Reset Password ══ --}}
    @if($showPasswordModal)
    <div class="modal-backdrop" wire:click="$set('showPasswordModal',false)">
        <div class="modal-box max-w-sm" wire:click.stop>
            <div class="modal-header" style="background:linear-gradient(to right,#d97706,#b45309)">
                <h3>Reset Password</h3>
                <button wire:click="$set('showPasswordModal',false)" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600 mb-3">
                    Reset password untuk <strong>{{ $passwordName }}</strong>
                </p>
                <div>
                    <label class="form-label">Password Baru <span class="text-red-500">*</span></label>
                    <input wire:model="newPassword" type="password"
                           class="input @error('newPassword') input-error @enderror"
                           placeholder="Min. 8 karakter">
                    @error('newPassword')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showPasswordModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="resetPassword" wire:loading.attr="disabled"
                        class="btn-primary" style="background:#d97706">
                    <span wire:loading.remove wire:target="resetPassword">Reset Password</span>
                    <span wire:loading wire:target="resetPassword">Mereset...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MODAL: Link ke Pegawai ══ --}}
    @if($showLinkModal)
    <div class="modal-backdrop" wire:click="$set('showLinkModal',false)">
        <div class="modal-box max-w-sm" wire:click.stop>
            <div class="modal-header" style="background:linear-gradient(to right,#1d4ed8,#1e40af)">
                <h3>Hubungkan ke Pegawai</h3>
                <button wire:click="$set('showLinkModal',false)" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm text-gray-600 mb-3">
                    Akun <strong>{{ $linkUserName }}</strong>
                </p>
                <div>
                    <label class="form-label">Pilih Pegawai <span class="text-red-500">*</span></label>
                    <select wire:model="linkEmployeeId"
                            class="input @error('linkEmployeeId') input-error @enderror">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">
                                {{ $emp->name }} — {{ $emp->nipy ?? $emp->nik }}
                            </option>
                        @endforeach
                    </select>
                    @error('linkEmployeeId')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    Menghubungkan akun ini ke data pegawai agar bisa mengakses portal mobile.
                </p>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showLinkModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="saveLink" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="saveLink">Hubungkan</span>
                    <span wire:loading wire:target="saveLink">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ MODAL: Toggle Aktif ══ --}}
    @if($showToggleModal)
    <div class="modal-backdrop" wire:click="$set('showToggleModal',false)">
        <div class="modal-box max-w-sm" wire:click.stop>
            <div class="modal-header"
                 style="background:linear-gradient(to right,{{ $toggleIsActive ? '#dc2626,#b91c1c' : '#16a34a,#15803d' }})">
                <h3>{{ $toggleIsActive ? 'Nonaktifkan' : 'Aktifkan' }} Akun</h3>
                <button wire:click="$set('showToggleModal',false)" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="text-sm text-gray-600">
                    {{ $toggleIsActive ? 'Nonaktifkan' : 'Aktifkan' }} akun
                    <strong>{{ $toggleUserName }}</strong>?
                </p>
                @if($toggleIsActive)
                <p class="text-xs text-gray-400 mt-2">
                    User tidak bisa login sampai diaktifkan kembali.
                </p>
                @endif
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showToggleModal',false)" class="btn-ghost">Batal</button>
                <button wire:click="toggleActive" wire:loading.attr="disabled"
                        class="{{ $toggleIsActive ? 'btn-danger' : 'btn' }} text-white"
                        style="{{ !$toggleIsActive ? 'background:#16a34a' : '' }}">
                    <span wire:loading.remove wire:target="toggleActive">
                        Ya, {{ $toggleIsActive ? 'Nonaktifkan' : 'Aktifkan' }}
                    </span>
                    <span wire:loading wire:target="toggleActive">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
