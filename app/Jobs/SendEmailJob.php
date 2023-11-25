<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\EmailApi;
use App\Models\notification;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $notification;
    public function __construct( notification $notification)
    {
        $this->$notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $receiver = $this->notification->receiver;
        $content = $this->notification->content;

        // Gửi email đến người nhận
        Mail::to($receiver->email)->send(new EmailApi($content));

        // Xóa tin nhắn sau khi gửi
        $this->notification->delete();
    }
}
