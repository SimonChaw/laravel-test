<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DefaultMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $message;

    protected $base64_attachments;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $message, $attachments)
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->base64_attachments = $attachments;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->markdown('emails.default')->with('message', $this->message);

        foreach($this->base64_attachments as $attachment)
        {
            $this->attachData($attachment['file_data'], $attachment['filename']);
        }

        return $this;
    }
}
