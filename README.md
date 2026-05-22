# ModaStyle

Интернет-магазин модной одежды, обуви и аксессуаров на **Laravel 13**: каталог, корзина, заказы, профиль и админ-панель.

## Требования

- PHP 8.3+
- Composer
- SQLite (по умолчанию) или MySQL
- Git (опционально)

## Установка и запуск

```bash
cd magashop
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Откройте: http://127.0.0.1:8000

## Демо-аккаунты

| Роль  | Email               | Пароль   |
|-------|---------------------|----------|
| Админ | admin@modastyle.ru  | password |
| Покупатель | demo@modastyle.ru | password |

## Каталог и фото

```bash
php artisan catalog:sync --prune
php artisan products:sync-images
php artisan shop:cleanup
```

## Полезные команды

| Команда | Описание |
|---------|----------|
| `catalog:sync --prune` | Синхронизация товаров из `database/data/catalog.php` |
| `catalog:sync --append-wb` | Добавить товары с Wildberries |
| `products:sync-images` | Скачать фото товаров |
| `shop:cleanup` | Удалить лишние файлы фото |
| `composer run optimize` | Кэш конфигурации для продакшена |

## Функциональность

- Каталог, корзина, заказы, профиль
- Админ-панель (`/admin`)
- AI-чат поддержки
- Импорт каталога с Wildberries

## Стек

- Laravel 13, Blade, Bootstrap 5, SQLite
