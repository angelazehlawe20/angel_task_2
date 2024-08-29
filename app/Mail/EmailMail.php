<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user;
    public function __construct($user)
    {
        $this->user=$user;
    }


    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'))
                    ->subject('Send Email')
                    ->text('emails.send_email')
                    ->with([
                        'userName' => $this->user->username,
                    ]);
    }


}
