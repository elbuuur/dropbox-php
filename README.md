1. **Работа с файлами и библиотека laravel-medialibrary v.10**:
    - Конфигурация библиотеки: `_config/media-library.php`.
    - `max_file_size` регулирует размер загружаемого файла.
    - Можно установить собственный лимит, объявив переменную `MAX_FILE_SIZE` в файле `.env`. Значение по умолчанию: 20 МБ.


2. **Документация по API методам**:
    - Генерация документации с помощью команды `sail artisan l5-swagger:generate`.
    - Если переменная `L5_SWAGGER_GENERATE_ALWAYS` в файле `.env` установлена в `true`, документация будет генерироваться автоматически.
    - Необходимо определить переменную `SWAGGER_API_BASEURL` в файле `.env` со значением `http://localhost/api`.
    - Ссылка на документацию: [http://localhost/api/documentation#/](http://localhost/api/documentation#/).


3. **Максимальный лимит объема всех файлов на диске одного пользователя**:
    - Логика описана в `app/Http/Middleware/CheckFileUploadLimitMiddleware.php`.
    - Можно установить собственный лимит, объявив переменную `UPLOAD_LIMIT` в файле `.env`. Значение по умолчанию: 100 МБ.
    - В таблице `users` в столбце `upload_limit` фиксируется вес уже загруженных файлов пользователя.


4. **Крон-файлы**:
    - При загрузке файла можно указать срок хранения файла (поле `shelf_life`).
    - `app/Console/Kernel.php` содержит скрипт, который каждый час проверяет доступные файлы для удаления и помещает их в корзину (Soft deletes).
    - Для выполнения крона нужно выполнить команду `sail artisan schedule:run` в терминале.


5. **Кеширование**:
    - Осуществляется с помощью Redis.
    - Кешируются файлы (по идентификатору на 2 часа) и данные о пользователе (2 часа).
    - Время кеширования можно изменить через `.env` файл с помощью ключей `USER_CACHE_TIME` и `FILE_CACHE_TIME`.
    - Ключи, теги и значения времени кеширования (если не указаны в `.env` файле) определяются в файле `config/constants.php`.
    - Для правильного подключения Redis в `.env` файле необходимо установить значение `CACHE_DRIVER=redis`.


6. **Корзина. Удаление файлов и папок**:
    - Реализовано мягкое удаление моделей (SoftDeletes).
    - Реализована корзина с возможностью восстановления моделей.
    - Содержимое корзины хранится 10 дней (значение можно установить в `.env` файле с помощью переменной `TRASH_LIFESPAN`), затем полностью очищается.

