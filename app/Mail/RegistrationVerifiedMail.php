<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration)
    {
    }

    public function build()
    {
        return $this->subject('Pendaftaran Centaurian FunRun Telah Diverifikasi')
                    ->view('emails.registration_verified');
    }
}