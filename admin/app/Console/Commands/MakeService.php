<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');
        $fileSystem = new Filesystem();

        $path = app_path("Services/{$name}.php");

        if ($fileSystem->exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        $stub = <<<EOT
        <?php

        namespace App\Services;

        class {$name}
        {
            //
        }
        EOT;

        $fileSystem->put($path, $stub);
        $this->info("Service {$name} created successfully!");
    }
}
