<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;

    protected $subject;

    protected $body;

    protected $attachments;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $subject, $body, $attachments)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->attachments = $attachments;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::raw($this->body, function($message) {
            $message->to($this->to)
                ->subject($this->subject);

            foreach($this->attachments as $attachment) {
                $message->attachData($attachment['file_data'], $attachment['filename']);
            }
        });
    }
}
