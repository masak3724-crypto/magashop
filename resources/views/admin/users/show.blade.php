@extends('admin.layouts.app')
@section('title', $user->name)
@section('page_title', 'Пользователь: '.$user->name)

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">{{ $user->name }}</h5>
                @if($user->is_admin)
                <span class="badge bg-dark mb-2">Администратор</span>
                @endif
                <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i>{{ $user->email }}</p>
                <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i>{{ $user->profile?->phone ?? '—' }}</p>
                <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-muted"></i>{{ $user->profile?->city ?? '—' }}</p>
                <p class="mb-0 small text-muted">Зарегистрирован: {{ $user->created_at->format('d.m.Y H:i') }}</p>
                @if(!$user->is_admin)
                <form action="{{ route('admin.users.admin.store') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="mode" value="promote">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <button type="submit" class="btn btn-sm btn-accent w-100">
                        <i class="fas fa-user-shield me-1"></i> Назначить администратором
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.users.index', ['admin' => 1]) }}" class="btn btn-sm btn-outline-secondary mt-2 w-100">← К списку</a>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Заказы пользователя</h5>
            </div>
            <div class="card-body p-0">
                @if($user->orders->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>№</th>
                                <th>Дата</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Оплата</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td>{{ number_format($order->totalCost(), 0, ',', ' ') }} ₽</td>
                                <td><span class="badge status-badge status-{{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                                <td><span class="badge {{ $order->paymentBadgeClass() }}">{{ $order->paymentLabel() }}</span></td>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Открыть</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-4 mb-0">Заказов нет</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
