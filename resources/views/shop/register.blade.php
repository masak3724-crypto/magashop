@extends('shop.layouts.app')
@section('title', 'Регистрация')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header text-white py-3" style="background: var(--primary);">
                    <h3 class="mb-0"><i class="fas fa-user-plus me-2"></i>Регистрация</h3>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Имя пользователя</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Подтверждение пароля</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="terms" value="1" class="form-check-input" id="terms" {{ old('terms') ? 'checked' : '' }} required>
                                <label class="form-check-label small" for="terms">
                                    Я принимаю условия
                                    <a href="{{ route('offer') }}" target="_blank" rel="noopener noreferrer">публичной оферты</a>
                                    и даю согласие на обработку персональных данных в соответствии с
                                    <a href="{{ route('privacy') }}" target="_blank" rel="noopener noreferrer">политикой конфиденциальности</a>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-accent w-100">Зарегистрироваться</button>
                    </form>
                    <p class="text-center mt-3 mb-0">Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
