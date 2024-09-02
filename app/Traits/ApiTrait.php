<?php
namespace App\Traits;

trait ApiTrait
{
    public function SuccessResponse($data=null,$message=null,$code)
    {
        return response()->json([
            'data'=>$data,
            'message'=>$message,
        ], $code);
    }

    public function ErrorResponse($message,$code)
    {
        return response()->json([
            'message'=>$message,
            ],$code);
    }

}
