# telegram-bot
"Телеграм бот для информирования о новых заказах"
Это проект для реализации telegram бота, который будет отслеживать все заказы в заведении и
присылать уведомления о поступлении новых заказов.

## Требования
- PHP 8.1+
- Laravel 10
- MySQL 8
- Composer
- Docker

## Установка и запуск через Docker

### 1. Склонируйте репозиторий, установите зависимости:

git clone https://github.com/Alexx1088/telegram-bot.git

установить зависимости через Composer, сгенерировать application key:

composer install
php artisan key:generate

### 2.  Собрать и запустить контейнеры:

docker-compose up --build -d

### 3.Скопировать файл .env.example в .env

### 4. Проверить настройки базы данных и убедится, что они соответствуют данным из docker-compose.yml

### 5. Выполнить миграции

docker exec -it bot php artisan migrate 

### 6. Администрирование базы данных (веб-приложение Adminer):

Adminer доступен по адресу: http://localhost:8093.

Сервер: db_bot
Логин: root
Пароль: root
База данных: bot





## Основная функциональность

    • взаимодействие с пользователем происходит посредством HTTP запросов к API серверу с использованием статического API ключа
    • получение списка всех организаций находящихся в конкретном здании
    • получение списка всех организаций, которые относятся к указанному виду деятельности
    • получение списка организаций, которые находятся в заданном радиусе/прямоугольной области относительно указанной точки на карте. список зданий
    • вывод информации об организации по её идентификатору
    • поиск организации по виду деятельности. Например, поиск по виду деятельности «Еда», которая находится на первом уровне дерева, и чтобы нашлись все организации, которые относятся к видам деятельности, лежащим внутри. Т.е. в результатах поиска должны отобразиться организации с видом деятельности Еда, Мясная продукция, Молочная продукция.
    • поиск организации по названию

## REST API Методы

POST /api/login — аутентификация пользователя, получение статического API ключа(пароль "password", email взять из
таблицы пользователей).
GET /api/buildings/{id?}/organizations — список всех организаций находящихся в конкретном здании.
GET /api/activities/{id?}/organizations — список всех организаций, которые относятся к указанному виду деятельности.
GET /api/organizations/rectangle — список организаций в соответствующих зданиях, которые находятся в
заданной прямоугольной области относительно указанной точки на карте.
GET /api/organization/{organization_id} - вывод информации об организации по её идентификатору
GET /api/activities/{activity_id}/organizations_list - список всех организаций, которые относятся к указанному виду
деятельности, вместе с видами деятельности, лежащими внутри корневого вида.
GET /api/organizations/search - поиск организации по названию

## Контакты

Если у вас есть вопросы по проекту, вы можете связаться со мной:

telegram: @alexx108
