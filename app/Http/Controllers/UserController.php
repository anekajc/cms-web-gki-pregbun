<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'generated_password', 'created_at'])
            ->makeVisible('generated_password');

        return Inertia::render('user', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return Inertia::render('user/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'user'])],
        ]);

        $password = $this->generatePassword();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $password,            // hashed via cast (login credential)
            'generated_password' => $password,  // encrypted via cast (admin can re-view/send)
            'must_change_password' => true,     // forced to set their own on first login
            'email_verified_at' => now(),       // admin-provisioned, no verification email
        ]);

        return redirect()->route('user')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function regeneratePassword(User $user)
    {
        $password = $this->generatePassword();

        $user->update([
            'password' => $password,
            'generated_password' => $password,
            'must_change_password' => true,
        ]);

        return back()->with('success', 'Password baru berhasil dibuat.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $user->delete();

        return redirect()->route('user')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function editAccess(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('user')->withErrors(['access' => 'Admin memiliki akses penuh.']);
        }

        $tree = Access::tree();

        return Inertia::render('user/access', [
            'user' => $user->only(['id', 'name', 'email']),
            'tree' => $tree,
            // Drop keys for rows that no longer exist so saving never fails validation.
            'granted' => array_values(array_intersect($user->permissions()->pluck('permission')->all(), Access::allKeys())),
        ]);
    }

    public function updateAccess(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('user')->withErrors(['access' => 'Admin memiliki akses penuh.']);
        }

        $validated = $request->validate([
            'permissions' => 'present|array',
            'permissions.*' => ['string', Rule::in(Access::allKeys())],
        ]);

        $keys = array_values(array_unique($validated['permissions']));

        DB::transaction(function () use ($user, $keys) {
            $user->permissions()->whereNotIn('permission', $keys)->delete();

            $existing = $user->permissions()->pluck('permission')->all();

            foreach (array_diff($keys, $existing) as $key) {
                $user->permissions()->create(['permission' => $key]);
            }
        });

        return redirect()->route('user')->with('success', 'Akses pengguna berhasil diperbarui.');
    }

    /**
     * 8-char alphanumeric password excluding look-alike characters (0/O, 1/l/I)
     * so it's easy for the admin to read and pass on.
     */
    private function generatePassword(int $length = 8): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $max = strlen($alphabet) - 1;

        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $max)];
        }

        return $password;
    }
}
