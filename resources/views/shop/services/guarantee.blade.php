@extends('shop.layouts.app')
@section('title', 'Возврат и обмен')

@section('content')
<section class="page-header-gradient">
    <div class="container position-relative">
        <div class="service-page-badge mb-3">
            <img src="{{ asset('images/icon-guarantee.svg') }}" alt="Возврат и обмен" width="36" height="36">
        </div>
        <h1 class="display-6 fw-bold mb-2">Возврат и обмен</h1>
        <p class="lead mb-0 opacity-90">Лёгкий возврат в течение 14 дней</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <p class="lead fw-medium" style="color: var(--primary-dark);">Покупая в ModaStyle, вы можете спокойно примерить вещи дома и вернуть то, что не подошло.</p>
                <p class="text-muted">Мы работаем с официальными поставщиками брендов. Каждая вещь проходит контроль качества перед отправкой: бирки, фурнитура, швы и соответствие размерной сетке.</p>

                <h3 class="h5 fw-bold mt-4 mb-3" style="color: var(--primary-dark);">Что гарантируем</h3>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="value-card h-100">
                            <div class="benefit-icon benefit-icon--sm mb-2">
                                <img src="{{ asset('images/icon-guarantee.svg') }}" alt="" width="28" height="28">
                            </div>
                            <h4 class="h6 fw-bold">Оригинальные бренды</h4>
                            <p class="text-muted small mb-0">Только авторизованные поставки, без реплик.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="value-card h-100">
                            <div class="benefit-icon benefit-icon--sm mb-2"><i class="fas fa-file-contract"></i></div>
                            <h4 class="h6 fw-bold">Документы</h4>
                            <p class="text-muted small mb-0">Чек и накладная в каждом заказе.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="value-card h-100">
                            <div class="benefit-icon benefit-icon--sm mb-2"><i class="fas fa-ruler"></i></div>
                            <h4 class="h6 fw-bold">Размерная сетка</h4>
                            <p class="text-muted small mb-0">Подробные таблицы размеров на карточке товара.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="value-card h-100">
                            <div class="benefit-icon benefit-icon--sm mb-2"><i class="fas fa-rotate-left"></i></div>
                            <h4 class="h6 fw-bold">Возврат 14 дней</h4>
                            <p class="text-muted small mb-0">Обмен или возврат при сохранении бирок и ярлыков.</p>
                        </div>
                    </div>
                </div>

                <h3 class="h5 fw-bold mt-4 mb-3" style="color: var(--primary-dark);">Как оформить возврат</h3>
                <p class="text-muted mb-0">Свяжитесь с поддержкой через чат, по телефону +7 937 953 54 80 или на странице «Контакты». Мы подскажем ближайший пункт приёма или заберём курьером.</p>
            </div>
            <div class="col-lg-5">
                @include('shop.services.partials.sidebar', [
                    'highlights' => [
                        'Возврат в течение 14 дней',
                        'Сохранение бирок обязательно',
                        'Быстрый возврат денег',
                        'Помощь стилиста по размеру',
                    ],
                ])
            </div>
        </div>
    </div>
</section>
@endsection
