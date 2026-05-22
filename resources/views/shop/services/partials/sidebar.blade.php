<div class="service-info-card">
    <h5 class="fw-bold mb-3" style="color: var(--primary-dark);">Кратко</h5>
    <ul class="service-info-list mb-4">
        @foreach($highlights as $item)
        <li><i class="fas fa-check-circle text-accent me-2"></i>{{ $item }}</li>
        @endforeach
    </ul>
    <a href="{{ route('products') }}" class="btn btn-glow rounded-pill w-100 mb-2">Перейти в каталог</a>
    <a href="{{ route('contacts') }}" class="btn btn-outline-primary rounded-pill w-100">Задать вопрос</a>
</div>
