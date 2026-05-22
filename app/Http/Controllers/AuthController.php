<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('shop.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $login = $credentials['email'];
        $password = $credentials['password'];

        $user = str_contains($login, '@')
            ? User::where('email', $login)->first()
            : User::where('name', $login)->orWhere('email', $login)->first();

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('index'));
        }

        return back()->withErrors([
            'email' => 'Неверный логин или пароль.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('shop.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:users,name'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Необходимо принять условия публичной оферты и политику конфиденциальности.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Profile::create(['user_id' => $user->id]);

        Auth::login($user);

        return redirect()->route('index')->with('success', 'Регистрация прошла успешно!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('index');
    }
}
