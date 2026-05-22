<div class="card h-100 product-card">
    <div class="position-relative">
        <a href="{{ route('product.show', $product) }}" class="product-photo-wrap product-photo-wrap--card d-block">
            <img src="{{ $product->imageUrl() }}" class="product-photo @if($product->hasWhiteMatteBackground()) product-photo--matte @endif" alt="{{ $product->name }}" loading="lazy">
        </a>
        @if($product->price < 3500)
        <span class="badge badge-sale position-absolute top-0 start-0 m-2">Sale</span>
        @endif
    </div>
    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-1">
            <a href="{{ route('product.show', $product) }}" class="text-decoration-none text-dark">{{ $product->name }}</a>
        </h5>
        <p class="card-text text-muted small mb-3">
            <i class="fas fa-tag me-1 opacity-50"></i>{{ $product->category->name }}
        </p>
        <div class="d-flex justify-content-between align-items-center mt-auto">
            <span class="product-price">{{ number_format($product->price, 0, ',', ' ') }} ₽</span>
            @auth
            <form action="{{ route('cart.add', $product) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-sm btn-accent rounded-pill px-3" title="В корзину">
                    <i class="fas fa-cart-plus"></i>
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Войдите для покупки">
                <i class="fas fa-cart-plus"></i>
            </a>
            @endauth
        </div>
    </div>
</div>
