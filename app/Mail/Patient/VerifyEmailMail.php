<?php

namespace App\Mail\Patient;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Patient $patient,
        public string $verificationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email - Chenaker Smile Maker',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.patient.verify-email',
            with: [
                'patient' => $this->patient,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }
}
