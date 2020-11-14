<?php

namespace App\Console\Commands\Setup;

use App\Models\Country;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ImportStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dorcas:import-states';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the states data from the provided JSON file in the resource_path';

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
        $this->info('Importing entries into the database...');
        try {
            $filename = resource_path('country-states.json');
            # get the filename
            if (!file_exists($filename)) {
                throw new FileNotFoundException('Could not find the country-states.json file at: '.$filename);
            }
            if (!is_readable($filename)) {
                throw new FileException('The country-states.json ('.$filename.') file is not readable by the process.');
            }
            $entries = json_decode(file_get_contents($filename), true);
            # read in the file data
            foreach ($entries['countries'] as $entry) {
                $this->line('Setting up states for country: '.$entry['country'].'...');
                $country = Country::where('name', 'like', $entry['country'])->first();
                # to see if we already have the model
                if (empty($country)) {
                    $this->warn('Could not find country...skipping...');
                    continue;
                }
                $createdStates = $country->states()->pluck('name')->all();
                # get the states
                $states = [];
                # states to be created for the country
                $this->info('States for '.$country->name . ' [Current: '.count($createdStates).']');
                # small information display
                foreach ($entry['states'] as $state) {
                    $this->line('Processing: '.$state);
                    if (in_array($state, $createdStates)) {
                        $this->line('already exists; skipping...');
                        continue;
                    }
                    $states[] = ['uuid' => Uuid::uuid1()->toString(), 'name' => $state];
                    # add the record
                }
                $this->line('Adding '.count($states).' states to country...');
                $country->states()->createMany($states);
                $this->line('saved.');
            }
            $this->info('Done!');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
        return;
    }
}
