<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Admin\BookRequestController;

class AutoReturnExpiredBookRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:auto-return-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically return books to stock if not collected within deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new BookRequestController();
        $count = $controller->autoReturnExpiredRequests();
        
        $this->info("Auto-returned {$count} expired book request(s).");
        
        return Command::SUCCESS;
    }
}
