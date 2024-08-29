<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $code;
    public function __construct($code)
    {
        $this->code=$code;
    }

    public function build()
    {
        return $this->subject('Your 2FA Code')
                    ->text('emails.twoFactorMail')
                    ->with('code',$this->code);
    }
}
