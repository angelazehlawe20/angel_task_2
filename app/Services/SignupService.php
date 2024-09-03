<?php
namespace App\Services;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiTrait;
use App\Traits\FileUploadTrait;
use App\Events\RegisterEvent;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;



class SignupService
{
    use FileUploadTrait;
    use ApiTrait;

    public function signupUser(RegisterRequest $request)
    {
        $validation=$request->validated();

        $profilePhotoPath=$this->uploadFile($request,'profile_photo','profile_photos','public');
        $certificatePath=$this->uploadFile($request,'certificate','certificates','public');

        //الوصول الى الصورةالمرفوعة
        $photo_url=$this->fileUrl($profilePhotoPath);

        $user=User::create([
            'email'=>$validation['email'],
            'phone_number'=>$validation['phone_number'],
            'username'=>$validation['username'],
            'profile_photo'=>$profilePhotoPath,
            'certificate'=>$certificatePath,
            'password'=>bcrypt($validation['password']),
            'verification_code'=>Str::random(6),
            'verification_code_expires_at'=>now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new VerificationMail($user));

        RegisterEvent::dispatch($user);

        return $this->SuccessResponse($user,'user registered.Please check your email for verification.',201);

    }
}
