@extends('shop.layouts.app')
@section('title', 'Заказ #'.$order->id)
@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background: var(--primary);">Заказ #{{ $order->id }}</div>
                <div class="card-body">
                    <table class="table">
                        <thead><tr><th>Товар</th><th>Цена</th><th>Кол-во</th><th>Итого</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ number_format($item->price, 0, ',', ' ') }} ₽</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->cost(), 0, ',', ' ') }} ₽</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="3" class="text-end fw-bold">Всего</td><td class="fw-bold">{{ number_format($order->totalCost(), 0, ',', ' ') }} ₽</td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <p><strong>Статус:</strong> {{ $order->statusLabel() }}</p>
                    <p><strong>Оплата:</strong> {{ $order->paid ? 'Оплачен' : 'Не оплачен' }}</p>
                    <p><strong>Дата:</strong> {{ $order->created_at->format('d.m.Y H:i') }}</p>
                    @if(!$order->paid)
                    <a href="{{ route('order.payment', $order) }}" class="btn btn-accent w-100">Оплатить</a>
                    @endif
                    @if($order->canBeCancelledByUser())
                    <form action="{{ route('order.cancel', $order) }}" method="POST" class="mt-2" onsubmit="return confirm('Отменить заказ #{{ $order->id }}?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash-alt me-2"></i>Отменить заказ
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Доставка</h6>
                    <p class="small mb-0">{{ $order->address }}, {{ $order->city }}, {{ $order->postal_code }}<br>{{ $order->first_name }} {{ $order->last_name }}<br>{{ $order->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
