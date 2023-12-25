<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateEventStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-event-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventUpdateStatusI = DB::table('events')->where('start_time','<=',Carbon::now())
                                                ->where('end_time','>=',Carbon::now())
                                                ->update(['status' => 1]);
        $eventUpdateStatusEnd = DB::table('events')->where('end_time','<',Carbon::now())
                                                ->update(['status' => 0]);
        $this->info('Event statuses updated successfully.');
    }
}
