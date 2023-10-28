# Описание 

Пакет предоставляет набор компонентов постоянно необходимых в работе и упрощающих разработку

#### Tizix\LaravelHelpers\Helpers\\*

- `BaseRepository` - Базовый класс для Репозитория в при использовании  Repository-Service pattern
- `BaseRequest` - Базовый класс Request
- `ResponseHelper` - класс, ответа 

1. Для работы сервиса в системе, требуется его подключить в `config/app.php`
```php
'providers' => [
    // ...
    Tizix\LaravelHelpers\Providers\ModularRouteServiceProvider::class,
]

```
2. Публикация пакета выполняется через консольную команду

```shell script
php artisan vendor:publish --provider="Tizix\LaravelHelpers\Providers\ModularRouteServiceProvider"
```
3. В Папке config публикуется конфиг `modular.php`
```php
<?php

return [
    'path' => base_path().'/app/Modules',// Корневая папка модуля
    'base_namespace' => 'App\Modules', // Корневая папка модуля
    'groupWithoutPrefix' => 'Pub',
    'groupMiddleware' => [

    ], // тут можно регистрировать Middleware
    'modules' => [
        'Admin' => [], // Название модуля Admin для авторизированных пользователь 
        'Pub' => [],  // Название модуля Pub для 
    ],
];
```


4. Создание нового модуля выполняется через консольную команду виды команд `--controller --model --migration  --service --repository --request`
```shell script
php artisan make:module  Admin/User --all 
```
В проекте используется модульная архитектура приложения.
<pre><code>
app/
 ├──Modules/
  ├── Name module<span></span>/
    ├── Controllers/
    ├── Models/
    ├── Migrations/
    ├── Routes/
    ├── Requests/
    ├── Services/
    ├── Repository/
    ├── DTO/
</code></pre>