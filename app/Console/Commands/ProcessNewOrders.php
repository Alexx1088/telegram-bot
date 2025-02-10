<?php

namespace App\Console\Commands;

use App\Services\OrderProcessor;
use Illuminate\Console\Command;

class ProcessNewOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:process-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process new orders and notify subscribed users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       $processor = new OrderProcessor();
       $processor->processNewOrders();

       $this->info('Processed new orders and sent notifications.');
    }
}
