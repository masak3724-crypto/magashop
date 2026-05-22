document.addEventListener('DOMContentLoaded', function () {
    var widget = document.getElementById('support-widget');
    if (!widget) return;

    var panel = document.getElementById('support-panel');
    var toggle = document.getElementById('support-toggle');
    var closeBtn = document.getElementById('support-close');
    var form = document.getElementById('support-form');
    var input = document.getElementById('support-input');
    var messages = document.getElementById('support-messages');
    var sendBtn = document.getElementById('support-send');
    var quickBtns = document.querySelectorAll('.support-quick-btn');
    var statusLabel = document.getElementById('support-status-label');
    var chatUrl = widget.dataset.chatUrl;
    var isOpen = false;
    var greeted = false;
    var loading = false;

    var welcomeText = 'Здравствуйте! Я AI-ассистент ModaStyle. Помогу с размером, доставкой с примеркой и заказом. Выберите быстрый вопрос ниже или напишите своё сообщение.';

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(text, type) {
        var bubble = document.createElement('div');
        bubble.className = 'support-msg support-msg--' + type;
        bubble.innerHTML = '<div class="support-msg__bubble">' + escapeHtml(text).replace(/\n/g, '<br>') + '</div>';
        messages.appendChild(bubble);
        scrollToBottom();
    }

    function addTyping() {
        var el = document.createElement('div');
        el.className = 'support-msg support-msg--bot support-msg--typing';
        el.id = 'support-typing';
        el.innerHTML = '<div class="support-msg__bubble"><span></span><span></span><span></span></div>';
        messages.appendChild(el);
        scrollToBottom();
    }

    function removeTyping() {
        var el = document.getElementById('support-typing');
        if (el) el.remove();
    }

    function setQuickButtonsDisabled(disabled) {
        quickBtns.forEach(function (btn) {
            btn.disabled = disabled;
        });
    }

    function sendMessage(text) {
        if (loading || !text) return;

        addMessage(text, 'user');
        loading = true;
        sendBtn.disabled = true;
        input.disabled = true;
        setQuickButtonsDisabled(true);
        addTyping();

        fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ message: text }),
        })
            .then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) throw data;
                    return data;
                });
            })
            .then(function (data) {
                removeTyping();
                if (data.provider && statusLabel) {
                    statusLabel.textContent = data.provider + ' онлайн';
                }
                addMessage(data.reply || 'Извините, не удалось получить ответ. Попробуйте ещё раз.', 'bot');
            })
            .catch(function (err) {
                removeTyping();
                var msg = 'Не удалось отправить сообщение. Проверьте соединение и попробуйте снова.';
                if (err && err.errors && err.errors.message) {
                    msg = err.errors.message[0];
                }
                addMessage(msg, 'bot');
            })
            .finally(function () {
                loading = false;
                sendBtn.disabled = false;
                input.disabled = false;
                setQuickButtonsDisabled(false);
                input.focus();
            });
    }

    function setOpen(open) {
        isOpen = open;
        panel.hidden = !open;
        widget.classList.toggle('support-widget--open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (open && !greeted) {
            greeted = true;
            addMessage(welcomeText, 'bot');
        }

        if (open) {
            setTimeout(function () { input.focus(); }, 200);
        }
    }

    toggle.addEventListener('click', function () {
        setOpen(!isOpen);
    });

    closeBtn.addEventListener('click', function () {
        setOpen(false);
    });

    quickBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.getAttribute('data-message');
            if (text) {
                sendMessage(text);
            }
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = input.value.trim();
        if (!text) return;
        input.value = '';
        sendMessage(text);
    });
});
