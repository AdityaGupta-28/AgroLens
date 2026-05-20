<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';

    // Form fields
    public $userId;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 'government_officer';
    public $is_active = true;

    public $confirmingDeletion = null;
    public $showForm = false;
    public $isEdit = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        if (! auth()->user()?->hasPermission(\App\Enums\Permission::ManageUsers)) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $this->resetForm();
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role instanceof UserRole ? $user->role->value : $user->role;
        $this->is_active = (bool) $user->is_active;
        $this->isEdit = true;
        $this->showForm = true;
    }

    public function resetForm(): void
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'government_officer';
        $this->is_active = true;
        $this->showForm = false;
        $this->isEdit = false;
        $this->confirmingDeletion = null;
        $this->resetValidation();
    }

    public function saveUser(): void
    {
        if (! auth()->user()?->hasPermission(\App\Enums\Permission::ManageUsers)) {
            abort(403);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'role' => ['required', Rule::in(array_map(fn($c) => $c->value, UserRole::cases()))],
            'is_active' => ['boolean'],
        ];

        if (! $this->isEdit) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        $validated = $this->validate($rules);

        if ($this->isEdit) {
            $user = User::findOrFail($this->userId);
            
            // Prevent self-deactivation or self-role change if editing own account
            if ($user->id === auth()->id()) {
                $validated['is_active'] = true;
                $validated['role'] = UserRole::SuperAdmin->value;
            }

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = UserRole::from($validated['role']);
            $user->is_active = $validated['is_active'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();
            $user->clearPermissionCache();

            session()->flash('success', 'User updated successfully.');
        } else {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => UserRole::from($validated['role']),
                'is_active' => $validated['is_active'],
                'password' => Hash::make($validated['password']),
            ]);

            session()->flash('success', 'User registered successfully.');
        }

        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        if (! auth()->user()?->hasPermission(\App\Enums\Permission::ManageUsers)) {
            abort(403);
        }

        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return; // Can't deactivate yourself
        }

        $user->is_active = ! $user->is_active;
        $user->save();
        $user->clearPermissionCache();

        session()->flash('success', 'User status updated.');
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeletion = $id;
    }

    public function deleteUser(): void
    {
        if (! auth()->user()?->hasPermission(\App\Enums\Permission::ManageUsers)) {
            abort(403);
        }

        $user = User::findOrFail($this->confirmingDeletion);

        if ($user->id === auth()->id()) {
            $this->confirmingDeletion = null;
            return; // Can't delete yourself
        }

        $user->delete();
        $this->confirmingDeletion = null;

        session()->flash('success', 'User deleted successfully.');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, function ($q) {
                $q->where('role', $this->roleFilter);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }
}
