<?php

namespace App\Http\Controllers;

use App\Services\EmailController\SendEmailService;


class EmailController extends Controller
{
    protected $SendEmailService;

    public function __constructor(SendEmailService $SendEmailService)
    {
        $this->SendEmailService = $SendEmailService;
    }
    public function sendEmail()
    {
        return $this->SendEmailService->sendEmailUser();
    }

}
