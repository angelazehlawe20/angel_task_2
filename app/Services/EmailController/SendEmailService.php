<?php
namespace App\Services\EmailController;

use App\Traits\ApiTrait;
use App\Mail\EmailMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class SendEmailService
{
    use ApiTrait;

    private $cacheTTL = 600;
    private $cachePrefix = 'send_email_';

    public function sendEmailUser()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->ErrorResponse('User not authenticated.', 401);
        }

        $cacheKey = $this->getCacheKey();

        if ($this->isEmailSendingRateLimited($cacheKey)) {
            return $this->ErrorResponse('You can only send one email every 10 minutes from this IP.', 429);
        }

        $this->sendEmail($user);
        $this->storeEmailSentInCache($cacheKey);

        return $this->SuccessResponse($user, 'Email sent successfully.', 200);
    }

    private function getCacheKey(): string
    {
        return $this->cachePrefix . request()->ip();
    }

    private function isEmailSendingRateLimited(string $cacheKey): bool
    {
        return Cache::has($cacheKey);
    }

    private function sendEmail($user): void
    {
        Mail::to($user->email)->send(new EmailMail($user));
    }

    private function storeEmailSentInCache(string $cacheKey): void
    {
        Cache::put($cacheKey, true, $this->cacheTTL);
    }
}
