<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DefaultMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $message;

    protected $file_manifest;

    protected $parent_uuid;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message, $attachments)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->file_manifest = $attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->markdown('emails.default')->with('message', $this->message);

        foreach($this->file_manifest as $attachment)
        {
            $this->attach(Storage::path($attachment['file_path']), ['as' => $attachment['filename']]);
        }

        return $this;
    }
}
