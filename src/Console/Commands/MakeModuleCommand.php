<?php

namespace Palax\LaravelHelpers\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final class MakeModuleCommand extends Command
{
    protected Filesystem $files;

    protected $signature = 'make:module {name}
                                        {--all}
                                        {--migration}                                      
                                        {--controller}
                                        {--model}
                                        {--service}
                                        {--repository}                                      
                                        {--request}';

    protected $description = 'Создать новый модуль';

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->files = $filesystem;
    }

    /**
     * @throws FileNotFoundException
     */
    public function handle(): void
    {

        if ($this->option('all')) {
            $this->input->setOption('controller', true);
            $this->input->setOption('model', true);
            $this->input->setOption('service', true);
            $this->input->setOption('repository', true);
            $this->input->setOption('request', true);
        }
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));
        if ($this->option('controller')) {
            $this->createController();
        }

        if ($this->option('model')) {
            $this->createModel();
        }

        if ($this->option('service')) {
            $serviceName = "{$modelName}Service";
            $this->createServiceClass($serviceName);
        }

        if ($this->option('request')) {
            $this->createRequestClasses($modelName);
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }
        if ($this->option('repository')) {
            $this->createRepository();
        }
    }

    private function createModel(): void
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        if ($this->confirm('Создать модель с миграцией ? ', true)) {
            $this->call('make:model', [
                'name' => 'App\\Modules\\'.trim($this->argument('name')).'\\Models\\'.$model,
                '--migration' => true,
            ]);
        } else {
            $this->call('make:model', [
                'name' => 'App\\Modules\\'.trim($this->argument('name')).'\\Models\\'.$model,
            ]);
        }
    }

    /**
     * @throws FileNotFoundException
     */
    private function createController(): void
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->getApiControllerPath($this->argument('name'));

        $serviceName = $controller;
        if ($this->alreadyExists($path)) {
            $this->error("{$controller}Controller already exists.");
        } else {
            $this->makeDirectory($path);
            $stub = null;
            try {
                $stub = $this->files->get(base_path('stubs/controller.model.api.stub'));
            } catch (FileNotFoundException $e) {
                $this->error($e->getMessage());
            }

            $stub = str_replace(
                [
                    '{{ namespace }}',
                    '{{ rootNamespace }}',
                    '{{ class }}',
                    '{{ ServiceClass }}',
                    '{{ FullServiceClass }}',
                    '{{ indexRequest }}',
                    '{{ storeRequest }}',
                    '{{ updateRequest }}',
                ],
                [
                    'App\\Modules\\'.str_replace('/', DIRECTORY_SEPARATOR, $this->argument('name')).'\\Controllers\\Api',
                    $this->laravel->getNamespace(),
                    $controller.'Controller',
                    $serviceName.'Service',
                    'App\\Modules\\'.str_replace('/', DIRECTORY_SEPARATOR, $this->argument('name'))."\\Services\\$serviceName".'Service',
                    $serviceName.'IndexRequest',
                    $serviceName.'StoreRequest',
                    $serviceName.'UpdateRequest',
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info("{$controller}Controller created successfully.");
        }
        $this->updateModularConfig();
        $this->createApiRoutes($controller, $modelName);
        $this->createRequestClasses($serviceName);
        $this->createServiceClass($serviceName);
    }

    private function createMigration(): void
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));

        try {
            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
                '--path' => 'App\\Modules\\'.trim($this->argument('name')).'\\Migrations',
            ]);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function createApiRoutes(string $controller, string $modelName): void
    {
        $routePath = $this->getApiRoutesPath($this->argument('name'));

        if ($this->alreadyExists($routePath)) {
            $this->error("{$modelName}Route already exists.");
        } else {
            $this->makeDirectory($routePath);
            $stub = null;
            try {
                $stub = $this->files->get(base_path('stubs/routes.api.stub'));
            } catch (FileNotFoundException $e) {
                $this->error($e->getMessage());
            }

            $stub = str_replace(
                [
                    '{{ Class }}',
                    '{{ RoutePrefix }}',
                    '{{ ModelVariable }}',
                    '{{ model }}',
                ],
                [
                    'Api\\'.$controller.'Controller',
                    Str::singular(Str::snake(lcfirst($modelName), '-')),
                    '{id}',
                    '{'.$modelName.'}',

                ],
                $stub
            );

            $this->files->put($routePath, $stub);
            $this->info("{$modelName}Route created successfully.");
        }
    }

    private function getApiControllerPath(string $argument): string
    {
        $controller = Str::studly(class_basename($argument));

        return $this->laravel['path'].'/Modules/'.str_replace('\\', DIRECTORY_SEPARATOR, $argument).'/Controllers/Api/'."{$controller}Controller.php";
    }

    private function alreadyExists(string $path): bool
    {
        return $this->files->exists($path);
    }

    private function makeDirectory(string $path): void
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    private function getApiRoutesPath(string $argument): string
    {
        return $this->laravel['path'].'/Modules/'.str_replace('\\', DIRECTORY_SEPARATOR, $argument).'/Routes/api.php';
    }

    /**
     * @throws FileNotFoundException
     */
    private function updateModularConfig(): void
    {
        $group = explode('/', $this->argument('name'))[0];
        $module = Str::studly(class_basename($this->argument('name')));

        $modular = $this->files->get(base_path('config/modular.php'));

        $matches = [];
        $pattern = "/'modules' => \[.*?'{$group}' => \[(.*?)]/s";
        preg_match($pattern, $modular, $matches);

        if (count($matches) == 2) {
            if (! preg_match("/'{$module}'/", $matches[1])) {
                $pattern = "/('modules' => \[.*?'{$group}' => \[)/s";
                $parts = preg_split($pattern, $modular, 2, PREG_SPLIT_DELIM_CAPTURE);
                if (count($parts) == 3) {
                    $configStr = $parts[0].$parts[1]."\n            '$module',".$parts[2];
                    $this->files->put(base_path('config/modular.php'), $configStr);
                }
            }
        }
    }

    private function createRequestClasses(string $modelName): void
    {
        $requestTypes = ['Index', 'Store', 'Update'];

        foreach ($requestTypes as $requestType) {
            $requestClassName = "{$modelName}{$requestType}Request";
            $requestPath = $this->laravel['path'].'/Modules/'.str_replace('\\', DIRECTORY_SEPARATOR, $this->argument('name'))."/Requests/{$requestClassName}.php";

            if (! $this->alreadyExists($requestPath)) {
                $this->makeDirectory($requestPath);

                $stub = null;
                try {
                    $stub = $this->files->get(base_path('stubs/request.stub'));
                } catch (FileNotFoundException $e) {
                    $this->error($e->getMessage());
                }

                $stub = str_replace(
                    [
                        '{{ namespace }}',
                        '{{ class }}',
                    ],
                    [
                        'App\\Modules\\'.str_replace('/', DIRECTORY_SEPARATOR, $this->argument('name')).'\\Requests',
                        $requestClassName,
                    ],
                    $stub
                );

                $this->files->put($requestPath, $stub);
                $this->info("{$requestClassName} created successfully.");
            } else {
                $this->error("{$requestClassName} already exists.");
            }
        }
    }

    private function createServiceClass(string $modelName): void
    {
        $serviceClassName = "{$modelName}Service";
        $servicePath = $this->laravel['path'].'/Modules/'.str_replace('\\', DIRECTORY_SEPARATOR, $this->argument('name'))."/Services/{$serviceClassName}.php";

        if (! $this->alreadyExists($servicePath)) {
            $this->makeDirectory($servicePath);

            $stub = null;
            try {
                $stub = $this->files->get(base_path('stubs/serviceClass.stub'));
            } catch (FileNotFoundException $e) {
                $this->error($e->getMessage());
            }

            $stub = str_replace(
                [
                    '{{ namespace }}',
                    '{{ class }}',
                ],
                [
                    'App\\Modules\\'.str_replace('/', DIRECTORY_SEPARATOR, $this->argument('name')).'\\Services',
                    $serviceClassName,
                ],
                $stub
            );

            $this->files->put($servicePath, $stub);
            $this->info("{$serviceClassName} created successfully.");
        } else {
            $this->error("{$serviceClassName} already exists.");
        }
    }

    private function createRepository(): void
    {
        $repository = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $repositoryClassName = "{$repository}Repository";

        $path = $this->laravel['path'].'/Modules/'.str_replace('\\', DIRECTORY_SEPARATOR, $this->argument('name'))."/Repository/{$repositoryClassName}.php";

        if (! $this->alreadyExists($path)) {
            $this->makeDirectory($path);

            $stub = null;
            try {
                $stub = $this->files->get(base_path('stubs/repository.stub'));
            } catch (FileNotFoundException $e) {
                $this->error($e->getMessage());
            }

            $stub = str_replace(
                [
                    '{{ namespace }}',
                    '{{ class }}',
                ],
                [
                    'App\\Modules\\'.str_replace('/', DIRECTORY_SEPARATOR, $this->argument('name')).'\\Repository',
                    $repositoryClassName,
                ],
                $stub
            );

            $this->files->put($path, $stub); // исправлено с $repositoryClassName на $path
            $this->info("{$repositoryClassName} created successfully.");
        } else {
            $this->error("{$repositoryClassName} already exists.");
        }
    }
}
