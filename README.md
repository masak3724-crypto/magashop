# ModaStyle

Интернет-магазин модной одежды, обуви и аксессуаров на **Laravel 13**: каталог, корзина, заказы, профиль пользователя и админ-панель.

## Требования

- PHP 8.2+
- Composer
- SQLite (по умолчанию) или MySQL

## Установка и запуск

```bash
cd autoclub
composer install
cp .env.example .env   # если файла .env ещё нет
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Откройте в браузере: http://127.0.0.1:8000

## Демо-аккаунт

- **Email:** demo@modastyle.ru  
- **Пароль:** password  

## Функциональность

- Главная страница и каталог (одежда / обувь / аксессуары)
- Карточка товара, корзина, оформление и оплата заказа (имитация)
- Регистрация, вход, профиль, история заказов
- Доставка с примеркой, возврат, программа лояльности
- AI-чат поддержки, страницы «О нас» и «Контакты»

## Стек

- Laravel 13, Blade, Bootstrap 5, SQLite
