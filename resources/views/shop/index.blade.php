@extends('shop.layouts.app')

@section('title', 'Главная')

@section('content')
<section class="hero-section hero-section--fashion text-white py-5">
    <div class="container py-5 hero-content">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-10 col-xl-9 text-center">
                <span class="hero-badge mb-3">
                    <i class="fas fa-sparkles"></i> Новая коллекция весна–лето
                </span>
                <h1 class="display-4 fw-bold mt-2 mb-3 hero-editorial">
                    Мода, которая<br><span class="hero-highlight">вдохновляет</span>
                </h1>
                <p class="lead mb-4 opacity-90">Одежда, обувь и аксессуары от российских и мировых брендов. Доставка с примеркой, лёгкий возврат и стильные новинки каждую неделю.</p>
                <div class="d-flex flex-wrap gap-3 mb-0 justify-content-center">
                    <a href="{{ route('products') }}" class="btn btn-lg btn-glow px-4 rounded-0">
                        <i class="fas fa-shopping-bag me-2"></i>В каталог
                    </a>
                    <a href="#categories" class="btn btn-lg btn-outline-light px-4 rounded-0">Категории</a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong>2000+</strong>
                        <span>моделей в каталоге</span>
                    </div>
                    <div class="hero-stat">
                        <strong>Примерка</strong>
                        <span>перед покупкой</span>
                    </div>
                    <div class="hero-stat">
                        <strong>−60%</strong>
                        <span>на распродаже</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="categories" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title" style="color: var(--primary-dark);">Категории</h2>
            <p class="section-subtitle">Соберите образ на любой случай — от повседневного до вечернего</p>
        </div>
        <div class="row">
            @php
                $categoryIcons = [
                    'odezhda' => 'icon-clothing.svg',
                    'muzhchinam' => 'icon-clothing.svg',
                    'obuv' => 'icon-shoes.svg',
                    'aksessuary' => 'icon-accessories.svg',
                ];
            @endphp
            @foreach($categories as $category)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card category-card h-100 text-center">
                    <div class="card-body py-5 px-4">
                        <div class="category-icon-wrap">
                            <img src="{{ asset('images/' . ($categoryIcons[$category->slug] ?? 'clothing-placeholder.svg')) }}" alt="{{ $category->name }}">
                        </div>
                        <h5 class="card-title fw-bold" style="color: var(--primary-dark);">{{ $category->name }}</h5>
                        <p class="text-muted small mb-3">{{ $category->products_count }} позиций в каталоге</p>
                        <a href="{{ route('products', ['category' => $category->slug]) }}" class="btn btn-primary-custom rounded-0 px-4">Смотреть</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5 why-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title" style="color: var(--primary-dark);">Почему ModaStyle</h2>
            <p class="section-subtitle">Стильный шопинг без рисков — как в лучших fashion-магазинах</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="benefit-item text-center p-4 h-100">
                    <div class="benefit-icon"><i class="fas fa-gem"></i></div>
                    <h4 class="fw-bold" style="color: var(--primary-dark);">Актуальные тренды</h4>
                    <p class="mb-0 text-muted">Новинки каждую неделю — платья, деним, верхняя одежда и аксессуары.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-item text-center p-4 h-100">
                    <div class="benefit-icon benefit-icon--blue"><i class="fas fa-truck-fast"></i></div>
                    <h4 class="fw-bold" style="color: var(--primary-dark);">Доставка с примеркой</h4>
                    <p class="mb-0 text-muted">Примерьте дома и оплатите только то, что подошло.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="benefit-item text-center p-4 h-100">
                    <div class="benefit-icon benefit-icon--green"><i class="fas fa-rotate-left"></i></div>
                    <h4 class="fw-bold" style="color: var(--primary-dark);">Лёгкий возврат</h4>
                    <p class="mb-0 text-muted">Обмен и возврат в течение 14 дней — без лишней бюрократии.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
