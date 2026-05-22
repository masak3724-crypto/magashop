@extends('shop.layouts.app')
@section('title', 'Корзина')
@section('content')
<div class="container py-5">
    <div class="text-center mb-4">
        <h1 style="color: var(--primary-dark);"><i class="fas fa-shopping-cart me-2"></i>Корзина</h1>
        <p class="lead">Выбранные товары</p>
    </div>
    @if($cart->items->isNotEmpty())
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead style="background: var(--primary); color: #fff;">
                                <tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Итого</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($cart->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="product-photo-wrap product-photo-wrap--cart me-3">
                                                <img src="{{ $item->product->imageUrl() }}" class="product-photo @if($item->product->hasWhiteMatteBackground()) product-photo--matte @endif" alt="">
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                <small class="text-muted">{{ $item->product->category->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($item->product->price, 0, ',', ' ') }} ₽</td>
                                    <td>
                                        <form action="{{ route('cart.update', $item) }}" method="POST" class="cart-qty-form">
                                            @csrf
                                            <div class="qty-stepper">
                                                <button type="button" class="qty-stepper__btn minus-btn" aria-label="Уменьшить">−</button>
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="100" class="qty-stepper__input quantity-input" readonly>
                                                <button type="button" class="qty-stepper__btn plus-btn" aria-label="Увеличить">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>{{ number_format($item->totalPrice(), 0, ',', ' ') }} ₽</td>
                                    <td>
                                        <form action="{{ route('cart.remove', $item) }}" method="POST">@csrf
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm" style="border-radius: 15px;">
                <div class="card-body">
                    <h5>Итог заказа</h5>
                    <p class="d-flex justify-content-between"><span>Товары</span><strong>{{ number_format($cart->totalPrice(), 0, ',', ' ') }} ₽</strong></p>
                    <p class="d-flex justify-content-between"><span>Доставка</span><span>Бесплатно</span></p>
                    <hr>
                    <a href="{{ route('order.create') }}" class="btn btn-accent w-100 mb-2">Оформить заказ</a>
                    <a href="{{ route('products') }}" class="btn btn-outline-primary w-100">Продолжить покупки</a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card text-center py-5">
        <div class="card-body">
            <i class="fas fa-shopping-cart fa-4x mb-3" style="color: var(--accent);"></i>
            <h3>Корзина пуста</h3>
            <a href="{{ route('products') }}" class="btn btn-accent mt-3">В каталог</a>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shop-forms.js') }}"></script>
@endpush
