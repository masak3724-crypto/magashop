<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupportChatService
{
    public function isAiEnabled(): bool
    {
        return $this->resolveProvider() !== 'fallback';
    }

    public function providerLabel(): string
    {
        return match ($this->resolveProvider()) {
            'openai' => 'AI-ассистент',
            'gemini' => 'AI Gemini',
            'groq' => 'AI Groq',
            'pollinations' => 'AI-ассистент',
            default => 'Умный помощник',
        };
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function reply(string $message, array $history = []): string
    {
        $provider = $this->resolveProvider();

        if ($provider !== 'fallback') {
            $aiReply = match ($provider) {
                'openai' => $this->askOpenAiCompatible($message, $history, config('services.openai')),
                'gemini' => $this->askGemini($message, $history),
                'groq' => $this->askOpenAiCompatible($message, $history, config('services.groq')),
                'pollinations' => $this->askPollinations($message, $history),
                default => null,
            };

            if ($aiReply !== null) {
                return $aiReply;
            }
        }

        return $this->fallbackReply($message);
    }

    private function resolveProvider(): string
    {
        $forced = config('services.support_ai.provider');

        if ($forced && $forced !== 'auto' && $this->providerConfigured($forced)) {
            return $forced;
        }

        if (filled(config('services.openai.key'))) {
            return 'openai';
        }

        if (filled(config('services.gemini.key'))) {
            return 'gemini';
        }

        if (filled(config('services.groq.key'))) {
            return 'groq';
        }

        if (config('services.pollinations.enabled')) {
            return 'pollinations';
        }

        return 'fallback';
    }

    private function providerConfigured(string $provider): bool
    {
        return match ($provider) {
            'openai' => filled(config('services.openai.key')),
            'gemini' => filled(config('services.gemini.key')),
            'groq' => filled(config('services.groq.key')),
            'pollinations' => (bool) config('services.pollinations.enabled'),
            default => false,
        };
    }

    /**
     * @param  array{key?: string, base_url: string, model: string}  $config
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function askOpenAiCompatible(string $message, array $history, array $config): ?string
    {
        if (empty($config['key'])) {
            return null;
        }

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ...array_slice($history, -8),
            ['role' => 'user', 'content' => $message],
        ];

        try {
            $response = Http::withToken($config['key'])
                ->timeout(30)
                ->post(rtrim($config['base_url'], '/').'/chat/completions', [
                    'model' => $config['model'],
                    'messages' => $messages,
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                return $this->cleanAiText($response->json('choices.0.message.content'));
            }
        } catch (\Throwable) {
            //
        }

        return null;
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function askGemini(string $message, array $history): ?string
    {
        $key = config('services.gemini.key');
        if (! $key) {
            return null;
        }

        $model = config('services.gemini.model');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $contents = [];
        foreach (array_slice($history, -6) as $item) {
            $role = $item['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = ['role' => $role, 'parts' => [['text' => $item['content']]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['x-goog-api-key' => $key])
                ->post($url, [
                    'systemInstruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ],
                ]);

            if ($response->successful()) {
                return $this->cleanAiText($response->json('candidates.0.content.parts.0.text'));
            }
        } catch (\Throwable) {
            //
        }

        return null;
    }

    /**
     * Бесплатный анонимный API: https://text.pollinations.ai
     *
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function askPollinations(string $message, array $history): ?string
    {
        $prompt = $this->buildPollinationsPrompt($message, $history);

        try {
            $client = Http::timeout(45);

            if (! config('services.pollinations.verify_ssl')) {
                $client = $client->withoutVerifying();
            }

            $response = $client->get(rtrim(config('services.pollinations.base_url'), '/').'/'.rawurlencode($prompt), [
                'model' => config('services.pollinations.model'),
            ]);

            if ($response->successful()) {
                $text = $this->cleanAiText($response->body());

                if ($text !== null && ! str_starts_with($text, '⚠️')) {
                    return $text;
                }
            }
        } catch (\Throwable) {
            //
        }

        return null;
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    private function buildPollinationsPrompt(string $message, array $history): string
    {
        $lines = [
            $this->systemPrompt(),
            '',
            'Веди диалог на русском языке. Отвечай кратко (2–4 предложения).',
        ];

        foreach (array_slice($history, -6) as $item) {
            $prefix = $item['role'] === 'assistant' ? 'Ассистент' : 'Клиент';
            $lines[] = "{$prefix}: {$item['content']}";
        }

        $lines[] = "Клиент: {$message}";
        $lines[] = 'Ассистент:';

        return implode("\n", $lines);
    }

    private function cleanAiText(mixed $text): ?string
    {
        if (! is_string($text)) {
            return null;
        }

        $text = trim($text);

        return $text !== '' ? $text : null;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Ты — виртуальный ассистент интернет-магазина одежды ModaStyle (Россия).
Отвечай кратко, дружелюбно и по делу на русском языке.
Помогай с: подбором размера, каталогом, доставкой с примеркой, оплатой, возвратом, программой лояльности, заказами и контактами.
Факты о магазине:
- Доставка: бесплатно от 3 999 ₽ по Чебоксарам; в регионы — СДЭК, Boxberry, ПЭК. Есть примерка перед оплатой.
- Телефон: +7 937 953 54 80, email: info@modastyle.ru.
- Адрес: г. Чебоксары, ул. Декабристов, 17А.
- Возврат и обмен в течение 14 дней, программа лояльности ModaStyle Club.
Если вопрос требует действий менеджера (жалоба, возврат конкретного заказа, индивидуальный расчёт) — предложи связаться по телефону или через страницу «Контакты».
Не выдумывай цены и наличие конкретных моделей — направляй в каталог.
PROMPT;
    }

    private function fallbackReply(string $message): string
    {
        $text = Str::lower($message);

        if (Str::contains($text, ['привет', 'здравств', 'добрый', 'hi', 'hello'])) {
            return 'Здравствуйте! Я ассистент ModaStyle. Помогу с размером, доставкой, заказом или отвечу на вопросы о магазине. Чем могу помочь?';
        }

        if (Str::contains($text, ['доставк', 'привез', 'курьер', 'сдэк', 'отправ'])) {
            return 'Доставка по Чебоксарам бесплатна при заказе от 3 999 ₽ (иначе 390 ₽, 1–2 рабочих дня). Доступна примерка перед оплатой. По России — СДЭК, Boxberry и ПЭК. Подробнее на странице «Доставка».';
        }

        if (Str::contains($text, ['гарант', 'подлин', 'оригинал', 'поддел'])) {
            return 'Мы продаём оригинальную одежду и обувь от официальных поставщиков. Возврат и обмен — в течение 14 дней. Подробности — в разделе «Возврат и обмен».';
        }

        if (Str::contains($text, ['лояльн', 'балл', 'кэшбэк', 'клуб', 'скидк'])) {
            return 'Программа ModaStyle Club: до 7% кэшбэка баллами, скидка на день рождения и ранний доступ к распродаже. Подробности — на странице «Программа лояльности».';
        }

        if (Str::contains($text, ['оплат', 'карт', 'налич', 'рассроч'])) {
            return 'Оплата доступна при оформлении заказа в личном кабинете — способы указаны на этапе оплаты. Если нужна консультация по оплате конкретного заказа, позвоните нам: +7 937 953 54 80.';
        }

        if (Str::contains($text, ['заказ', 'оформ', 'купить', 'корзин', 'каталог'])) {
            return 'Чтобы оформить заказ: выберите вещи в каталоге, добавьте в корзину, войдите в аккаунт и перейдите к оформлению. Нужна регистрация — это займёт пару минут.';
        }

        if (Str::contains($text, ['размер', 'подбор', 'плать', 'джинс', 'обув', 's ', 'm ', 'l ', 'xl'])) {
            return 'На карточке каждого товара есть таблица размеров. Если сомневаетесь — закажите два размера с примеркой и оставьте подходящий. Напишите рост, вес и обычный размер — подскажу, на что смотреть.';
        }

        if (Str::contains($text, ['контакт', 'телефон', 'почт', 'email', 'адрес', 'где вы', 'находит', 'связать'])) {
            return 'Контакты ModaStyle: телефон +7 937 953 54 80, email info@modastyle.ru, адрес — г. Чебоксары, ул. Декабристов, 17А. Также форма на странице «Контакты».';
        }

        if (Str::contains($text, ['возврат', 'обмен', 'вернут'])) {
            return 'Возврат и обмен в течение 14 дней при сохранении бирок. Для индивидуальной ситуации: +7 937 953 54 80 или info@modastyle.ru.';
        }

        if (Str::contains($text, ['спасибо', 'благодар'])) {
            return 'Рад помочь! Если появятся ещё вопросы — пишите. Приятных покупок!';
        }

        return 'Спасибо за вопрос! Я могу подсказать по каталогу, размерам, доставке с примеркой, возврату и программе лояльности. Уточните, что вас интересует — или позвоните: +7 937 953 54 80.';
    }
}
