<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailApi;
use App\Models\notification;
class SendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:send-mail';

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
        $currentDateTime = Carbon::now()->toDateTimeString();
        $emails = notification::where('time_send', '<=', $currentDateTime)
            ->with('user_receiver',function ($query){
                $query->select('id','name','email');
            })
            ->whereNull('sent_at')
            ->get();


//        $emails = notification::where('time_send', '<=', $currentDateTime)
//            ->whereNull('time_send')
//            ->get();
        if ($emails->count() > 0) {
        foreach ($emails as $email) {
            $data = [
                'title' => $email->title,
                'message' =>$email->content,
            ];
            // Gửi email tới người nhận $recipient
            Mail::to($email->user_receiver->email)->send(new EmailApi($data));
            $email->sent_at =  now();
            $email->save();
        }
        }

        return 0;
    }
}
