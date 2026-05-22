@extends('admin.layouts.app')
@section('title', 'Заказы')
@section('page_title', 'Заказы')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Поиск</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="№, email, имя...">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Статус</label>
                <select name="status" class="form-select">
                    <option value="">Все</option>
                    @foreach(\App\Models\Order::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Оплата</label>
                <select name="paid" class="form-select">
                    <option value="">Все</option>
                    <option value="1" @selected(request('paid') === '1')>Оплачен</option>
                    <option value="0" @selected(request('paid') === '0')>Не оплачен</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary-custom">Фильтр</button>
                @if(request()->hasAny(['q', 'status', 'paid']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Сброс</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>№</th>
                        <th>Клиент</th>
                        <th>Email</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Оплата</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->first_name }} {{ $order->last_name }}</td>
                        <td>{{ $order->email }}</td>
                        <td>{{ number_format($order->totalCost(), 0, ',', ' ') }} ₽</td>
                        <td><span class="badge status-badge status-{{ $order->status }}">{{ $order->statusLabel() }}</span></td>
                        <td>
                            <span class="badge {{ $order->paymentBadgeClass() }}">{{ $order->paymentLabel() }}</span>
                        </td>
                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Управление</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Заказы не найдены</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
    <div class="card-footer bg-white">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
