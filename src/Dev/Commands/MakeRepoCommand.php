<?php

namespace Core\Dev\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

class MakeRepoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository
    {name : name of the repository to be created}
    {module? : name of the modules to be created in}
    {--p|path= : Location where the repository will be added to}
    {--namespace= : The namespace to create the repository in}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $DS = DIRECTORY_SEPARATOR;

        $repository = $this->argument('name');
        $ModuleName = $this->argument('module');

        if(!$ModuleName){

            $path  = $this->option('path') ?: 'app/Http/Repositories';
            $path  = base_path($path);

            $repositoryPath = $path .$DS. $repository;

            $repositoryDir  = pathinfo($repositoryPath, PATHINFO_DIRNAME);

            $namespace = trim($this->option('namespace') ?: 'App\\Http\\Repositories', '/\\');
            $namespace .= '\\'. str_replace(['//', '/', '\\', $DS], '\\', dirname($repository));


        } else {
            $module = Module::find($ModuleName);

            $path = $module->getPath(). "{$DS}Http{$DS}Repositories";

            $repositoryPath = $path .$DS. $repository;

            $repositoryDir  = pathinfo($repositoryPath, PATHINFO_DIRNAME);

            $namespace = "Modules\\" . $module->getStudlyName();
            $namespace .= Str::after($repositoryDir, $ModuleName);
            $namespace = trim($namespace, '/\\.');
        }


        $repositoryName = pathinfo($repositoryPath, PATHINFO_BASENAME);

        $stub = base_path('stubs/custom/Repository.stub');
        $repositoryDir = str_replace(['/','\\'], $DS, $repositoryDir);


        if (!file_exists($repositoryDir)) {
            mkdir($repositoryDir, 0775, true);
        }

        $repositoryPath = $repositoryPath .'.php';

        if (file_exists($repositoryPath)) {
            return $this->line("... repository, '$repository' already exists!");
        }

        $searchNreplace = [
            '$REPO_NAME$' => $repositoryName,
            '$REPO_SPACE$' => ucfirst(trim($namespace, '/\\.')),
        ];
        $search = array_keys($searchNreplace);
        $replace = array_values($searchNreplace);

        $STUB_DATA = file_get_contents($stub);
        $REPO_DATA = str_replace($search, $replace, $STUB_DATA);
        file_put_contents($repositoryPath, $REPO_DATA);

        $this->line("$repository created in $path");
    }
}
