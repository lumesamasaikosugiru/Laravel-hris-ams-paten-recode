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
    public string $search     = '';
    public string $roleFilter = '';

    // Modal tambah/edit
    public bool $showModal    = false;
    public bool $isEdit       = false;
    public ?int  $editingId   = null;

    // Form fields
    public string     $name        = '';
    public string     $email       = '';
    public string     $password    = '';
    public string     $role        = '';
    public int|string $employee_id = '';
    public bool       $is_active   = true;

    // Modal reset password
    public bool   $showPasswordModal = false;
    public ?int   $passwordUserId    = null;
    public string $newPassword       = '';
    public string $passwordName      = '';

    // Modal link employee
    public bool   $showLinkModal    = false;
    public ?int   $linkUserId       = null;
    public string $linkUserName     = '';
    public int|string $linkEmployeeId = '';

    // Modal nonaktifkan
    public bool   $showToggleModal  = false;
    public ?int   $toggleUserId     = null;
    public string $toggleUserName   = '';
    public bool   $toggleIsActive   = true;

    protected function rules(): array
    {
        $emailRule = $this->isEdit
            ? "required|email|unique:users,email,{$this->editingId}"
            : 'required|email|unique:users,email';

        return [
            'name'        => 'required|string|max:255',
            'email'       => $emailRule,
            'password'    => $this->isEdit ? 'nullable|min:8' : 'required|min:8',
            'role'        => 'required|exists:roles,name',
            'employee_id' => 'nullable|exists:employees,id',
        ];
    }

    protected $messages = [
        'name.required'     => 'Nama wajib diisi.',
        'email.required'    => 'Email wajib diisi.',
        'email.unique'      => 'Email sudah digunakan.',
        'password.required' => 'Password wajib diisi.',
        'password.min'      => 'Password minimal 8 karakter.',
        'role.required'     => 'Role wajib dipilih.',
    ];

    // ── Open Modal Tambah ─────────────────────────────────────
    public function openCreate(): void
    {
        $this->reset(['name','email','password','role','employee_id','editingId']);
        $this->isEdit     = false;
        $this->is_active  = true;
        $this->resetValidation();
        $this->showModal  = true;
    }

    // ── Open Modal Edit ───────────────────────────────────────
    public function openEdit(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);
        $this->editingId   = $id;
        $this->isEdit      = true;
        $this->name        = $user->name;
        $this->email       = $user->email;
        $this->password    = '';
        $this->role        = $user->roles->first()?->name ?? '';
        $this->employee_id = $user->employee_id ?? '';
        $this->is_active   = $user->is_active ?? true;
        $this->resetValidation();
        $this->showModal   = true;
    }

    // ── Simpan User ───────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $data = [
                'name'        => $this->name,
                'email'       => $this->email,
                'employee_id' => $this->employee_id ?: null,
            ];

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            if ($this->isEdit) {
                $user = User::findOrFail($this->editingId);
                $user->update($data);
                $user->syncRoles([$this->role]);

                // Update employee link
                if ($this->employee_id) {
                    Employee::where('user_id', $user->id)
                        ->where('id', '!=', $this->employee_id)
                        ->update(['user_id' => null]);
                    Employee::find($this->employee_id)?->update(['user_id' => $user->id]);
                }

                session()->flash('success', "Akun {$this->name} berhasil diperbarui.");
            } else {
                $data['password']           = Hash::make($this->password);
                $data['email_verified_at']  = now();
                $user = User::create($data);
                $user->assignRole($this->role);

                if ($this->employee_id) {
                    Employee::find($this->employee_id)?->update(['user_id' => $user->id]);
                }

                session()->flash('success', "Akun {$this->name} berhasil dibuat.");
            }
        });

        $this->showModal = false;
    }

    // ── Reset Password ────────────────────────────────────────
    public function openPasswordModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->passwordUserId = $id;
        $this->passwordName   = $user->name;
        $this->newPassword    = '';
        $this->resetValidation();
        $this->showPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->validate(['newPassword' => 'required|min:8'], [
            'newPassword.required' => 'Password baru wajib diisi.',
            'newPassword.min'      => 'Password minimal 8 karakter.',
        ]);

        User::findOrFail($this->passwordUserId)
            ->update(['password' => Hash::make($this->newPassword)]);

        session()->flash('success', "Password {$this->passwordName} berhasil direset.");
        $this->showPasswordModal = false;
    }

    // ── Link ke Pegawai ───────────────────────────────────────
    public function openLinkModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->linkUserId     = $id;
        $this->linkUserName   = $user->name;
        $this->linkEmployeeId = $user->employee_id ?? '';
        $this->resetValidation();
        $this->showLinkModal  = true;
    }

    public function saveLink(): void
    {
        $this->validate(['linkEmployeeId' => 'required|exists:employees,id'], [
            'linkEmployeeId.required' => 'Pilih pegawai.',
        ]);

        DB::transaction(function () {
            $user = User::findOrFail($this->linkUserId);

            // Lepas link lama
            Employee::where('user_id', $user->id)->update(['user_id' => null]);

            // Set link baru
            Employee::find($this->linkEmployeeId)?->update(['user_id' => $user->id]);
            $user->update(['employee_id' => $this->linkEmployeeId]);
        });

        session()->flash('success', "{$this->linkUserName} berhasil dihubungkan ke data pegawai.");
        $this->showLinkModal = false;
    }

    // ── Toggle Aktif/Nonaktif ─────────────────────────────────
    public function openToggleModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->toggleUserId   = $id;
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
        $users = User::with(['roles','employee.school'])
            ->when($this->search, fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->roleFilter, fn($q) => $q
                ->whereHas('roles', fn($r) => $r->where('name', $this->roleFilter)))
            ->orderBy('name')
            ->paginate(15);

        $roles     = Role::orderBy('name')->pluck('name');
        $employees = Employee::whereIn('status', ['active','probation'])
            ->orderBy('name')
            ->get(['id','name','nipy','nik']);

        return view('livewire.admin.user-management',
            compact('users','roles','employees'));
    }
}
