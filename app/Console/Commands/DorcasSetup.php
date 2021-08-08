<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use App\Http\Controllers\Setup\Init as AuthInit;
use App\Http\Controllers\Auth\Register as AuthRegister;

use Illuminate\Support\Facades\DB;

class DorcasSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dorcas:setup {--database=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Dorcas Installation';

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
    public function handle($options = "")
    {

        // $value = $this->argument('name');
        // // and
        // $value = $this->option('name');
        // // or get all as array
        // $arguments = $this->argument();
        // $options = $this->option();

        // default setup
        $database_old = $this->option('database');
        $database = getenv('DB_DATABASE');


        $databaseHub = getenv('DB_HUB_DATABASE');

        $this->info('Checking / Creating HUB Database');
        if (!$databaseHub) {
            $this->info('Skipping creation of database as env(DB_DATABASE) is empty');
            return;
        }

        try {
            $conn = mysqli_connect(getenv('DB_HUB_HOST'), getenv('DB_HUB_USERNAME'), getenv('DB_HUB_PASSWORD'));

            if (!$conn) {
                die("Connection to HUB failed: " . mysqli_connect_error());
            }
            
            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS `" . $databaseHub . "`";
            if (mysqli_query($conn, $sql)) {
                $this->info(sprintf('Successfully created %s database', $databaseHub));
            } else {
                $this->error(sprintf('Error creating %s database, %s', $databaseHub, mysqli_error($conn)));
            }
            
            mysqli_close($conn);

        } catch (Exception $exception) {
            $this->error(sprintf('Failed to create %s database, %s', $database, $exception->getMessage()));
        }


        $this->info('Importing HUB database...');
        try {
            $filename = resource_path('hub.sql');
            # get the filename
            if (!file_exists($filename)) {
                throw new FileNotFoundException('Could not find the hub.sql database file at: '.$filename);
            }
            if (!is_readable($filename)) {
                throw new FileException('The core.sql ('.$filename.') file is not readable by the process.');
            }


            $connImport = mysqli_connect(getenv('DB_HUB_HOST'), getenv('DB_HUB_USERNAME'), getenv('DB_HUB_PASSWORD'));

            if (!$connImport) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $sql = "USE `" . $databaseHub . "`";
            if (mysqli_query($connImport, $sql)) {
                $this->info(sprintf('Successfully selected %s database', $databaseHub));
            } else {
                $this->error(sprintf('Error selecting %s database, %s', $databaseHub, mysqli_error($connImport)));
            }

            $queryLines = 0;
            $tempLine = '';
            // Read in the full file
            $lines = file($filename);
            // Loop through each line
            foreach ($lines as $line) {

                // Skip it if it's a comment
                if (substr($line, 0, 2) == '--' || $line == '')
                    continue;

                // Add this line to the current segment
                $tempLine .= $line;
                // If its semicolon at the end, so that is the end of one query
                if (substr(trim($line), -1, 1) == ';')  {
                    // Perform the query
                    mysqli_query($connImport, $tempLine) or $this->error(sprintf("Error in " . $tempLine .": %s", mysqli_error($connImport)));
                    
                    // Reset temp variable to empty
                    $tempLine = '';
                    $queryLines++;
                }
            }

            $this->info(sprintf('%s SQL lines imported successfully to HUB', $queryLines));

            mysqli_close($connImport);

        } catch (Exception $exception) {
            $this->error(sprintf('Failed to import HUB database: %s', $exception->getMessage()));
        }



        $this->info('Checking / Creating CORE Database');
        if (!$database) {
            $this->info('Skipping creation of database as env(DB_DATABASE) is empty');
            return;
        }

        try {
            $key = \Illuminate\Support\Str::random(32);
            $path = base_path('.env');
            if (file_exists($path)) {
                file_put_contents($path, str_replace(
                    'APP_KEY=', 'APP_KEY='.$key, file_get_contents($path)
                ));
                $this->info('Successfully created APP KEY');
            }

            $conn = mysqli_connect(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));

            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }
            
            // Create database
            $sql = "CREATE DATABASE IF NOT EXISTS `" . $database . "`";
            if (mysqli_query($conn, $sql)) {
                $this->info(sprintf('Successfully created %s database', $database));
            } else {
                $this->error(sprintf('Error creating %s database, %s', $database, mysqli_error($conn)));
            }
            
            mysqli_close($conn);

        } catch (Exception $exception) {
            $this->error(sprintf('Failed to create %s database, %s', $database, $exception->getMessage()));
        }

        $this->info('Importing CORE database...');
        try {
            $filename = resource_path('core.sql');
            # get the filename
            if (!file_exists($filename)) {
                throw new FileNotFoundException('Could not find the core.sql database file at: '.$filename);
            }
            if (!is_readable($filename)) {
                throw new FileException('The core.sql ('.$filename.') file is not readable by the process.');
            }


            $connImport = mysqli_connect(getenv('DB_HOST'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));

            if (!$connImport) {
                die("Connection failed: " . mysqli_connect_error());
            }

            $sql = "USE `" . $database . "`";
            if (mysqli_query($connImport, $sql)) {
                $this->info(sprintf('Successfully selected %s database', $database));
            } else {
                $this->error(sprintf('Error selecting %s database, %s', $database, mysqli_error($connImport)));
            }

            $queryLines = 0;
            $tempLine = '';
            // Read in the full file
            $lines = file($filename);
            // Loop through each line
            foreach ($lines as $line) {

                // Skip it if it's a comment
                if (substr($line, 0, 2) == '--' || $line == '')
                    continue;

                // Add this line to the current segment
                $tempLine .= $line;
                // If its semicolon at the end, so that is the end of one query
                if (substr(trim($line), -1, 1) == ';')  {
                    // Perform the query
                    mysqli_query($connImport, $tempLine) or $this->error(sprintf("Error in " . $tempLine .": %s", mysqli_error($connImport)));
                    
                    // Reset temp variable to empty
                    $tempLine = '';
                    $queryLines++;
                }
            }

            $this->info(sprintf('%s SQL lines imported successfully', $queryLines));

            mysqli_close($connImport);

        } catch (Exception $exception) {
            $this->error(sprintf('Failed to import database: %s', $exception->getMessage()));
        }

        $this->info('Setting up OAuth and Administrative Account...');
        try {
            $init = new AuthInit();
            $setup = json_decode($init->setup()); // this one doesnt seem to return what we want o

            //manually get client ids & secret in first password grant client record
            $client = DB::table("oauth_clients")->where('password_client', 1)->first();
            $client_id = $client->id;
            $client_secret = $client->secret;

            //$this->info(' ID: ' . $client_id . ", Secret: " . $client_secret);

            $password = \Illuminate\Support\Str::random(10);

            $data = [
                "firstname" => "Admin",
                "lastname" => "User",
                "email" => "demo@dorcas.io",
                "installer" => "true",
                "domain" => getenv('DORCAS_BASE_DOMAIN'),
                "password" => $password,
                "company" => "Demo",
                "phone" => "08012345678",
                "feature_select" => "all",
                "client_id" => $client_id,
                "client_secret" => $client_secret,
            ];

            $register = new AuthRegister();
            $request = new \Illuminate\Http\Request($data);
            $fractal = new \League\Fractal\Manager;
            $user = $register->register($request, $fractal);


        } catch (Exception $exception) {
            $this->error(sprintf('Failed setting up OAuth: %s', $exception->getMessage()));
        }

    }

}
