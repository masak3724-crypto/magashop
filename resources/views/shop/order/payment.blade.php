@extends('shop.layouts.app')
@section('title', 'Оплата')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background: var(--primary);">Оплата заказа #{{ $order->id }}</div>
                <div class="card-body">
                    <div class="alert alert-info">Сумма к оплате: <strong>{{ number_format($order->totalCost(), 0, ',', ' ') }} ₽</strong></div>
                    <form method="POST" action="{{ route('order.payment.process', $order) }}" id="payment-form" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Номер карты</label>
                            <input type="text" class="form-control" name="card_number" placeholder="0000 0000 0000 0000" inputmode="numeric" autocomplete="cc-number" maxlength="19" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Имя владельца</label>
                            <input type="text" class="form-control" name="card_name" placeholder="IVAN IVANOV" autocomplete="cc-name" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Срок</label>
                                <input type="text" class="form-control" name="card_expiry" placeholder="MM/YY" inputmode="numeric" maxlength="5" autocomplete="cc-exp" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" name="card_cvv" inputmode="numeric" maxlength="3" autocomplete="cc-csc" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-accent w-100"><i class="fas fa-lock me-2"></i>Оплатить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shop-forms.js') }}"></script>
@endpush
