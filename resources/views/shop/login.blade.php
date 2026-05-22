@extends('shop.layouts.app')
@section('title', 'Вход')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header text-white py-3" style="background: var(--primary);">
                    <h3 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Вход</h3>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Логин или email</label>
                            <input type="text" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Запомнить меня</label>
                        </div>
                        <button type="submit" class="btn btn-accent w-100 mb-2">Войти</button>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Регистрация</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
