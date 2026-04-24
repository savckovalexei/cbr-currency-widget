# 📊 CBR Currency Widget

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-24.0-2496ED?logo=docker)](https://docker.com)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis)](https://redis.io)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap)](https://getbootstrap.com)

Веб-приложение для автоматической загрузки, хранения и отображения официальных курсов валют Центрального Банка России. Включает настраиваемый виджет с автообновлением, административную панель управления и REST API.

## ✨ Функциональность

### 📥 Автоматическая загрузка курсов
- Получение данных с официального API ЦБ РФ (`http://www.cbr.ru/scripts/XML_daily.asp`)
- Парсинг XML и сохранение в базу данных
- Настраиваемый список валют для загрузки
- Учёт номинала валют (например, иена за 100 единиц)
- Кэширование через Redis с тегированием


### 📈 Виджет курсов валют
- Отображение актуальных курсов на текущий день
- Визуализация изменений относительно предыдущего дня:
  - 🟢 Зелёный + стрелка вверх — курс вырос
  - 🔴 Красный + стрелка вниз — курс упал
  - ⚪ Серый — без изменений
- Автоматическое обновление с настраиваемым интервалом
- Обратный отсчёт до следующего обновления
- Адаптивный дизайн (Bootstrap 5)
- Плавные анимации при обновлении данных

### ⚙️ Административная панель
- Управление списком валют для загрузки с ЦБ
- Управление списком валют для отображения в виджете
- Настройка интервала автообновления виджета
- Валидация всех полей
- Автоматический сброс кэша при изменении настроек

### 📡 REST API
- `GET /api/rates` — получение курсов валют
- `GET /api/settings` — получение настроек
- `POST /api/settings` — обновление настроек
- Поддержка фильтрации по дате
- JSON-ответы

---

## 🛠 Технологический стек

### Backend
| Технология | Назначение |
|------------|------------|
| **PHP 8.2** | Язык программирования |
| **Laravel 10.x** | Веб-фреймворк |
| **MySQL 8.0** | Реляционная база данных |
| **Redis 7** | Кэширование |
| **Predis** | PHP-клиент для Redis |

### Frontend
| Технология | Назначение |
|------------|------------|
| **Bootstrap 5.3** | CSS-фреймворк |
| **Bootstrap Icons** | Иконки |
| **Vanilla JavaScript** | AJAX-запросы и динамика |

### Инфраструктура
| Технология | Назначение |
|------------|------------|
| **Docker** | Контейнеризация |
| **Docker Compose** | Оркестрация контейнеров |
| **Nginx** | Веб-сервер |
| **phpMyAdmin** | Администрирование БД |

---

## 📋 Системные требования

- **Docker** версии 20.10 или выше
- **Docker Compose** версии 2.0 или выше
- **Git** (для клонирования репозитория)
- 4 ГБ свободной оперативной памяти
- 10 ГБ свободного места на диске

> **Примечание для Windows:** рекомендуется использовать Docker Desktop с WSL2

---

## 🚀 Быстрый старт
### 1️⃣ Клонирование репозитория

`git clone https://github.com/savckovalexei/cbr-currency-widget.git`


`cd cbr-currency-widget`

### 2️⃣ Настройка окружения

# Копируем файл окружения
`cp .env.example .env`

#### Для Windows:
#### copy .env.example .env
**Важно: проверьте содержимое .env, особенно:**

DB_HOST=mysql          # Имя сервиса MySQL в Docker

DB_DATABASE=cbr_currencies

DB_USERNAME=root

DB_PASSWORD=root_password


REDIS_HOST=redis       # Имя сервиса Redis в Docker

REDIS_CLIENT=predis


CACHE_DRIVER=redis

APP_TIMEZONE=Europe/Moscow

### 3️⃣ Запуск Docker-контейнеров

#### Сборка образов и запуск контейнеров
`docker-compose up -d --build`

#### Проверка статуса
`docker-compose ps`

#### Ожидаемый результат: 5 контейнеров со статусом Up:

cbr_app — PHP приложение

cbr_nginx — Веб-сервер

cbr_mysql — База данных (healthy)

cbr_redis — Кэш

cbr_phpmyadmin — Админка БД

### 4️⃣ Инициализация приложения
#### Установка зависимостей
`docker-compose exec -u root app composer install --no-dev --no-interaction`
#### Дайте права
`docker-compose exec -u root app chown -R www-data:www-data /var/www/vendor`
#### Генерация ключа приложения
`docker-compose exec app php artisan key:generate`

#### Создание таблиц и заполнение тестовыми данными
`docker-compose exec app php artisan migrate:fresh --seed`

Что делает сидер:

Создаёт 5 валют (USD, EUR, CNY, GBP, JPY)

Генерирует 30 дней исторических курсов

Устанавливает настройки по умолчанию

### 5 Первая загрузка актуальных курсов

`docker-compose exec app php artisan app:fetch-cbr-rates`

### 6 Открытие приложения

| Сервис | URL | Описание |
|:---------|:---------|:---------|
| Виджет   | http://localhost:8080/widget   | Публичный виджет курсов   |
| Админ-панель   | http://localhost:8080/admin/settings   | Управление настройками   |
| phpMyAdmin   | http://localhost:8081   | Управление БД   |
| API   | http://localhost:8080/api/rates   | REST API курсов   |

**Доступ к phpMyAdmin:**

Сервер: mysql

Пользователь: root

Пароль: root_password


**REST API**

Получение курсов валют:
`GET /api/rates`
`GET /api/rates?date=2026-04-24`
Параметры запроса:
| Параметр | Тип | Обязательный |Описание|
|:---------|:---------|:---------|:-------|
| date   | string   | Нет   |   Дата в формате YYYY-MM-DD. По умолчанию — сегодня   |

Пример успешного ответа (200):

[
    {
        "char_code": "USD",
        "name": "Доллар США",
        "value": 96.5432,
        "previous": 95.8721,
        "change": 0.6711,
        "trend": "up"
    },
    {
        "char_code": "EUR",
        "name": "Евро",
        "value": 104.2189,
        "previous": 104.5467,
        "change": -0.3278,
        "trend": "down"
    }
]

Получение настроек:

`GET /api/settings`
Пример ответа (200):
`{"update_interval": 30}`

Обновление настроек
`POST /api/settings
Content-Type: application/json`

{
    "update_interval": 60
}

Параметры запроса:

| Параметр | Тип | Обязательный |Описание|
|:---------|:---------|:---------|:-------|
| update_interval| integer   | Да|   Интервал обновления в секундах (мин. 5)   |

Пример ответа (200):

{
    "message": "Настройки обновлены",
    "update_interval": 60
}

Пример ошибки валидации (422):

{
    "message": "Интервал должен быть целым числом",
    "errors": {
        "update_interval": ["Минимальный интервал — 5 секунд"]
    }
}

**Примеры curl-запросов:**



Получить курсы на сегодня
`curl http://localhost:8080/api/rates`

 Получить курсы на конкретную дату
`curl "http://localhost:8080/api/rates?date=2026-04-20"`

 Получить настройки
`curl http://localhost:8080/api/settings`

 Обновить интервал
`curl -X POST http://localhost:8080/api/settings \
  -H "Content-Type: application/json" \
  -d '{"update_interval": 45}'`

⚙️ **Автоматизация загрузки курсов**

#### Консольная команда
 Ручной запуск

`docker-compose exec app php artisan app:fetch-cbr-rates`

#### Принудительное обновление (игнорируя кэш)
`docker-compose exec app php artisan app:fetch-cbr-rates --force`

Настройка Cron
Добавте в crontab:
##### Каждый час
`0 * * * * docker exec cbr_app php artisan app:fetch-cbr-rates >> /var/log/cbr-fetch.log 2>&1`

##### Или каждые 30 минут в рабочее время ЦБ (10:00-17:00 МСК)
`*/30 10-17 * * 1-5 docker exec cbr_app php artisan app:fetch-cbr-rates >> /var/log/cbr-fetch.log 2>&1`

#### Настройка Laravel Scheduler

Добавьте системный cron для запуска планировщика:
`* * * * * docker exec cbr_app php artisan schedule:run >> /dev/null 2>&1`