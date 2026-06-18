<div>
    {{-- Toolbar --}}
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..."
                class="input w-64">
            <select wire:model.live="roleFilter" class="input w-auto">
                <option value="">Semua Role</option>
                @foreach ($roles as $r)
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
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Nama & Email</th>
                    <th>Role</th>
                    <th class="hidden md:table-cell">Terhubung ke Pegawai</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    {{--
                    Klik baris (di luar tombol aksi) langsung buka modal Edit.
                    Tombol aksi memakai @click.stop di markup-nya sendiri (lihat di bawah)
                    supaya klik tombol tidak ikut memicu wire:click baris ini.
                --}}
                    <tr wire:click="openEdit({{ $user->id }})" class="cursor-pointer">
                        <td class="text-gray-400 text-xs">{{ $users->firstItem() + $loop->index }}</td>
                        <td>
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                        </td>
                        <td>
                            @foreach ($user->roles as $role)
                                <span class="badge-purple text-xs">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="hidden md:table-cell text-sm">
                            @if ($user->employee)
                                <p class="font-medium text-gray-700">{{ $user->employee->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->employee->school->name }}</p>
                            @else
                                <span class="text-xs text-gray-300 italic">Belum terhubung</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($user->is_active ?? true)
                                <span class="badge-green">Aktif</span>
                            @else
                                <span class="badge-red">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center" @click.stop>
                            <div class="flex items-center justify-center gap-1">
                                {{-- Edit --}}
                                <button wire:click="openEdit({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </button>
                                {{-- Link Pegawai --}}
                                <button wire:click="openLinkModal({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 transition"
                                    title="Hubungkan ke Pegawai">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                    </svg>
                                </button>
                                {{-- Reset Password --}}
                                <button wire:click="openPasswordModal({{ $user->id }})"
                                    class="p-1.5 rounded-lg text-amber-500 hover:bg-amber-50 transition"
                                    title="Reset Password">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z" />
                                    </svg>
                                </button>
                                {{-- Toggle Aktif --}}
                                @if ($user->id !== auth()->id())
                                    <button wire:click="openToggleModal({{ $user->id }})"
                                        class="p-1.5 rounded-lg {{ $user->is_active ?? true ? 'text-red-400 hover:bg-red-50' : 'text-green-500 hover:bg-green-50' }} transition"
                                        title="{{ $user->is_active ?? true ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            @if ($user->is_active ?? true)
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            @endif
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center text-sm text-gray-400">Belum ada user</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- ══ MODAL: Tambah / Edit User ══ --}}
    @if ($showModal)
        <div class="modal-backdrop" wire:click="$set('showModal',false)">
            <div class="modal-box max-w-md" wire:click.stop>
                <div class="modal-header">
                    <h3>{{ $isEdit ? 'Edit User' : 'Tambah User Baru' }}</h3>
                    <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
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
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email" class="input @error('email') input-error @enderror"
                            placeholder="email@fatahillah.id">
                        @error('email')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="form-label">
                            Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '*' }}
                        </label>
                        <input wire:model="password" type="password"
                            class="input @error('password') input-error @enderror"
                            placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Min. 8 karakter' }}">
                        @error('password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select wire:model="role" class="input @error('role') input-error @enderror">
                            <option value="">-- Pilih Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Link ke Pegawai --}}
                    <div x-data="{
                        open: false,
                        search: '',
                        selected: null,
                        activeIndex: -1,
                        employees: {{ Js::from($employees) }},
                        get filtered() {
                            if (!this.search) return this.employees.slice(0, 50);
                            const q = this.search.toLowerCase();
                            return this.employees.filter(e =>
                                e.name.toLowerCase().includes(q) || e.code.toLowerCase().includes(q)
                            ).slice(0, 50);
                        },
                        select(emp) {
                            this.selected = emp;
                            this.search = emp.name;
                            this.open = false;
                            this.activeIndex = -1;
                            $wire.set('employee_id', emp.id);
                        },
                        clear() {
                            this.selected = null;
                            this.search = '';
                            this.activeIndex = -1;
                            $wire.set('employee_id', '');
                        },
                        moveDown() {
                            if (!this.open) { this.open = true; return; }
                            this.activeIndex = Math.min(this.activeIndex + 1, this.filtered.length - 1);
                            this.scrollActive();
                        },
                        moveUp() {
                            this.activeIndex = Math.max(this.activeIndex - 1, 0);
                            this.scrollActive();
                        },
                        scrollActive() {
                            this.$nextTick(() => {
                                const el = document.getElementById('user-emp-item-' + this.activeIndex);
                                if (el) el.scrollIntoView({ block: 'nearest' });
                            });
                        }
                    }" x-init="const current = employees.find(e => e.id == @js($employee_id));
                    if (current) { selected = current;
                        search = current.name; }" x-on:click.outside="open = false"
                        wire:key="emp-edit-{{ $editingId }}">
                        <label class="form-label">Hubungkan ke Pegawai</label>
                        <div class="relative">
                            <input type="text" x-model="search" x-on:focus="open = true; activeIndex = -1"
                                x-on:input="open = true; activeIndex = -1"
                                x-on:keydown.arrow-down.prevent="moveDown()" x-on:keydown.arrow-up.prevent="moveUp()"
                                x-on:keydown.enter.prevent="filtered[activeIndex] ? select(filtered[activeIndex]) : (filtered.length === 1 ? select(filtered[0]) : null)"
                                x-on:keydown.escape="open = false" placeholder="Ketik nama atau NIPY/NIK..."
                                class="input pr-8" autocomplete="off">
                            <button x-show="search" x-on:click="clear()" type="button"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div x-show="open && filtered.length > 0" x-transition
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-52 overflow-y-auto">
                                <template x-for="(emp, index) in filtered" :key="emp.id">
                                    <div :id="'user-emp-item-' + index" x-on:click="select(emp)"
                                        x-on:mouseover="activeIndex = index"
                                        :class="activeIndex === index ? 'bg-violet-100 text-violet-800' : 'hover:bg-violet-50'"
                                        class="px-3 py-2.5 cursor-pointer border-b border-gray-50 last:border-0 flex items-center justify-between transition-colors">
                                        <span class="text-sm font-medium" x-text="emp.name"></span>
                                        <span class="text-xs text-gray-400 font-mono ml-2" x-text="emp.code"></span>
                                    </div>
                                </template>
                            </div>
                            <div x-show="open && search && filtered.length === 0"
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 px-3 py-3 text-sm text-gray-400 text-center">
                                Tidak ditemukan
                            </div>
                        </div>
                        <div x-show="selected" class="mt-1.5 flex items-center gap-1.5 text-xs text-green-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span x-text="selected ? selected.name + ' (' + selected.code + ')' : ''"></span>
                        </div>
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
    @if ($showPasswordModal)
        <div class="modal-backdrop" wire:click="$set('showPasswordModal',false)">
            <div class="modal-box max-w-sm" wire:click.stop>
                <div class="modal-header" style="background:linear-gradient(to right,#d97706,#b45309)">
                    <h3>Reset Password</h3>
                    <button wire:click="$set('showPasswordModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
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
                            class="input @error('newPassword') input-error @enderror" placeholder="Min. 8 karakter">
                        @error('newPassword')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showPasswordModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="resetPassword" wire:loading.attr="disabled" class="btn-primary"
                        style="background:#d97706">
                        <span wire:loading.remove wire:target="resetPassword">Reset Password</span>
                        <span wire:loading wire:target="resetPassword">Mereset...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ MODAL: Link ke Pegawai ══ --}}
    @if ($showLinkModal)
        <div class="modal-backdrop" wire:click="$set('showLinkModal',false)">
            <div class="modal-box max-w-sm" wire:click.stop>
                <div class="modal-header" style="background:linear-gradient(to right,#1d4ed8,#1e40af)">
                    <h3>Hubungkan ke Pegawai</h3>
                    <button wire:click="$set('showLinkModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-sm text-gray-600 mb-3">
                        Akun <strong>{{ $linkUserName }}</strong>
                    </p>

                    {{-- Peringatan: akun ini sudah terhubung ke pegawai lain --}}
                    @if ($linkCurrentEmployeeName)
                        <div
                            class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5 mb-3">
                            <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <p class="text-xs text-amber-700 leading-relaxed">
                                Akun ini sudah terhubung ke <strong>{{ $linkCurrentEmployeeName }}</strong>.
                                Memilih pegawai lain akan <strong>memutus link tersebut</strong> dan
                                menggantinya dengan yang baru.
                            </p>
                        </div>
                    @endif

                    <div x-data="{
                        open: false,
                        search: '',
                        selected: null,
                        activeIndex: -1,
                        employees: {{ Js::from($employees) }},
                        get filtered() {
                            if (!this.search) return this.employees.slice(0, 50);
                            const q = this.search.toLowerCase();
                            return this.employees.filter(e =>
                                e.name.toLowerCase().includes(q) || e.code.toLowerCase().includes(q)
                            ).slice(0, 50);
                        },
                        select(emp) {
                            this.selected = emp;
                            this.search = emp.name;
                            this.open = false;
                            this.activeIndex = -1;
                            $wire.set('linkEmployeeId', emp.id);
                        },
                        clear() {
                            this.selected = null;
                            this.search = '';
                            this.activeIndex = -1;
                            $wire.set('linkEmployeeId', '');
                        },
                        moveDown() {
                            if (!this.open) { this.open = true; return; }
                            this.activeIndex = Math.min(this.activeIndex + 1, this.filtered.length - 1);
                            this.scrollActive();
                        },
                        moveUp() {
                            this.activeIndex = Math.max(this.activeIndex - 1, 0);
                            this.scrollActive();
                        },
                        scrollActive() {
                            this.$nextTick(() => {
                                const el = document.getElementById('link-emp-item-' + this.activeIndex);
                                if (el) el.scrollIntoView({ block: 'nearest' });
                            });
                        }
                    }" x-init="const current = employees.find(e => e.id == @js($linkEmployeeId));
                    if (current) { selected = current;
                        search = current.name; }" x-on:click.outside="open = false"
                        wire:key="emp-link-{{ $linkUserId }}">
                        <label class="form-label">Pilih Pegawai <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" x-model="search" x-on:focus="open = true; activeIndex = -1"
                                x-on:input="open = true; activeIndex = -1"
                                x-on:keydown.arrow-down.prevent="moveDown()" x-on:keydown.arrow-up.prevent="moveUp()"
                                x-on:keydown.enter.prevent="filtered[activeIndex] ? select(filtered[activeIndex]) : (filtered.length === 1 ? select(filtered[0]) : null)"
                                x-on:keydown.escape="open = false" placeholder="Ketik nama atau NIPY/NIK..."
                                class="input pr-8 @error('linkEmployeeId') input-error @enderror" autocomplete="off">
                            <button x-show="search" x-on:click="clear()" type="button"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div x-show="open && filtered.length > 0" x-transition
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-52 overflow-y-auto">
                                <template x-for="(emp, index) in filtered" :key="emp.id">
                                    <div :id="'link-emp-item-' + index" x-on:click="select(emp)"
                                        x-on:mouseover="activeIndex = index"
                                        :class="activeIndex === index ? 'bg-violet-100 text-violet-800' : 'hover:bg-violet-50'"
                                        class="px-3 py-2.5 cursor-pointer border-b border-gray-50 last:border-0 flex items-center justify-between transition-colors">
                                        <span class="text-sm font-medium" x-text="emp.name"></span>
                                        <span class="text-xs text-gray-400 font-mono ml-2" x-text="emp.code"></span>
                                    </div>
                                </template>
                            </div>
                            <div x-show="open && search && filtered.length === 0"
                                class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 px-3 py-3 text-sm text-gray-400 text-center">
                                Tidak ditemukan
                            </div>
                        </div>
                        <div x-show="selected" class="mt-1.5 flex items-center gap-1.5 text-xs text-green-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span x-text="selected ? selected.name + ' (' + selected.code + ')' : ''"></span>
                        </div>
                        @error('linkEmployeeId')
                            <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
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
    @if ($showToggleModal)
        <div class="modal-backdrop" wire:click="$set('showToggleModal',false)">
            <div class="modal-box max-w-sm" wire:click.stop>
                <div class="modal-header"
                    style="background:linear-gradient(to right,{{ $toggleIsActive ? '#dc2626,#b91c1c' : '#16a34a,#15803d' }})">
                    <h3>{{ $toggleIsActive ? 'Nonaktifkan' : 'Aktifkan' }} Akun</h3>
                    <button wire:click="$set('showToggleModal',false)" class="text-white/70 hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-sm text-gray-600">
                        {{ $toggleIsActive ? 'Nonaktifkan' : 'Aktifkan' }} akun
                        <strong>{{ $toggleUserName }}</strong>?
                    </p>
                    @if ($toggleIsActive)
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
