<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public function __construct($user)
    {
        $this->user=$user;

    }


    public function build()
    {
        $content="your verification code : {{$this->user->verification_code}}\n";
        $content .="this code is valid until : {{$this->user->verification_code_expires_at}}";
        return $this->subject('Email verification')
                    ->text('emails.verification')
                    ->with([
                        'content'=>$content
                    ]);
    }
}
