<div>
    <div class="page-header">
        <div class="flex flex-wrap gap-2 flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari skill..." class="input w-60">
            <select wire:model.live="categoryFilter" class="input w-auto">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="openCreate" class="btn-primary"><x-icons.plus /> Tambah Skill</button>
    </div>

    <div class="tbl-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th class="w-8">#</th>
                    <th>Nama Skill</th>
                    <th>Kategori</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skills as $s)
                    <tr>
                        <td class="text-gray-400 text-xs">{{ $skills->firstItem() + $loop->index }}</td>
                        <td class="font-medium text-gray-800">{{ $s->name }}</td>
                        <td>
                            @if ($s->category)
                                <span class="badge-purple">{{ $s->category }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button wire:click="toggleStatus({{ $s->id }})"
                                class="badge {{ $s->is_active ? 'badge-green' : 'badge-gray' }} cursor-pointer hover:opacity-80 transition">
                                {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button wire:click="openEdit({{ $s->id }})"
                                    class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"><x-icons.pencil /></button>
                                <button wire:click="confirmDelete({{ $s->id }})"
                                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition"><x-icons.trash /></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <p class="text-sm font-medium text-gray-400">Belum ada skill</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($skills->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $skills->links() }}</div>
        @endif
    </div>

    @if ($showModal)
        <div class="modal-backdrop" wire:click="$set('showModal',false)">
            <div class="modal-box max-w-md" wire:click.stop>
                <div class="modal-header">
                    <h3>{{ $editingId ? 'Edit Skill' : 'Tambah Skill' }}</h3>
                    <button wire:click="$set('showModal',false)"
                        class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
                </div>
                <div class="modal-body">
                    <div>
                        <label class="form-label">Nama Skill <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="input @error('name') input-error @enderror"
                            placeholder="Nama kompetensi">
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Kategori</label>
                            <input wire:model="category" type="text" class="input"
                                placeholder="IT, Bahasa, Mengajar...">
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="is_active" class="input">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="save">Simpan</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="modal-backdrop" wire:click="$set('showDeleteModal',false)">
            <div class="modal-box max-w-sm" wire:click.stop>
                <div class="modal-header" style="background:linear-gradient(to right,#ef4444,#b91c1c)">
                    <h3>Hapus Skill?</h3>
                    <button wire:click="$set('showDeleteModal',false)"
                        class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('showDeleteModal',false)" class="btn-ghost">Batal</button>
                    <button wire:click="delete" class="btn-danger">Ya, Hapus</button>
                </div>
            </div>
        </div>
    @endif
</div>
