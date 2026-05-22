<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ModaStyle') — интернет-магазин одежды</title>
    <meta name="description" content="ModaStyle — интернет-магазин модной одежды, обуви и аксессуаров. Новинки, тренды и доставка с примеркой по России.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>
<nav class="gender-nav d-none d-md-block">
    <div class="container d-flex justify-content-center">
        <a href="{{ route('products', ['category' => 'muzhchinam']) }}" class="gender-nav__link {{ $activeCategory === 'muzhchinam' ? 'active' : '' }}">Мужчинам</a>
        <a href="{{ route('products', ['category' => 'odezhda']) }}" class="gender-nav__link {{ $activeCategory === 'odezhda' ? 'active' : '' }}">Женщинам</a>
        <a href="{{ route('products', ['category' => 'obuv']) }}" class="gender-nav__link {{ $activeCategory === 'obuv' ? 'active' : '' }}">Обувь</a>
        <a href="{{ route('products', ['category' => 'aksessuary']) }}" class="gender-nav__link {{ $activeCategory === 'aksessuary' ? 'active' : '' }}">Аксессуары</a>
        <a href="{{ route('products') }}" class="gender-nav__link {{ $activeCategory === 'all' ? 'active' : '' }}">Новинки</a>
        <a href="{{ route('products', ['sale' => 1]) }}" class="gender-nav__link {{ $activeCategory === 'sale' ? 'active' : '' }}">Распродажа</a>
    </div>
</nav>

<nav class="navbar navbar-expand-lg navbar-light site-navbar sticky-top">
    <div class="container">
        @include('shop.partials.logo')
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto ms-lg-4">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products', 'product.show') ? 'active' : '' }}" href="{{ route('products') }}">Каталог</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">О нас</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contacts') ? 'active' : '' }}" href="{{ route('contacts') }}">Контакты</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                @auth
                <a href="{{ route('cart') }}" class="btn btn-outline-light position-relative rounded-0 px-3">
                    <i class="fas fa-shopping-bag"></i>
                    @if($cartItemsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $cartItemsCount }}</span>
                    @endif
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle rounded-0" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> {{ auth()->user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-0">
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-id-card me-2 text-muted"></i> Профиль</a></li>
                        <li><a class="dropdown-item" href="{{ route('orders') }}"><i class="fas fa-box me-2 text-muted"></i> Мои заказы</a></li>
                        @if(auth()->user()->isAdmin())
                        <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fas fa-shield-alt me-2 text-accent"></i> Админ-панель</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">@csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> Выйти</button>
                            </form>
                        </li>
                    </ul>
                </div>
                @else
                <a href="{{ route('login') }}" class="btn btn-glow rounded-0 px-4">Войти</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@include('shop.partials.feature_strip')

<main>
    @include('shop.partials.alerts')
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 footer-brand">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="ModaStyle">
                <p class="small opacity-75 mb-0">Интернет-магазин модной одежды, обуви и аксессуаров для всей семьи.</p>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="fw-bold mb-3">Покупателям</h5>
                <p class="mb-2"><a href="{{ route('services.delivery') }}" class="text-white text-decoration-none opacity-75">Доставка и примерка</a></p>
                <p class="mb-2"><a href="{{ route('services.guarantee') }}" class="text-white text-decoration-none opacity-75">Возврат и обмен</a></p>
                <p class="mb-4"><a href="{{ route('services.tire-service') }}" class="text-white text-decoration-none opacity-75">Программа лояльности</a></p>
                <h5 class="fw-bold mb-3">Контакты</h5>
                <p class="mb-2"><i class="fas fa-envelope me-2 text-accent"></i> info@modastyle.ru</p>
                <p class="mb-2"><i class="fas fa-phone me-2 text-accent"></i> <a href="tel:+79379535480" class="text-white text-decoration-none">+7 937 953 54 80</a></p>
                <p class="mb-0"><i class="fas fa-map-marker-alt me-2 text-accent"></i> г. Чебоксары, ул. Декабристов, 17А</p>
            </div>
            <div class="col-md-4 mb-4 footer-social">
                <h5 class="fw-bold mb-3">Мы в соцсетях</h5>
                <div class="social-links">
                    <a href="https://vk.ru" target="_blank" rel="noopener noreferrer" aria-label="ВКонтакте"><i class="fab fa-vk"></i></a>
                    <a href="https://max.ru" target="_blank" rel="noopener noreferrer" class="social-max" aria-label="Мессенджер MAX">
                        <img src="{{ asset('images/icon-max.svg') }}" alt="MAX">
                    </a>
                </div>
            </div>
        </div>
        <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
            <p class="mb-2 small opacity-75">
                <a href="{{ route('privacy') }}" class="footer-legal-link">Политика конфиденциальности</a>
                <span class="mx-2 opacity-50">|</span>
                <a href="{{ route('offer') }}" class="footer-legal-link">Публичная оферта</a>
            </p>
            <p class="mb-0 small opacity-75">&copy; {{ date('Y') }} ModaStyle. Все права защищены.</p>
        </div>
    </div>
</footer>

@include('shop.partials.support_widget')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="{{ asset('js/script.js') }}" defer></script>
<script src="{{ asset('js/support-chat.js') }}" defer></script>
@stack('scripts')
</body>
</html>
