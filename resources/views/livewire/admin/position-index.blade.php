<div>
  <div class="page-header">
    <div class="flex flex-wrap gap-2 flex-1">
      <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari jabatan..." class="input w-52">
      <select wire:model.live="schoolFilter" class="input w-auto">
        <option value="">Semua Sekolah</option>
        @foreach($schools as $sc)<option value="{{ $sc->id }}">{{ $sc->name }}</option>@endforeach
      </select>
      <select wire:model.live="departmentFilter" class="input w-auto">
        <option value="">Semua Dept</option>
        @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
      </select>
      <select wire:model.live="statusFilter" class="input w-auto">
        <option value="">Semua Status</option><option value="1">Aktif</option><option value="0">Nonaktif</option>
      </select>
    </div>
    <button wire:click="openCreate" class="btn-primary"><x-icons.plus /> Tambah Jabatan</button>
  </div>

  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr>
        <th class="w-8">#</th><th>Nama Jabatan</th>
        <th class="hidden md:table-cell">Departemen</th>
        <th class="hidden lg:table-cell">Sekolah</th>
        <th class="text-center w-20">Level</th>
        <th class="text-center">Status</th><th class="text-center">Aksi</th>
      </tr></thead>
      <tbody>
        @forelse($positions as $p)
        <tr>
          <td class="text-gray-400 text-xs">{{ $positions->firstItem()+$loop->index }}</td>
          <td>
            <p class="font-medium text-gray-800">{{ $p->name }}</p>
            @if($p->description)<p class="text-xs text-gray-400 truncate max-w-xs">{{ $p->description }}</p>@endif
          </td>
          <td class="hidden md:table-cell">
            <span class="badge-code mr-1">{{ $p->department->code }}</span>
            <span class="text-xs text-gray-500">{{ $p->department->name }}</span>
          </td>
          <td class="text-sm text-gray-500 hidden lg:table-cell">{{ $p->school->name }}</td>
          <td class="text-center">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $p->level >= 3 ? 'badge-purple' : 'badge-gray' }}">
              {{ $p->level }}
            </span>
          </td>
          <td class="text-center">
            <button wire:click="toggleStatus({{ $p->id }})" class="badge {{ $p->is_active ? 'badge-green' : 'badge-gray' }} cursor-pointer hover:opacity-80 transition">
              {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
            </button>
          </td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <button wire:click="openEdit({{ $p->id }})" class="p-1.5 rounded-lg text-violet-600 hover:bg-violet-50 transition"><x-icons.pencil /></button>
              <button wire:click="confirmDelete({{ $p->id }})" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition"><x-icons.trash /></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="py-16 text-center">
          <p class="text-sm font-medium text-gray-400">Belum ada jabatan</p>
        </td></tr>
        @endforelse
      </tbody>
    </table>
    @if($positions->hasPages())
      <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">{{ $positions->links() }}</div>
    @endif
  </div>

  @if($showModal)
  <div class="modal-backdrop" wire:click="$set('showModal',false)">
    <div class="modal-box max-w-md" wire:click.stop>
      <div class="modal-header">
        <h3>{{ $editingId ? 'Edit Jabatan' : 'Tambah Jabatan' }}</h3>
        <button wire:click="$set('showModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-body">
        <div>
          <label class="form-label">Sekolah <span class="text-red-500">*</span></label>
          <select wire:model.live="school_id" class="input @error('school_id') input-error @enderror">
            <option value="">-- Pilih Sekolah --</option>
            @foreach($schools as $sc)<option value="{{ $sc->id }}">{{ $sc->name }}</option>@endforeach
          </select>
          @error('school_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="form-label">Departemen <span class="text-red-500">*</span></label>
          <select wire:model="department_id" class="input @error('department_id') input-error @enderror" {{ empty($school_id) ? 'disabled' : '' }}>
            <option value="">{{ empty($school_id) ? '-- Pilih sekolah dulu --' : '-- Pilih Departemen --' }}</option>
            @foreach($modalDepts as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
          </select>
          @error('department_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="form-label">Nama Jabatan <span class="text-red-500">*</span></label>
          <input wire:model="name" type="text" class="input @error('name') input-error @enderror" placeholder="Nama jabatan">
          @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Level</label>
            <select wire:model="level" class="input">
              @foreach(['1'=>'1 — Staf','2'=>'2 — Senior','3'=>'3 — Supervisor','4'=>'4 — Manager','5'=>'5 — Pimpinan'] as $v=>$l)
                <option value="{{ $v }}">{{ $l }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="form-label">Status</label>
            <select wire:model="is_active" class="input"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
          </div>
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
        <h3>Hapus Jabatan?</h3>
        <button wire:click="$set('showDeleteModal',false)" class="text-white/70 hover:text-white"><x-icons.x-mark /></button>
      </div>
      <div class="modal-body text-center">
        <p class="text-sm text-gray-600">Pegawai yang memegang jabatan ini akan terpengaruh.</p>
      </div>
      <div class="modal-footer">
        <button wire:click="$set('showDeleteModal',false)" class="btn-ghost">Batal</button>
        <button wire:click="delete" class="btn-danger">Ya, Hapus</button>
      </div>
    </div>
  </div>
  @endif
</div>
