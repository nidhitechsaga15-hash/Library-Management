<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\FakeBooksSeeder;

class SeedFakeBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:seed-fake {count=10000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed fake books into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int)$this->argument('count');
        $this->info("Starting to seed {$count} fake books...");
        
        $seeder = new FakeBooksSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('Done!');
        
        return 0;
    }
}
