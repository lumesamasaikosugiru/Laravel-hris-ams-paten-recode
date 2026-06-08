<div>
  {{-- Toolbar --}}
  <div class="page-header">
    <div class="flex flex-wrap gap-2 flex-1">
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari sekolah..." class="input w-64">
      <select wire:model.live="statusFilter" class="input w-auto">
        <option value="">Semua Status</option>
        <option value="1">Aktif</option>
        <option value="0">Nonaktif</option>
      </select>
    </div>
    <button wire:click="openCreate" class="btn-primary"><x-icons.plus /> Tambah Sekolah</button>
  </div>

  {{-- Table --}}
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr>
        <th class="w-8">#</th><th>Kode</th><th>Nama Sekolah</th>
        <th class="hidden md:table-cell">Kepala Sekolah</th>
        <th class="hidden lg:table-cell">Telepon</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr></thead>
      <tbody>
        @forelse($schools as $s)
        <tr>
          <td class="text-gray-400 text-xs">{{ $schools->firstItem()+$loop->index }}</td>
          <td><span class="badge-code">{{ $s->code }}</span></td>
          <td class="font-medium text-gray-800">{{ $s->name }}</td>
          <td class="text-gray-500 text-sm hidden md:table-cell">{{ $s->principal_name ?? '—' }}</td>
          <td class="text-gray-500 text-sm hidden lg:table-cell">{{ $s->phone ?? '—' }}</td>
          <td class="text-center">
            <button wire:click="toggleStatus({{ $s->id }})" class="badge {{ $s->is_active ? 'badge-green' : 'badge-gray' }} cursor-pointer hover:opacity-80 transition">
              {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
            </button>
          </td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <button wire:click="openEdit({{ $s->id }})" class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"><x-icons.pencil /></button>
              <button wire:click="confirmDelete({{ $s->id }})" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition"><x-icons.trash /></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-16 text-center">
          <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>
          </div>
          <p class="text-sm font-medium text-gray-400">Belum ada data sekolah</p>
          <p class="text-xs text-gray-300 mt-1">Klik Tambah Sekolah untuk memulai</p>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($schools->hasPages())
      <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $schools->links() }}</div>
    @endif
  </div>

  {{-- Modal --}}
  @if($showModal)
  <div class="modal-backdrop" wire:click="$set('showModal',false)">
    <div class="modal-box max-w-lg" wire:click.stop>
      <div class="modal-header">
        <h3>{{ $editingId ? 'Edit Sekolah' : 'Tambah Sekolah' }}</h3>
        <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-body">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Kode <span class="text-red-500">*</span></label>
            <input wire:model="code" type="text" class="input uppercase @error('code') input-error @enderror" placeholder="SMK-01">
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="form-label">Status</label>
            <select wire:model="is_active" class="input">
              <option value="1">Aktif</option><option value="0">Nonaktif</option>
            </select>
          </div>
        </div>
        <div>
          <label class="form-label">Nama Sekolah <span class="text-red-500">*</span></label>
          <input wire:model="name" type="text" class="input @error('name') input-error @enderror" placeholder="Nama lengkap sekolah">
          @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="form-label">Kepala Sekolah</label>
          <input wire:model="principal_name" type="text" class="input" placeholder="Nama kepala sekolah">
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Telepon</label>
            <input wire:model="phone" type="text" class="input" placeholder="021xxxxxxx">
          </div>
          <div>
            <label class="form-label">Email</label>
            <input wire:model="email" type="email" class="input @error('email') input-error @enderror" placeholder="sekolah@email.com">
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
          </div>
        </div>
        <div>
          <label class="form-label">Alamat</label>
          <textarea wire:model="address" rows="2" class="input resize-none" placeholder="Alamat lengkap"></textarea>
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

  {{-- Delete Modal --}}
  @if($showDeleteModal)
  <div class="modal-backdrop" wire:click="$set('showDeleteModal',false)">
    <div class="modal-box max-w-sm" wire:click.stop>
      <div class="modal-header" style="background:linear-gradient(to right,#ef4444,#b91c1c)">
        <h3>Hapus Sekolah?</h3>
        <button wire:click="$set('showDeleteModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-body text-center">
        <x-icons.warning class="w-10 h-10 text-red-400 mx-auto mb-2" />
        <p class="text-sm text-gray-600">Data sekolah akan dihapus (soft delete). Dapat dipulihkan oleh Super Admin.</p>
      </div>
      <div class="modal-footer">
        <button wire:click="$set('showDeleteModal',false)" class="btn-ghost">Batal</button>
        <button wire:click="delete" class="btn-danger">Ya, Hapus</button>
      </div>
    </div>
  </div>
  @endif
</div>
