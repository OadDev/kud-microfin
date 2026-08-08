<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $mailSubject,
        public string $body,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->mailSubject)
            ->view('emails.notification')
            ->with(['body' => $this->body]);
    }
}
