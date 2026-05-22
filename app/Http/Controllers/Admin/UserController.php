<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('profile')->withCount('orders');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $promotableUsers = User::where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.users.index', compact('users', 'promotableUsers'));
    }

    public function show(User $user)
    {
        $user->load(['profile', 'orders' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.users.show', compact('user'));
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['create', 'promote'])],
            'user_id' => ['required_if:mode,promote', 'nullable', 'exists:users,id'],
            'name' => ['required_if:mode,create', 'nullable', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required_if:mode,create', 'nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required_if:mode,create', 'nullable', 'confirmed', Password::min(8)],
        ]);

        if ($data['mode'] === 'promote') {
            $user = User::findOrFail($data['user_id']);

            if ($user->is_admin) {
                return back()->with('error', 'Этот пользователь уже является администратором.');
            }

            $user->update(['is_admin' => true]);

            return redirect()
                ->route('admin.users.show', $user)
                ->with('success', "Пользователю «{$user->name}» назначены права администратора.");
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => true,
        ]);

        Profile::create(['user_id' => $user->id]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "Администратор «{$user->name}» успешно создан.");
    }
}
