<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    public function index()
    {
        return Inertia::render('Roles/index', [
            'roles' => Role::with('permissions')->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(fn($perm): array => [
                        'id' => $perm->id,
                        'name' => $perm->name,
                    ]),
                    'created_at' => $role->created_at->toDateTimeString(),
                ];
            }),
            'permissions' => Permission::all()->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        return to_route('roles.index');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->back()->with('success', 'Role deleted successfully');
    }
    public function create()
    {
        return Inertia::render('Roles/create', [
            'permissions' => Permission::all()->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name
            ])
        ]);
    }
}
