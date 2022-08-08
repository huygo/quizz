<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testcron;
class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $datasave=['value'=>date("Y-m-d H:i:s")];
        $insert= Testcron::insert($datasave);
        return $insert;
    }
}
