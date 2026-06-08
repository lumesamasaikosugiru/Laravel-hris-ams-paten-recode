<div>
  <div class="page-header">
    <div class="flex flex-wrap gap-2 flex-1">
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari departemen..." class="input w-60">
      <select wire:model.live="schoolFilter" class="input w-auto">
        <option value="">Semua Sekolah</option>
        @foreach($schools as $sc)<option value="{{ $sc->id }}">{{ $sc->name }}</option>@endforeach
      </select>
      <select wire:model.live="statusFilter" class="input w-auto">
        <option value="">Semua Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option>
      </select>
    </div>
    <button wire:click="openCreate" class="btn-primary"><x-icons.plus /> Tambah Departemen</button>
  </div>

  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr>
        <th class="w-8">#</th><th>Kode</th><th>Nama Departemen</th>
        <th class="hidden md:table-cell">Sekolah</th>
        <th class="hidden lg:table-cell text-center">Jabatan</th>
        <th class="text-center">Status</th><th class="text-center">Aksi</th>
      </tr></thead>
      <tbody>
        @forelse($departments as $d)
        <tr>
          <td class="text-gray-400 text-xs">{{ $departments->firstItem()+$loop->index }}</td>
          <td><span class="badge-code">{{ $d->code }}</span></td>
          <td>
            <p class="font-medium text-gray-800">{{ $d->name }}</p>
            @if($d->description)<p class="text-xs text-gray-400 truncate max-w-xs">{{ $d->description }}</p>@endif
          </td>
          <td class="text-sm text-gray-500 hidden md:table-cell">{{ $d->school->name }}</td>
          <td class="text-center hidden lg:table-cell">
            <span class="badge-purple">{{ $d->positions()->count() }} jabatan</span>
          </td>
          <td class="text-center">
            <button wire:click="toggleStatus({{ $d->id }})" class="badge {{ $d->is_active ? 'badge-green' : 'badge-gray' }} cursor-pointer hover:opacity-80 transition">
              {{ $d->is_active ? 'Aktif' : 'Nonaktif' }}
            </button>
          </td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <button wire:click="openEdit({{ $d->id }})" class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"><x-icons.pencil /></button>
              <button wire:click="confirmDelete({{ $d->id }})" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition"><x-icons.trash /></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-16 text-center">
          <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
          </div>
          <p class="text-sm font-medium text-gray-400">Belum ada departemen</p>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($departments->hasPages())
      <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $departments->links() }}</div>
    @endif
  </div>

  @if($showModal)
  <div class="modal-backdrop" wire:click="$set('showModal',false)">
    <div class="modal-box max-w-md" wire:click.stop>
      <div class="modal-header">
        <h3>{{ $editingId ? 'Edit Departemen' : 'Tambah Departemen' }}</h3>
        <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-body">
        <div>
          <label class="form-label">Sekolah <span class="text-red-500">*</span></label>
          <select wire:model="school_id" class="input @error('school_id') input-error @enderror">
            <option value="">-- Pilih Sekolah --</option>
            @foreach($schools as $sc)<option value="{{ $sc->id }}">{{ $sc->name }}</option>@endforeach
          </select>
          @error('school_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Kode <span class="text-red-500">*</span></label>
            <input wire:model="code" type="text" class="input uppercase @error('code') input-error @enderror" placeholder="TU">
            @error('code')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="form-label">Status</label>
            <select wire:model="is_active" class="input"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
          </div>
        </div>
        <div>
          <label class="form-label">Nama Departemen <span class="text-red-500">*</span></label>
          <input wire:model="name" type="text" class="input @error('name') input-error @enderror" placeholder="Nama departemen">
          @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="form-label">Deskripsi</label>
          <textarea wire:model="description" rows="2" class="input resize-none" placeholder="Opsional"></textarea>
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

  @if($showDeleteModal)
  <div class="modal-backdrop" wire:click="$set('showDeleteModal',false)">
    <div class="modal-box max-w-sm" wire:click.stop>
      <div class="modal-header" style="background:linear-gradient(to right,#ef4444,#b91c1c)">
        <h3>Hapus Departemen?</h3>
        <button wire:click="$set('showDeleteModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-body text-center">
        <p class="text-sm text-gray-600">Jabatan yang terhubung juga akan terpengaruh.</p>
      </div>
      <div class="modal-footer">
        <button wire:click="$set('showDeleteModal',false)" class="btn-ghost">Batal</button>
        <button wire:click="delete" class="btn-danger">Ya, Hapus</button>
      </div>
    </div>
  </div>
  @endif
</div>
