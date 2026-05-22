@extends('admin.layouts.app')
@section('title', 'Товары')
@section('page_title', 'Товары')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-plus-circle me-2 text-accent"></i>Добавление товара</h5>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#addProductForm">
            {{ request()->has('add') || $errors->any() ? 'Свернуть' : 'Развернуть' }}
        </button>
    </div>
    <div class="collapse {{ request()->has('add') || $errors->any() ? 'show' : '' }}" id="addProductForm">
        <div class="card-body border-top">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Название</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Michelin Primacy 4 205/55 R16">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Категория</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Выберите...</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Цена, ₽</label>
                        <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" min="0" step="1" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Описание</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" required placeholder="Описание товара...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Фото</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,image/gif">
                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Белый фон убирается автоматически</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Наличие</label>
                        <select name="available" class="form-select">
                            <option value="1" @selected(old('available', '1') == '1')>В наличии</option>
                            <option value="0" @selected(old('available') === '0')>Нет в наличии</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-accent">
                            <i class="fas fa-plus me-1"></i> Добавить товар
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Поиск</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Название товара...">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Наличие</label>
                <select name="available" class="form-select">
                    <option value="">Все</option>
                    <option value="1" @selected(request('available') === '1')>В наличии</option>
                    <option value="0" @selected(request('available') === '0')>Нет в наличии</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary-custom">Найти</button>
                <a href="{{ route('admin.products.index', ['add' => 1]) }}#addProductForm" class="btn btn-accent">
                    <i class="fas fa-plus me-1"></i> Новый товар
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle admin-products-table">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th class="admin-product-photo-col">Фото</th>
                        <th>Товар</th>
                        <th>Категория</th>
                        <th>Цена, ₽</th>
                        <th>Наличие</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td class="admin-product-photo-col">
                            <div class="product-photo-wrap product-photo-wrap--admin">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="product-photo @if($product->hasWhiteMatteBackground()) product-photo--matte @endif">
                            </div>
                            <div class="admin-product-photo-actions">
                                <form action="{{ route('admin.products.image.store', $product) }}" method="POST" enctype="multipart/form-data" class="mb-1">
                                    @csrf
                                    <input type="file" name="image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/gif" required onchange="this.form.submit()">
                                </form>
                                @if($product->hasImageFile())
                                <form action="{{ route('admin.products.image.destroy', $product) }}" method="POST" onsubmit="return confirm('Удалить фото?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Удалить фото</button>
                                </form>
                                @endif
                            </div>
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>
                            <form id="update-{{ $product->id }}" action="{{ route('admin.products.update', $product) }}" method="POST" class="d-none">@csrf @method('PUT')</form>
                            <input type="number" name="price" form="update-{{ $product->id }}" class="form-control form-control-sm" style="width:120px" value="{{ (int) $product->price }}" min="0" step="1">
                        </td>
                        <td>
                            <select name="available" form="update-{{ $product->id }}" class="form-select form-select-sm" style="width:140px">
                                <option value="1" @selected($product->available)>В наличии</option>
                                <option value="0" @selected(!$product->available)>Нет в наличии</option>
                            </select>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="submit" form="update-{{ $product->id }}" class="btn btn-sm btn-accent">Сохранить</button>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить товар «{{ $product->name }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Товары не найдены</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-3">{{ $products->links() }}</div>
    @endif
</div>
@endsection
