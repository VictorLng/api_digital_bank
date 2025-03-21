<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeFullStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:full-structure {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria Controller, Model, Repository, BO e Interface para uma entidade';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $this->createModel($name);
        $this->createController($name);
        $this->createRepository($name);
        $this->createBO($name);
        $this->createInterface($name);

        $this->info("Estrutura completa criada para: {$name}");
    }

    private function createModel($name)
    {
        $path = app_path("Models/{$name}.php");
        if (!File::exists($path)) {
            File::ensureDirectoryExists(app_path('Models'));
            File::put($path, $this->getModelTemplate($name));
            $this->info("Model criado: {$path}");
        }
    }

    private function createController($name)
    {
        $path = app_path("Http/Controllers/{$name}Controller.php");
        if (!File::exists($path)) {
            File::ensureDirectoryExists(app_path('Http/Controllers'));
            File::put($path, $this->getControllerTemplate($name));
            $this->info("Controller criado: {$path}");
        }
    }

    private function createRepository($name)
    {
        $path = app_path("Repositories/{$name}Repository.php");
        if (!File::exists($path)) {
            File::ensureDirectoryExists(app_path('Repositories'));
            File::put($path, $this->getRepositoryTemplate($name));
            $this->info("Repository criado: {$path}");
        }
    }

    private function createBO($name)
    {
        $path = app_path("BO/{$name}Bo.php");
        if (!File::exists($path)) {
            File::ensureDirectoryExists(app_path('BO'));
            File::put($path, $this->getBOTemplate($name));
            $this->info("BO criado: {$path}");
        }
    }

    private function createInterface($name)
    {
        $path = app_path("BO/Interfaces/{$name}Interface.php");
        if (!File::exists($path)) {
            File::ensureDirectoryExists(app_path('BO/Interfaces'));
            File::put($path, $this->getInterfaceTemplate($name));
            $this->info("Interface criada: {$path}");
        }
    }

    private function getModelTemplate($name)
    {
        return <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    use HasFactory;

    protected \$fillable = [
        // Adicione os campos aqui
    ];
}
EOT;
    }

    private function getControllerTemplate($name)
    {
        return <<<EOT
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BO\\{$name}Bo;

class {$name}Controller extends Controller
{
    protected \${$name}Bo;

    public function __construct({$name}Bo \${$name}Bo)
    {
        \$this->{$name}Bo = \${$name}Bo;
    }

    // Adicione os métodos aqui
}
EOT;
    }

    private function getRepositoryTemplate($name)
    {
        return <<<EOT
<?php

namespace App\Repositories;

class {$name}Repository
{
    // Adicione os métodos aqui
}
EOT;
    }

    private function getBOTemplate($name)
    {
        return <<<EOT
<?php

namespace App\BO;

use App\BO\Interfaces\\{$name}Interface;

class {$name}Bo implements {$name}Interface
{
    // Adicione os métodos aqui
}
EOT;
    }

    private function getInterfaceTemplate($name)
    {
        return <<<EOT
<?php

namespace App\BO\Interfaces;

interface {$name}Interface
{
    // Defina os métodos aqui
}
EOT;
    }
}