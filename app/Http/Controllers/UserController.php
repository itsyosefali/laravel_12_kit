<?php
namespace App\Http\Controllers;
use Illuminate\Support\Arr;

use Inertia\Inertia;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/index', [
            'users' => User::with(['roles' => function ($query) {
                $query->select('name'); 
            }])->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'created_at' => $user->created_at->toDateTimeString(),
                    'updated_at' => $user->updated_at->toDateTimeString(),
                ];
            }),
            'roles' => Role::all(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $user = User::create(Arr::except($validated, ['roles']));
        $roles = Role::whereIn('id', $validated['roles'])->pluck('name');
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $roles = Role::whereIn('id', $validated['roles'])->pluck('name');
        $user->syncRoles($roles);

        $data = Arr::except($validated, ['roles']);
        $user->update($data);

        return back()->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted successfully');
    }
    public function create()
    {
        return Inertia::render('Users/create', [
            'roles' => Role::all(['id', 'name'])
        ]);
    }
}