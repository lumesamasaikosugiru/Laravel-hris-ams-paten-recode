<div>
  <div class="page-header">
    <div class="flex flex-wrap gap-2 flex-1">
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari jenis cuti..." class="input w-64">
      <select wire:model.live="statusFilter" class="input w-auto">
        <option value="">Semua Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option>
      </select>
    </div>
    <button wire:click="openCreate" class="btn-primary"><x-icons.plus /> Tambah Jenis Cuti</button>
  </div>

  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr>
        <th class="w-8">#</th><th>Nama Cuti</th>
        <th class="text-center">Kuota</th>
        <th class="text-center hidden md:table-cell">Peruntukan</th>
        <th class="text-center hidden md:table-cell">Siklus</th>
        <th class="text-center hidden lg:table-cell">Dok. Wajib</th>
        <th class="text-center">Status</th><th class="text-center">Aksi</th>
      </tr></thead>
      <tbody>
        @forelse($leaveTypes as $lt)
        <tr>
          <td class="text-gray-400 text-xs">{{ $leaveTypes->firstItem()+$loop->index }}</td>
          <td>
            <p class="font-medium text-gray-800">{{ $lt->name }}</p>
            @if($lt->description)<p class="text-xs text-gray-400">{{ $lt->description }}</p>@endif
          </td>
          <td class="text-center"><span class="badge-green font-semibold">{{ $lt->quota }} hari</span></td>
          <td class="text-center hidden md:table-cell">
            <span class="badge {{ $lt->gender==='all' ? 'badge-gray' : ($lt->gender==='female' ? 'bg-pink-100 text-pink-700' : 'badge-blue') }}">
              {{ $lt->gender_label }}
            </span>
          </td>
          <td class="text-center hidden md:table-cell">
            <span class="badge {{ $lt->cycle==='annual' ? 'badge-purple' : 'bg-orange-100 text-orange-700' }}">
              {{ $lt->cycle_label }}
            </span>
          </td>
          <td class="text-center hidden lg:table-cell text-base">{{ $lt->requires_document ? '📎' : '—' }}</td>
          <td class="text-center">
            <button wire:click="toggleStatus({{ $lt->id }})" class="badge {{ $lt->is_active ? 'badge-green' : 'badge-gray' }} cursor-pointer hover:opacity-80 transition">
              {{ $lt->is_active ? 'Aktif' : 'Nonaktif' }}
            </button>
          </td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <button wire:click="openEdit({{ $lt->id }})" class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"><x-icons.pencil /></button>
              <button wire:click="confirmDelete({{ $lt->id }})" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition"><x-icons.trash /></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="py-16 text-center">
          <p class="text-sm font-medium text-gray-400">Belum ada jenis cuti</p>
          <p class="text-xs text-gray-300 mt-1">Contoh: Cuti Tahunan, Cuti Sakit, Cuti Melahirkan</p>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($leaveTypes->hasPages())
      <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $leaveTypes->links() }}</div>
    @endif
  </div>

  @if($showModal)
  <div class="modal-backdrop" wire:click="$set('showModal',false)">
    <div class="modal-box max-w-md" wire:click.stop>
      <div class="modal-header">
        <h3>{{ $editingId ? 'Edit Jenis Cuti' : 'Tambah Jenis Cuti' }}</h3>
        <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-body">
        <div>
          <label class="form-label">Nama Jenis Cuti <span class="text-red-500">*</span></label>
          <input wire:model="name" type="text" class="input @error('name') input-error @enderror" placeholder="Cuti Tahunan">
          @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Kuota (hari) <span class="text-red-500">*</span></label>
            <input wire:model="quota" type="number" min="1" max="365" class="input @error('quota') input-error @enderror">
            @error('quota')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="form-label">Status</label>
            <select wire:model="is_active" class="input"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Peruntukan Gender</label>
            <select wire:model="gender" class="input">
              <option value="all">Semua</option><option value="male">Laki-laki</option><option value="female">Perempuan</option>
            </select>
          </div>
          <div>
            <label class="form-label">Siklus</label>
            <select wire:model="cycle" class="input">
              <option value="annual">Tahunan</option><option value="once">Sekali Seumur Hidup</option>
            </select>
          </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
          <input wire:model="requires_document" type="checkbox" id="req_doc" class="mt-0.5 rounded border-gray-300 text-violet-600 w-4 h-4">
          <label for="req_doc" class="text-sm text-gray-700 cursor-pointer">
            Wajib upload dokumen pendukung
            <span class="block text-xs text-gray-400 font-normal">Contoh: surat dokter untuk cuti sakit</span>
          </label>
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
        <h3>Hapus Jenis Cuti?</h3>
        <button wire:click="$set('showDeleteModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-footer">
        <button wire:click="$set('showDeleteModal',false)" class="btn-ghost">Batal</button>
        <button wire:click="delete" class="btn-danger">Ya, Hapus</button>
      </div>
    </div>
  </div>
  @endif
</div>
