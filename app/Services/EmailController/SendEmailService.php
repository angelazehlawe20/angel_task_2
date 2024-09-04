<?php
namespace App\Services\EmailController;
use App\Traits\ApiTrait;
use App\Mail\EmailMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SendEmailService
{
    use ApiTrait;

    public function sendEmailUser()
    {

    $user = Auth::user();
    if (!$user) {
        return $this->ErrorResponse('User not authenticated.', 401);
    }
    Mail::to($user->email)->send(new EmailMail($user));
    return $this->SuccessResponse($user,'Email sent successfully.',200);
    
    }
}
