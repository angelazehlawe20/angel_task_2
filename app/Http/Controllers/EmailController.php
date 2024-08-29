<?php

namespace App\Http\Controllers;

use App\Mail\EmailMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmail()
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'User not authenticated.'], 401);
    }

    Mail::to($user->email)->send(new EmailMail($user));

    return response()->json(['message'=>'Email sent successfully!'],200);
    }


}
