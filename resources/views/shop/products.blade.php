@extends('shop.layouts.app')

@section('title', 'Каталог')

@section('content')
<div class="container py-5">
    <div class="catalog-hero text-center py-5 mb-5 px-3">
        <img src="{{ asset('images/logo-dark.svg') }}" alt="ModaStyle" height="40" class="mb-3">
        <h1 class="fw-bold mb-2 hero-editorial">Каталог</h1>
        <p class="lead mb-0 opacity-90">Одежда, обувь и аксессуары — более 2000 моделей</p>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0" style="color: var(--primary-dark);">Все товары</h2>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="dropdown d-inline-block">
                <button class="btn btn-primary-custom dropdown-toggle rounded-0 px-4" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i> {{ $currentCategoryLabel }}
                </button>
                <ul class="dropdown-menu shadow border-0 rounded-0">
                    <li><a class="dropdown-item" href="{{ route('products', ['category' => 'all']) }}">Все</a></li>
                    @foreach($categories as $category)
                    <li><a class="dropdown-item" href="{{ route('products', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="d-block d-md-none mb-4">
        <div class="d-flex flex-nowrap overflow-auto py-2 gap-2">
            <a href="{{ route('products', ['category' => 'all']) }}" class="btn btn-sm {{ $currentCategory === 'all' ? 'btn-primary-custom' : 'btn-outline-dark' }} flex-shrink-0 rounded-0">Все</a>
            @foreach($categories as $category)
            <a href="{{ route('products', ['category' => $category->slug]) }}" class="btn btn-sm {{ $currentCategory === $category->slug ? 'btn-primary-custom' : 'btn-outline-dark' }} flex-shrink-0 rounded-0">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>

    <div class="row">
        @forelse($products as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            @include('shop.partials.product_card', ['product' => $product])
        </div>
        @empty
        <div class="col-12">
            <div class="card text-center py-5 border-0 shadow-sm rounded-0" style="background: var(--gradient-card);">
                <div class="card-body">
                    <div class="benefit-icon mx-auto mb-4"><i class="fas fa-box-open"></i></div>
                    <h3 class="fw-bold" style="color: var(--primary-dark);">Товары не найдены</h3>
                    <p class="text-muted">Попробуйте другую категорию</p>
                    <a href="{{ route('products') }}" class="btn btn-accent rounded-0 px-4 mt-2">Сбросить фильтры</a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
