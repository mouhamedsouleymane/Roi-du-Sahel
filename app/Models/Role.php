<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_roles');
    }

    public function givePermissionTo(string|Permission $permission): static
    {
        $perm = is_string($permission)
            ? Permission::firstOrCreate(['name' => $permission, 'guard_name' => $this->guard_name])
            : $permission;

        $this->permissions()->syncWithoutDetaching([$perm->id]);
        return $this;
    }
}
