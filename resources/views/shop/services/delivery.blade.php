@extends('shop.layouts.app')
@section('title', 'Доставка')

@section('content')
<section class="page-header-gradient">
    <div class="container position-relative">
        <div class="service-page-badge mb-3">
            <img src="{{ asset('images/icon-delivery.svg') }}" alt="" width="36" height="36">
        </div>
        <h1 class="display-6 fw-bold mb-2">Доставка с примеркой</h1>
        <p class="lead mb-0 opacity-90">Бесплатно от 3 999 ₽ по городу и в регионы России</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <p class="lead fw-medium" style="color: var(--primary-dark);">Доставим заказ курьером — примерьте дома и оплатите только то, что подошло.</p>
                <p class="text-muted">Заказы от 3 999 ₽ доставляем бесплатно по Москве. В другие города — СДЭК, Boxberry, ПЭК. Срок и стоимость рассчитываем при оформлении.</p>

                <h3 class="h5 fw-bold mt-4 mb-3" style="color: var(--primary-dark);">Условия</h3>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="value-card h-100">
                            <h4 class="h6 fw-bold">По городу</h4>
                            <p class="text-muted small mb-0">Бесплатно от 3 999 ₽. При меньшей сумме — 390 ₽. Срок 1–2 рабочих дня.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="value-card h-100">
                            <h4 class="h6 fw-bold">По России</h4>
                            <p class="text-muted small mb-0">Отправка в день оплаты. Трек-номер в личном кабинете.</p>
                        </div>
                    </div>
                </div>

                <h3 class="h5 fw-bold mt-4 mb-3" style="color: var(--primary-dark);">Как это работает</h3>
                <ol class="text-muted">
                    <li class="mb-2">Оформите заказ в каталоге и выберите доставку с примеркой.</li>
                    <li class="mb-2">Курьер привезёт несколько размеров на выбор.</li>
                    <li class="mb-2">Примерьте дома в спокойной обстановке.</li>
                    <li>Оплатите только понравившиеся вещи, остальное вернёте курьеру.</li>
                </ol>
            </div>
            <div class="col-lg-5">
                @include('shop.services.partials.sidebar', [
                    'highlights' => [
                        'Бесплатно от 3 999 ₽ по городу',
                        'Примерка перед оплатой',
                        'Отслеживание заказа онлайн',
                        'Самовывоз: г. Чебоксары, ул. Декабристов, 17А',
                    ],
                ])
            </div>
        </div>
    </div>
</section>
@endsection
