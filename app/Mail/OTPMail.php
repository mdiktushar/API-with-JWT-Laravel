<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sub, $otp, $user;
    /**
     * Create a new message instance.
     */
    public function __construct($subject, $otp, $user)
    {
        $this->sub = $subject;
        $this->otp = $otp;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->sub,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('email');
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'user' => $this->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
