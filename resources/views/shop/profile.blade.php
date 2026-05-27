@extends('shop.layouts.app')
@section('title', 'Профиль')
@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-center p-4 shadow-sm">
                <img src="{{ $user->profile?->avatarUrl() ?? asset('images/avatar-default.svg') }}" class="rounded-circle mx-auto mb-3" width="120" alt="">
                <h4>{{ $user->name }}</h4>
                <p class="text-muted small">С {{ $user->created_at->format('d.m.Y') }}</p>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">Редактировать</a>
            </div>
            <div class="card mt-3 shadow-sm">
                <div class="card-body">
                    <h6>Контакты</h6>
                    <p class="mb-1 small"><i class="fas fa-phone me-2"></i>{{ $user->profile?->phone ?? '—' }}</p>
                    <p class="mb-1 small"><i class="fas fa-city me-2"></i>{{ $user->profile?->city ?? '—' }}</p>
                    <p class="mb-0 small"><i class="fas fa-map-marker-alt me-2"></i>{{ $user->profile?->address ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header text-white panel-header">Мои заказы</div>
                <div class="card-body">
                    @if($orders->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>№</th><th>Дата</th><th>Сумма</th><th>Статус</th><th></th></tr></thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                    <td>{{ number_format($order->totalCost(), 0, ',', ' ') }} ₽</td>
                                    <td><span class="badge bg-secondary">{{ $order->statusLabel() }}</span></td>
                                    <td><a href="{{ route('order.show', $order) }}" class="btn btn-sm btn-primary-custom">Подробнее</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="mb-0">Заказов пока нет. <a href="{{ route('products') }}">Перейти в каталог</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
