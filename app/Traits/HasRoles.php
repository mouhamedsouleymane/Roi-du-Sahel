<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasRoles
{
    public function roles(): MorphToMany
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles');
    }

    public function permissions(): MorphToMany
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions');
    }

    public function assignRole(string|Role ...$roles): static
    {
        foreach ($roles as $role) {
            $roleObj = is_string($role) ? Role::firstOrCreate(['name' => $role]) : $role;
            $this->roles()->syncWithoutDetaching([$roleObj->id]);
        }
        return $this;
    }

    public function removeRole(string|Role $role): static
    {
        $roleObj = is_string($role) ? Role::where('name', $role)->first() : $role;
        if ($roleObj) {
            $this->roles()->detach($roleObj->id);
        }
        return $this;
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if (is_array($roles)) {
            return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
        }

        return false;
    }

    public function hasPermissionTo(string $permission): bool
    {
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        if ($this->permissions->contains('name', $permission)) {
            return true;
        }

        return $this->roles->flatMap->permissions->contains('name', $permission);
    }
}
