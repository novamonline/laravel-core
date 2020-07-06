<?php

namespace Core\Dev\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:install
                            {--f|force : Re-write initial resources during install}
                            {--r|reset : Delete initial resources and re-install}
                            {--d|db : Create all configured databases}
                            {--N|newdb : Create new local database}
                            {--m|migrate : Run migrations after database creation}
                            {--s|seed : Run seeds after migrations}
                            {--u|upgrade : Upgrade composer packages (not included in --all)}
                            {--a|app : Run installation of the application}
                            {--A|all : Run this command with ALL the options!}
                            {--S|serve : Run a local server after completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the application/resources on this server';

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
        $options = $this->options();

        $this->line('======================================================');

        $this->call('optimize');
        $this->call('cache:clear');

        if ($options['app'] || $options['all']) {
            $this->install_app($options);
        } else {
            shell_exec('composer dump-autoload');
        }

        if ($options['db'] || $options['all']) {
            $this->install_db($options);
        }

        if ($options['migrate'] || $options['all']) {
            $this->migrate_db($options);
        }

        if ($options['serve']) {
            $this->init_server($options);
        }

        $this->call('optimize');

        $this->line('======================================================');
//        exit();
        return false;
    }

    public function update_autoloads()
    {
        $this->line("Updating autoload files first!' ...  ");
        shell_exec('composer dump-autoload');
        $this->line('');
    }

    public function install_app($options)
    {
        $this->line('-----------------------------------------------------');
        $this->line("  Intalling/Updating 3rd-party packages' ...  ");
        $this->line('-----------------------------------------------------');

        $force = '';
        if ($options['force']) {
            $force = ' -f';
            shell_exec("git clean . -fdX");
            if (file_exists($env =  base_path('.env'))) {
                unlink($env);
            }
        }
        $action = isset($options['upgrade'])? 'update': 'install';
        shell_exec("composer $action");

        if ($options['reset'] || $options['all']) {
            $this->line('-------------------------------------');
            $this->line("   Adding initial resources...  ");
            $this->line('-------------------------------------');

            shell_exec('composer run-script post-root-package-install');
            shell_exec('composer run-script post-create-project-cmd');
            if(file_exists(base_path('vendor/laravel/passport'))){
                $this->call('passport:install');
            }
            $this->line(' ');
        }
    }

    public function connect_db($conn = null)
    {
        return  DB::connection($conn ?: 'root');
    }

    public function disconnect_db($conn = 'root')
    {
        // DB::disconnect($conn);
    }

    public function install_db($options)
    {
        $this->line('-------------------------------------------');
        $this->line('  Initialize the database  ');
        $this->line('--------------------------------------------');



        if ($options['newdb']) {
            $database = $this->ask('What is your database name?') ?? 'popcxdb00';
            $username = $this->ask('What is your database username?') ?? 'popcxusr00';
            $dbport = $this->ask('What is your database port?') ?? '3306';
            $password = $this->secret('What is your database password?') ?? 'P0pcxU$r00';
            $this->local_db($database, $username, $dbport, $password);
        } else {
            $CONNECTION = $this->connect_db('root');

            if ($CONNECTION) {
                $this->create_databases($CONNECTION);
                $this->disconnect_db('root');
            } else {
                $this->line('Database connection failed: '.mysqli_connect_error());
            }
        }
        $this->line('Done!');
        $this->line('');
    }

    public function local_db($database, $username, $dbport, $password)
    {
        $env_data = file_get_contents($env_file = base_path('.env'));
        if ($database) {
            $env_data = preg_replace("#(DB_DATABASE\=)(.+?)\s+#si", "$1$database\n", $env_data);
        }
        if ($username) {
            $env_data = preg_replace("#(DB_USERNAME\=)(.+?)\s+#si", "$1$username\n", $env_data);
        }
        if ($password) {
            $env_data = preg_replace("#(DB_PASSWORD\=)(.+?)\s+#si", "$1$dataport\n", $env_data);
        }
        if ($dbport) {
            $env_data = preg_replace("#(DB_PORT\=)(.+?)\s+#si", "$1$dataport\n", $env_data);
        }
        file_put_contents($env_file, $env_data);
    }

    public function create_databases($CONNECTION)
    {
        $DS = DIRECTORY_SEPARATOR;

        $DATABASES = array_filter(config('database.connections'));

        if (file_exists($modules = base_path('modules'))) {
            foreach (glob($modules.$DS."*") as $module_path) {
                $module_name = strtolower(basename($module_path));
                $module_conf = config("{$module_name}.database.connections");
                $DATABASES += array_filter($module_conf ?: []);
            }
        }

        $this->line("Creating database(s)...");
        $CreatedDB = [];
        foreach ($DATABASES as $n => $db) {
            $cdb = $db['database'] ?? null;
            if(!$cdb){
                continue;
            }
            if (is_null($CreatedDB) || !\in_array($cdb, $CreatedDB)) {
                if(Str::contains($n, 'legacy') || Str::contains($cdb, 'legacy')){
                    $this->line("... skipping $cdb");
                    continue;
                }
                $this->init_db($CONNECTION, $db);
            }
            $CreatedDB[] = $cdb;
            $this->add_db_user($CONNECTION, $db);
        }
    }

    public function init_db($CONNECTION, $db)
    {
        $host = $db['host'] ?? "127.0.0.1";
        $port = $db['port'] ?? 3306;
        $database = $db['database'] ?? null;
        $username = $db['username'] ?? null;
        $password = $db['password'] ?? "";
        $charset = $db['charset'] ?? 'utf8mb4';
        $collation = $db['collation'] ?? 'utf8mb4_general_ci';
        $driver = isset($db['driver']) && $db['driver'] == "mysql";

        if ($database && $driver) {
            $this->line("... backing up database '$database' if exists");
            $this->backup_db($db);

            $this->line("... adding database: db='$database'; charset='$charset'; collation='$collation'");
            $CONNECTION->statement("DROP DATABASE IF EXISTS $database;");
            $CONNECTION->statement("CREATE DATABASE $database CHARACTER SET $charset COLLATE $collation;");
        }
    }

    public function add_db_user($CONNECTION, $db)
    {
        $host = $db['host'] ?? "127.0.0.1";
        $port = $db['port'] ?? 3306;
        $database = $db['database'] ?? null;
        $username = $db['username'] ?? null;
        $password = $db['password'] ?? "";
        $charset = $db['charset'] ?? 'utf8mb4';
        $collation = $db['collation'] ?? 'utf8mb4_general_ci';
        $driver = isset($db['driver']) && $db['driver'] == "mysql";

        if ($database && $username && $password && $driver) {
            $privileges = "ALL PRIVILEGES";
            if(Str::startsWith($username, 'dbr_')){
                $privileges = "SELECT";
            } elseif(Str::startsWith($username, 'dbw_')){
                $privileges = "SELECT,INSERT,UPDATE,DELETE";
            } else{
                $database = "*";
            }
            $this->line("... adding user: username='$username', password='$password'; grant='$privileges' to $database");
            $CONNECTION->statement("CREATE USER IF NOT EXISTS '$username'@'%' IDENTIFIED BY '$password';");

            $CONNECTION->statement("GRANT $privileges ON $database.* TO '$username'@'%';");
            $CONNECTION->statement('FLUSH PRIVILEGES;');
        }
    }

    public function backup_db($db)
    {
        $rootdb = config('database.connections.root');
        if(!$rootdb){
            $continue = $this->ask('Root database confirguration doesn not exist! Continue? Y or N', 'Y');
            if(Str::startswith(strtolower($continue),'n')) die();
        }
        extract($rootdb);
        if($password) $password = "-p'$password'";

        if(!file_exists($backup_path = storage_path("data/backups/bak-".date('Ymd')))){
            mkdir($backup_path, 0644, true);
        }

        $backup = "$backup_path/db-".date('His').".sql";

        $database = $db['database'];

        $backup_passed = shell_exec("mysqldump -h $host -u $username -P $port $password $database > $backup 2>&1");

        if(!($backup = realpath($backup))){
            $this->line('Backup creation was NOT successful!');
            $continue = $this->ask('We will still drop the database. Continue? Y or N', 'Y');
            if(Str::startswith(strtolower($continue),'n')) die();
        }
        $clean_backup_path = Str::after($backup, base_path());
        $this->line("");
        $this->line("Backup created in $clean_backup_path!");
        $continue = $this->ask("Check that the backup is valid! Continue? Y or N", 'Y');
        if(Str::startswith(strtolower($continue),'n')) die();

        $this->line("... backup complete!'");
    }

    public function migrate_db($options)
    {
        $this->line('-------------------------------------------------');
        $this->line('  Run database migrations ');
        $this->line('-------------------------------------------------');

        $migrations = base_path($dbmig = 'database/migrations');
        $this->runMigrations($dbmig);

        // $this->line("Run in $dbmig");
        // $this->call("migrate:refresh", [
        //     '--path' => $dbmig
        // ]);
        // $this->line("");
        foreach (glob($migrations.'/*') as $migration) {
            if ($migration == "." || $migration == '..') {
                continue;
            }
            if (is_file($migration)) {
                continue;
            }


            $name = basename($migration);
            $this->runMigrations($dbmig, $name);
            // $conn =  config('database.connections');

            // $props = ["--path" => "$dbmig/$name"];
            // if (in_array("db_$name", $conn)) {
            //     $props += ["--database" => "db_$name"];
            // }
            // $this->call("migrate:refresh", $props);
            // $this->line("");
        }
        if (file_exists(base_path('modules'))) {
            $this->call("module:migrate");
        }

        if ($options['seed'] || $options['all']) {
            $this->line("------------------------------------------------");
            $this->line('Running database seeds');
            $this->line('------------------------------------------------');
            $this->call('db:seed', ['-vv' => true]);
            if (file_exists(base_path('modules'))) {
                $this->call('module:seed');
            }
        }
        $this->line('');
    }

    public function runMigrations($migrations, $name = null, $module = false)
    {
        $this->connect_db($conn = $name ? "db_$name": null);

        $migrations = "{$migrations}/{$name}";
        $connections =  config('database.connections');

        $props = ["--path" => $migrations];
        if ($name && in_array("db_$name", $connections)) {
            $props += ["--database" => "db_$name"];
        }

        $this->line("Run in $migrations");
        if ($module) {
            $this->call("module:migrate-reset $name", $props);
        } else {
            $this->call("migrate:fresh", $props);
        }
        $this->line("");
        $this->disconnect_db($conn);
    }

    public function init_server($options = null)
    {
        $this->line('---------------------------------------');
        $this->line('  Initializing local server');
        $this->line('---------------------------------------');
        $this->line("Setting up port ".$port = mt_rand(1111, 9999));
        $host = '127.0.0.1';
        shell_exec("start \"\" http://$host:$port");
        $this->call("serve", ['--host'=>$host, '--port'=>$port]);
        $this->line('');
    }
}
