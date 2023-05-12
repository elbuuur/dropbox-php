#### Для работы с файлами используется laravel-medialibrary v.10
конфигурация библиотеки лежит по пути _config/media-library.php_ (здесь регулируется размер загружаемого файла)

#### Документация по API методам

Для создания Документации по методам API используется инструмент автоматической генерации документации Swagger.


С помощью команды `sail artisan l5-swagger:generate`
происходит генерация документации. 

Если объявить в файле .env переменную `L5_SWAGGER_GENERATE_ALWAYS=true`, то
документация будет генерироваться автоматически. 

Также в .env файле нужно определить переменную `SWAGGER_API_BASEURL=http://localhost/api`


На данный момент все методы лежат в одном разделе: [http://localhost/api/documentation#/default](http://localhost/api/documentation#/default)
