<?php
namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserManagement extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $roleFilter = '';

    // Modal tambah/edit
    public bool $showModal = false;
    public bool $isEdit = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public int|string $employee_id = '';
    public bool $is_active = true;

    // Modal reset password
    public bool $showPasswordModal = false;
    public ?int $passwordUserId = null;
    public string $newPassword = '';
    public string $passwordName = '';

    // Modal link employee
    public bool $showLinkModal = false;
    public ?int $linkUserId = null;
    public string $linkUserName = '';
    public int|string $linkEmployeeId = '';
    public ?string $linkCurrentEmployeeName = null; // nama pegawai yg SUDAH terhubung sebelumnya (untuk peringatan)

    // Modal nonaktifkan
    public bool $showToggleModal = false;
    public ?int $toggleUserId = null;
    public string $toggleUserName = '';
    public bool $toggleIsActive = true;

    protected function rules(): array
    {
        $emailRule = $this->isEdit
            ? "required|email|unique:users,email,{$this->editingId}"
            : 'required|email|unique:users,email';

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $this->isEdit ? 'nullable|min:8' : 'required|min:8',
            'role' => 'required|exists:roles,name',
            'employee_id' => 'nullable|exists:employees,id',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.unique' => 'Email sudah digunakan.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 8 karakter.',
        'role.required' => 'Role wajib dipilih.',
    ];

    // ── Open Modal Tambah ─────────────────────────────────────
    public function openCreate(): void
    {
        $this->reset(['name', 'email', 'password', 'role', 'employee_id', 'editingId']);
        $this->isEdit = false;
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    // ── Open Modal Edit ───────────────────────────────────────
    public function openEdit(int $id): void
    {
        $user = User::with(['roles', 'employee'])->findOrFail($id);
        $this->editingId = $id;
        $this->isEdit = true;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles->first()?->name ?? '';
        // employee_id diambil dari relasi (employees.user_id), bukan kolom users.employee_id
        $this->employee_id = $user->employee?->id ?? '';
        $this->is_active = $user->is_active ?? true;
        $this->resetValidation();
        $this->showModal = true;
    }

    // ── Simpan User ───────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            // employee_id BUKAN kolom di tabel users — jangan ikut disimpan ke User
            $data = [
                'name' => $this->name,
                'email' => $this->email,
            ];

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            if ($this->isEdit) {
                $user = User::findOrFail($this->editingId);
                $user->update($data);
                $user->syncRoles([$this->role]);

                $this->syncEmployeeLink($user);

                session()->flash('success', "Akun {$this->name} berhasil diperbarui.");
            } else {
                $data['password'] = Hash::make($this->password);
                $data['email_verified_at'] = now();
                $user = User::create($data);
                $user->assignRole($this->role);

                $this->syncEmployeeLink($user);

                session()->flash('success', "Akun {$this->name} berhasil dibuat.");
            }
        });

        $this->showModal = false;
    }

    /**
     * Pastikan hanya SATU employee yang terhubung ke user ini,
     * dan lepas link lama jika employee_id diubah/dikosongkan.
     */
    private function syncEmployeeLink(User $user): void
    {
        // Lepas semua link employee lama milik user ini dulu
        Employee::where('user_id', $user->id)->update(['user_id' => null]);

        // Pasang link baru jika dipilih
        if ($this->employee_id) {
            Employee::find($this->employee_id)?->update(['user_id' => $user->id]);
        }
    }

    // ── Reset Password ────────────────────────────────────────
    public function openPasswordModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->passwordUserId = $id;
        $this->passwordName = $user->name;
        $this->newPassword = '';
        $this->resetValidation();
        $this->showPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->validate(['newPassword' => 'required|min:8'], [
            'newPassword.required' => 'Password baru wajib diisi.',
            'newPassword.min' => 'Password minimal 8 karakter.',
        ]);

        User::findOrFail($this->passwordUserId)
            ->update(['password' => Hash::make($this->newPassword)]);

        session()->flash('success', "Password {$this->passwordName} berhasil direset.");
        $this->showPasswordModal = false;
    }

    // ── Link ke Pegawai ───────────────────────────────────────
    public function openLinkModal(int $id): void
    {
        $user = User::with('employee')->findOrFail($id);
        $this->linkUserId = $id;
        $this->linkUserName = $user->name;
        // Ambil dari relasi (employees.user_id), bukan dari kolom users.employee_id
        $this->linkEmployeeId = $user->employee?->id ?? '';
        $this->linkCurrentEmployeeName = $user->employee?->name;
        $this->resetValidation();
        $this->showLinkModal = true;
    }

    public function saveLink(): void
    {
        $this->validate(['linkEmployeeId' => 'required|exists:employees,id'], [
            'linkEmployeeId.required' => 'Pilih pegawai.',
        ]);

        DB::transaction(function () {
            $user = User::findOrFail($this->linkUserId);

            // Lepas link lama milik user ini
            Employee::where('user_id', $user->id)->update(['user_id' => null]);

            // Lepas juga jika pegawai yang dipilih sudah terhubung ke user lain
            // (mencegah satu pegawai punya >1 akun aktif tanpa sadar)
            Employee::where('id', $this->linkEmployeeId)
                ->where('user_id', '!=', $user->id)
                ->update(['user_id' => null]);

            // Set link baru — HANYA di tabel employees, tabel users tidak disentuh
            Employee::find($this->linkEmployeeId)?->update(['user_id' => $user->id]);
        });

        session()->flash('success', "{$this->linkUserName} berhasil dihubungkan ke data pegawai.");
        $this->showLinkModal = false;
        $this->linkCurrentEmployeeName = null;
    }

    // ── Toggle Aktif/Nonaktif ─────────────────────────────────
    public function openToggleModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->toggleUserId = $id;
        $this->toggleUserName = $user->name;
        $this->toggleIsActive = $user->is_active ?? true;
        $this->showToggleModal = true;
    }

    public function toggleActive(): void
    {
        $user = User::findOrFail($this->toggleUserId);
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        session()->flash('success', "Akun {$this->toggleUserName} berhasil {$status}.");
        $this->showToggleModal = false;
    }

    public function render()
    {
        $users = User::with(['roles', 'employee.school'])
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn($q) => $q
                ->whereHas('roles', fn($r) => $r->where('name', $this->roleFilter)))
            ->orderBy('name')
            ->paginate(15);

        $roles = Role::orderBy('name')->pluck('name');
        $employees = Employee::whereIn('status', ['active', 'probation'])
            ->orderBy('name')
            ->get(['id', 'name', 'nipy', 'nik'])
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'code' => $e->nipy ?? $e->nik ?? '-',
            ]);

        return view(
            'livewire.admin.user-management',
            compact('users', 'roles', 'employees')
        );
    }
}