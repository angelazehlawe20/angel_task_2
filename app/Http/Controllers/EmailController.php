<?php

namespace App\Http\Controllers;

use App\Services\EmailController\SendEmailService;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    protected $sendEmailService;

    public function __construct(SendEmailService $sendEmailService)
    {
        $this->sendEmailService = $sendEmailService;
    }

    public function sendEmail(Request $request)
    {
        return $this->sendEmailService->sendEmailUser();
    }
}

