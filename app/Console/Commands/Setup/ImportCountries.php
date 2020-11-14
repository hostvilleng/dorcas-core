<?php

namespace App\Console\Commands\Setup;

use App\Models\Country;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ImportCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dorcas:import-countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the countries data from the provided JSON file in the resource_path';

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
        $this->info('Importing countries into the database...');
        try {
            $filename = resource_path('countries.json');
            # get the filename
            if (!file_exists($filename)) {
                throw new FileNotFoundException('Could not find the countries.json file at: '.$filename);
            }
            if (!is_readable($filename)) {
                throw new FileException('The countries.json ('.$filename.') file is not readable by the process.');
            }
            $countries = json_decode(file_get_contents($filename), true);
            # read in the file data
            foreach ($countries as $country) {
                $this->line('Setting up country: '.$country['name']['common'].'...');
                $model = Country::firstOrNew(['iso_code' => $country['cca2']]);
                # to see if we already have the model
                $model->name = $country['name']['common'];
                $model->dialing_code = $country['callingCode'][0] ?? null;
                # set the properties
                if (!$model->save()) {
                    $this->warn('save failed!');
                    continue;
                }
                $this->line('saved.');
            }
            $this->info('Done!');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        return;
    }
}
