<div id="support-widget" class="support-widget" data-chat-url="{{ route('support.chat') }}">
    <div id="support-panel" class="support-panel" hidden>
        <div class="support-panel__header">
            <div class="support-panel__avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <strong class="support-panel__title">Онлайн-поддержка</strong>
                <span class="support-panel__status">
                    <span class="support-panel__status-dot"></span>
                    <span id="support-status-label">{{ app(\App\Services\SupportChatService::class)->providerLabel() }} онлайн</span>
                </span>
            </div>
            <button type="button" class="support-panel__close" id="support-close" aria-label="Закрыть чат">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="support-panel__messages" id="support-messages" role="log" aria-live="polite"></div>
        <div class="support-panel__quick">
            <button type="button" class="support-quick-btn" data-message="Расскажите об условиях и сроках доставки">
                <i class="fas fa-truck me-1"></i> Доставка
            </button>
            <button type="button" class="support-quick-btn" data-message="Как подобрать размер одежды?">
                <i class="fas fa-ruler me-1"></i> Размер
            </button>
            <button type="button" class="support-quick-btn" data-message="Как с вами связаться?">
                <i class="fas fa-phone me-1"></i> Контакты
            </button>
        </div>
        <form class="support-panel__form" id="support-form">
            <input
                type="text"
                id="support-input"
                class="support-panel__input"
                placeholder="Напишите сообщение..."
                maxlength="1000"
                autocomplete="off"
                required
            >
            <button type="submit" class="support-panel__send" id="support-send" aria-label="Отправить">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <button type="button" class="support-widget__toggle" id="support-toggle" aria-label="Открыть онлайн-поддержку" aria-expanded="false">
        <i class="fas fa-comments support-widget__icon-open"></i>
        <i class="fas fa-times support-widget__icon-close"></i>
    </button>
</div>
