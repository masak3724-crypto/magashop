# Деплой ModaStyle на Railway

## 1. Создать проект

1. [railway.app/new](https://railway.app/new) → **Deploy from GitHub repo**
2. Репозиторий: `masak3724-crypto/magashop`
3. Ветка: `main`

## 2. База данных

На canvas добавьте **PostgreSQL** (рекомендуется) или **MySQL**.

SQLite на Railway **не подходит** — файловая система сбрасывается при деплое.

## 3. Переменные окружения

В сервисе Laravel: **Variables** → **Raw Editor** — вставьте содержимое файла [`.env.railway`](../.env.railway) из репозитория.

Обязательно задайте `APP_KEY` (локально):

```bash
php artisan key:generate --show
```

Скопируйте значение в Railway.

После выдачи домена в **Networking** → **Generate Domain** обновите:

```env
APP_URL=https://ваш-домен.up.railway.app
```

## 4. Деплой

При push в `main` Railway собирает проект автоматически.

Перед стартом выполняется `railway/init-app.sh`:

- миграции
- начальное наполнение каталога (если товаров ещё нет)
- `storage:link`, кэш config/route/view

Проверка здоровья: `GET /up`

## 5. Демо-аккаунты (после первого seed)

| Роль | Email | Пароль |
|------|-------|--------|
| Админ | admin@modastyle.ru | password |
| Покупатель | demo@modastyle.ru | password |

Смените пароли после выкладки в прод.

## 6. CLI (опционально)

```bash
npm i -g @railway/cli
railway login
railway link
railway up
```

## 7. Обновление каталога на сервере

```bash
railway run php artisan catalog:sync --prune
railway run php artisan products:sync-images
```

## Структура файлов Railway

| Файл | Назначение |
|------|------------|
| `nixpacks.toml` | PHP 8.3, расширения, `composer install` |
| `railway.json` | preDeploy, healthcheck |
| `railway/init-app.sh` | миграции и первичный seed |
| `.env.railway` | шаблон переменных |
