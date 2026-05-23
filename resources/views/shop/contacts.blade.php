@extends('shop.layouts.app')
@section('title', 'Контакты')
@section('content')
<section class="text-white text-center py-5" style="background: var(--primary);">
    <div class="container">
        <h1 class="display-5"><i class="fas fa-envelope me-2"></i>Контакты</h1>
        <p class="lead">Вопросы по размеру, заказу и доставке</p>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm p-4">
                    <h4><i class="fas fa-map-marker-alt me-2"></i>Адрес</h4>
                    <p class="mb-0">г. Чебоксары, ул. Декабристов, 17А</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm p-4">
                    <h4><i class="fas fa-phone me-2"></i>Связь</h4>
                    <p>Тел.: <a href="tel:+79379535480">+7 937 953 54 80</a><br>Email: info@modastyle.ru<br>
                    <a href="https://max.ru" target="_blank" rel="noopener" class="text-decoration-none d-inline-flex align-items-center gap-2 mt-2">
                        @include('shop.partials.icon-messenger-max') Мессенджер MAX
                    </a></p>
                    <p class="mb-0"><strong>Часы работы:</strong> Пн–Вс 9:00–20:00</p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm p-4">
                    <h2 class="text-center mb-4">Напишите нам</h2>
                    <form method="POST" action="{{ route('contacts.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Имя</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Телефон</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Сообщение</label>
                                <textarea name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-accent px-5">Отправить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
