@extends('shop.layouts.app')
@section('title', 'Программа лояльности')

@section('content')
<section class="page-header-gradient">
    <div class="container position-relative">
        <div class="service-page-badge mb-3">
            <img src="{{ asset('images/icon-fitting.svg') }}" alt="" width="36" height="36">
        </div>
        <h1 class="display-6 fw-bold mb-2">Программа лояльности</h1>
        <p class="lead mb-0 opacity-90">Копите баллы и получайте персональные скидки</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <p class="lead fw-medium" style="color: var(--primary-dark);">Станьте участником ModaStyle Club — как LOVE CARD у Love Republic, только у нас.</p>
                <p class="text-muted">За каждую покупку начисляем баллы, дарим скидку на день рождения и ранний доступ к распродажам. Чем больше покупаете — тем выше ваш статус.</p>

                <h3 class="h5 fw-bold mt-4 mb-3" style="color: var(--primary-dark);">Уровни программы</h3>
                <div class="table-responsive">
                    <table class="table service-price-table align-middle">
                        <thead>
                            <tr>
                                <th>Статус</th>
                                <th class="text-end">Кэшбэк</th>
                                <th class="text-end">Бонус</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted">
                            <tr><td>Style Start</td><td class="text-end">3%</td><td class="text-end">Приветственные 500 ₽</td></tr>
                            <tr><td>Style Plus</td><td class="text-end">5%</td><td class="text-end">−10% ко дню рождения</td></tr>
                            <tr><td>Style VIP</td><td class="text-end">7%</td><td class="text-end">Ранний доступ к sale</td></tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="h5 fw-bold mt-4 mb-3" style="color: var(--primary-dark);">Как присоединиться</h3>
                <p class="text-muted">Зарегистрируйтесь на сайте — карта лояльности активируется автоматически. Баллы отображаются в личном кабинете после каждого заказа.</p>
            </div>
            <div class="col-lg-5">
                @include('shop.services.partials.sidebar', [
                    'highlights' => [
                        'До 7% кэшбэка баллами',
                        'Скидка на день рождения',
                        'Ранний доступ к распродаже',
                        'Персональные подборки',
                    ],
                ])
            </div>
        </div>
    </div>
</section>
@endsection
