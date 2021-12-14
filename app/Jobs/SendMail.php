<?php

namespace App\Jobs;

use App\Mail\DefaultMail;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SendMail implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $uuid;

    protected string $to;

    protected string $subject;

    protected string $body;

    protected array $attachments;

    protected DefaultMail $mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $subject, $body, $attachments)
    {
        $this->uuid = Str::uuid();
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
        foreach($this->attachments as &$attachment) {
            $attachment['file_path'] = str_replace('@', DIRECTORY_SEPARATOR, "jobs@{$this->uuid}-{$attachment['filename']}");
            Storage::put(
                $attachment['file_path'],
                base64_decode($attachment['file_data']));
            unset($attachment['file_data']);
        }
        $this->mail = new DefaultMail($this->subject, $this->body, $this->attachments);
        Mail::to($this->to)->send($this->mail);
    }
}
