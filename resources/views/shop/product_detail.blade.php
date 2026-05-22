@extends('shop.layouts.app')
@section('title', $product->name)
@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('index') }}">Главная</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products') }}">Каталог</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 15px;">
                <div class="product-photo-wrap product-photo-wrap--detail">
                    <img src="{{ $product->imageUrl() }}" class="product-photo @if($product->hasWhiteMatteBackground()) product-photo--matte @endif" alt="{{ $product->name }}" style="max-height: 400px;">
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4" style="background: var(--light);">
                    <h1 style="color: var(--primary-dark);">{{ $product->name }}</h1>
                    <span class="badge bg-secondary mb-3">{{ $product->category->name }}</span>
                    <h3 class="mb-4" style="color: var(--accent);">{{ number_format($product->price, 0, ',', ' ') }} ₽</h3>
                    <h5>Описание</h5>
                    <p>{{ $product->description }}</p>
                    @auth
                    <form action="{{ route('cart.add', $product) }}" method="POST" class="d-flex align-items-center gap-3 mt-4">
                        @csrf
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary minus-btn" type="button">-</button>
                            <input type="number" name="quantity" value="1" min="1" max="100" class="form-control text-center quantity-input">
                            <button class="btn btn-outline-secondary plus-btn" type="button">+</button>
                        </div>
                        <button type="submit" class="btn btn-accent flex-grow-1"><i class="fas fa-shopping-cart me-2"></i>В корзину</button>
                    </form>
                    @else
                    <div class="alert alert-info mt-4"><a href="{{ route('login') }}">Войдите</a>, чтобы добавить товар в корзину</div>
                    @endauth
                    <div class="mt-4 p-3 rounded" style="background: #e8ecf4;">
                        <p class="mb-1"><i class="fas fa-check-circle text-success me-2"></i>В наличии</p>
                        <p class="mb-1"><i class="fas fa-truck me-2"></i>Доставка 1–3 дня</p>
                        <p class="mb-0"><i class="fas fa-undo me-2"></i>Возврат в течение 14 дней</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($relatedProducts->isNotEmpty())
    <section class="mt-5">
        <h3 class="mb-4" style="color: var(--primary-dark);">Похожие товары</h3>
        <div class="row">
            @foreach($relatedProducts as $related)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">@include('shop.partials.product_card', ['product' => $related])</div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
