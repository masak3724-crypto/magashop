@extends('shop.layouts.app')
@section('title', 'О нас')

@section('content')
<section class="page-header-gradient">
    <div class="container position-relative">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-8 text-center">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="ModaStyle" height="40" class="mb-3 mx-auto d-block">
                <h1 class="display-6 fw-bold mb-2 hero-editorial">О ModaStyle</h1>
                <p class="lead mb-0 opacity-90">Интернет-магазин модной одежды для тех, кто ценит стиль и комфорт</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <p class="lead fw-medium" style="color: var(--primary-dark);">С 2018 года помогаем собирать гардероб мечты — от базовых вещей до трендовых коллекций.</p>
                <p class="text-muted">Вдохновляемся лучшими практиками Lamoda, Love Republic и Gloria Jeans: чистый дизайн, актуальные коллекции, доставка с примеркой и заботливый сервис.</p>
                <div class="d-flex flex-wrap gap-2 mt-4 justify-content-center">
                    <a href="{{ route('products') }}" class="btn btn-glow rounded-0 px-4">Каталог</a>
                    <a href="{{ route('contacts') }}" class="btn btn-outline-dark rounded-0 px-4">Контакты</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background: var(--light);">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title" style="color: var(--primary-dark);">Наши ценности</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card">
                    <div class="benefit-icon"><i class="fas fa-heart"></i></div>
                    <h4 class="fw-bold">Стиль</h4>
                    <p class="text-muted mb-0">Только проверенные бренды и трендовые модели сезона.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="benefit-icon benefit-icon--blue"><i class="fas fa-headset"></i></div>
                    <h4 class="fw-bold">Сервис</h4>
                    <p class="text-muted mb-0">Стилисты и поддержка помогут с размером и образом.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card">
                    <div class="benefit-icon benefit-icon--green"><i class="fas fa-tag"></i></div>
                    <h4 class="fw-bold">Выгода</h4>
                    <p class="text-muted mb-0">Акции, распродажи до −60% и скидка при оплате онлайн.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
