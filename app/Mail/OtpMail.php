<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $type,
        public string $lang = 'az',
    ) {
        $this->locale($lang);
    }

    public function envelope(): Envelope
    {
        $key = $this->type === 'reset_password'
            ? 'emails.reset_password_subject'
            : 'emails.register_subject';

        return new Envelope(
            subject: __($key, [], $this->lang),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'code' => $this->code,
                'type' => $this->type,
                'locale' => $this->lang,
            ],
        );
    }
}
