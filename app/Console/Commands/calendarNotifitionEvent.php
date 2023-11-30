<?php

namespace App\Console\Commands;

use App\Mail\EmailApi;
use App\Models\event;
use Illuminate\Auth\Events\Logout;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class calendarNotifitionEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calendar-notifition-event';

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
        $currentDateTime = Carbon::now();
        $fiveHoursAgo = $currentDateTime->subHours(5)->toDateTimeString();
        $events = event::where('start_time', '>', $fiveHoursAgo)
            ->with(['attendances.user', 'user'])
            ->whereDate('start_time', '=', $currentDateTime->toDateString())
            ->where('status', 1)
            ->get();
        foreach ($events as $item) {

            foreach($item->attendances as $userSend){
                $data = [
                    'title' => "EMAIL NHẮC NHỞ SỰ KIỆN " . $item->name,
                    'message' =>$item->user->receivedNotifications->last()->content,
                ];

                Mail::to($userSend->user->email)->send(new EmailApi($data,));
                Log::info('Email sent successfully:' . $item->name);
            }

        }

        return 0;

    }
}
