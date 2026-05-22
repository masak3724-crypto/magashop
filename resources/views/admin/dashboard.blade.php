@extends('admin.layouts.app')
@section('title', 'Дашборд')
@section('page_title', 'Дашборд')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4 col-lg-2">
        <div class="admin-stat-card">
            <div class="admin-stat-icon text-primary"><i class="fas fa-users"></i></div>
            <div class="admin-stat-value">{{ $stats['users'] }}</div>
            <div class="admin-stat-label">Пользователей</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="admin-stat-card">
            <div class="admin-stat-icon text-success"><i class="fas fa-box"></i></div>
            <div class="admin-stat-value">{{ $stats['orders'] }}</div>
            <div class="admin-stat-label">Заказов</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="admin-stat-card">
            <div class="admin-stat-icon text-warning"><i class="fas fa-clock"></i></div>
            <div class="admin-stat-value">{{ $stats['pending_orders'] }}</div>
            <div class="admin-stat-label">Ожидают</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="admin-stat-card">
            <div class="admin-stat-icon text-info"><i class="fas fa-ruble-sign"></i></div>
            <div class="admin-stat-value">{{ number_format($stats['revenue'], 0, ',', ' ') }}</div>
            <div class="admin-stat-label">Выручка, ₽</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="admin-stat-card">
            <div class="admin-stat-icon text-secondary"><i class="fas fa-shirt"></i></div>
            <div class="admin-stat-value">{{ $stats['products'] }}</div>
            <div class="admin-stat-label">Товаров</div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="admin-stat-card">
            <div class="admin-stat-icon text-danger"><i class="fas fa-envelope"></i></div>
            <div class="admin-stat-value">{{ $stats['unread_messages'] }}</div>
            <div class="admin-stat-label">Новых писем</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Последние заказы</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary-custom">Все заказы</a>
            </div>
            <div class="card-body p-0">
                @if($recentOrders->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>№</th>
                                <th>Клиент</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Оплата</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->user?->name ?? $order->email }}</td>
                                <td>{{ number_format($order->totalCost(), 0, ',', ' ') }} ₽</td>
                                <td><span class="badge status-badge status-{{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                                <td>
                                    <span class="badge {{ $order->paymentBadgeClass() }}">{{ $order->paymentLabel() }}</span>
                                </td>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Открыть</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-4 mb-0">Заказов пока нет</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Заказы по статусам</h5>
            </div>
            <div class="card-body">
                @foreach(\App\Models\Order::STATUSES as $key => $label)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>{{ $label }}</span>
                    <span class="badge bg-primary">{{ $ordersByStatus[$key] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
