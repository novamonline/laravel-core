<?php

namespace Core\Dev\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class MakeTraitCommand extends Command
{
    /**
     * The name  of the console command.
     *
     * @var string
     */
    protected $name = "make:trait";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:trait
        {name : name of the trait to be created}
        {--p|path= : Location where the trait will be added to}
        {--namespace= : The namespace to create the Trait in}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait in the specified folders';

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

        $trait = $this->argument('name');
        $path  = $this->option('path') ?: 'app/Traits';
        $path  = base_path($path);

        $traitPath = $path .$DS. $trait;
        $traitName = trim(ucwords(dirname($trait)), '.');

        $namespace = ($this->option('namespace') ?: 'App\\Traits') ."\\";
        $namespace .= preg_replace("#(\\\/)+#msi","\\", $traitName);

        $stub = base_path('core/lib/stubs/Trait.stub');


        $traitDir  = pathinfo($traitPath, PATHINFO_DIRNAME);
        $traitName = pathinfo($traitPath, PATHINFO_BASENAME);


        $traitDir = str_replace(['/','\\'], $DS, $traitDir);
        if (!file_exists($traitDir)) {
            mkdir($traitDir, 0775, true);
        }

        $traitPath = $traitPath .'.php';
        if(file_exists($traitPath)){
            return $this->line("... Trait, '$trait' already exists!");
        }

        $STUB_DATA = file_get_contents($stub);

        $TRAIT_DATA = str_replace([
            '$TRAIT_NAME$', '$TRAIT_SPACE$'
        ], [
            $traitName, trim($namespace,'/\\')
        ], $STUB_DATA);

        file_put_contents($traitPath, $TRAIT_DATA);

        $cleanPath = trim(Str::after($path, base_path()), '/\\');

        $this->line("New trait '$trait' created in '$cleanPath' successfully");
    }
}
