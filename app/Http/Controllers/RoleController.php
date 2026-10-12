<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Rôles système protégés ne pouvant être supprimés.
     */
    protected const PROTECTED_ROLES = [
        'Super Admin',
        'Directeur',
        'Enseignant',
        'Parent',
        'Élève',
    ];

    /**
     * Display a listing of the roles with their user and permission counts.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $roles = Role::withCount(['users', 'permissions'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('display_name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        $totalRolesCount = Role::count();
        $totalPermissionsCount = Permission::count();
        $totalAssignedUsersCount = User::has('roles')->count();

        return view('roles.index', compact(
            'roles',
            'search',
            'totalRolesCount',
            'totalPermissionsCount',
            'totalAssignedUsersCount'
        ));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $permissionsByGroup = Permission::orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        return view('roles.create', compact('permissionsByGroup'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:125', 'unique:roles,name'],
            'display_name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('roles.index')
            ->with('status', "Le rôle « {$role->display_name} » a été créé avec succès.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');

        $permissionsByGroup = Permission::orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $isProtected = in_array($role->name, self::PROTECTED_ROLES, true);

        return view('roles.edit', compact('role', 'permissionsByGroup', 'isProtected'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $isProtected = in_array($role->name, self::PROTECTED_ROLES, true);

        $rules = [
            'display_name' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];

        // Only allow renaming role code if it's not a protected system role
        if (! $isProtected) {
            $rules['name'] = ['required', 'string', 'max:125', 'unique:roles,name,'.$role->id];
        }

        $validated = $request->validate($rules);

        $updateData = [
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ];

        if (! $isProtected && isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }

        $role->update($updateData);

        // Super Admin keeps all permissions, for other roles sync the selected ones
        if ($role->name === 'Super Admin') {
            $allPermIds = Permission::pluck('id')->toArray();
            $role->permissions()->sync($allPermIds);
        } else {
            $role->permissions()->sync($validated['permissions'] ?? []);
        }

        return redirect()->route('roles.index')
            ->with('status', "Le rôle « {$role->display_name} » et ses habilitations ont été mis à jour.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return redirect()->back()->withErrors([
                'error' => "Le rôle système « {$role->name} » est protégé et ne peut pas être supprimé.",
            ]);
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => "Ce rôle est actuellement attribué à {$role->users()->count()} utilisateur(s). Réassignez-les avant de pouvoir supprimer ce rôle.",
            ]);
        }

        $roleName = $role->display_name ?? $role->name;
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')
            ->with('status', "Le rôle « {$roleName} » a été supprimé.");
    }
}
