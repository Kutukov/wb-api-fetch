
Для работы нужно вставипь API ключ в файл .env параметр WB_API_KEY

Основные консольные команды запускаются из корня проекта

php artisan migrate - запуск миграций
php artisan wb:csvImport {table} {--from=} {--to=} - берёт данные из API и добавляет их в CSV файл по пути storage/app/raw_data
php artisan wb:fetch {table} {--from=} {--to=} - берёт данные из API и добавляет их в БД
php artisan app:csvToMigration {csv} {--table=} - создаёт черновую миграцию из CSV файла

аргументы:
{table} – имя таблицы
{--from=} - дата начала выборки (необязательно, по умолчанию вчерашняя дата)
{--to=} - дата конца выборки (необязательно, по умолчанию текущая дата)
{--csv=} - путь к CSV файлу

Всего есть доступ к 5 таблицам:
incomes - Доходы
migrations - Применённые миграции
orders - Заказы 
sales - Продажи
stocks - Доходы