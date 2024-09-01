<?php
namespace App\Traits;

trait ApiTrait
{
    protected function SuccessResponse($data=null,$message=null,$code)
    {
        return response()->json([
            'data'=>$data,
            'message'=>$message,
        ], $code);
    }

    protected function ErrorResponse($message,$code)
    {
        return response()->json([
            'message'=>$message,
            ],$code);
    }

}
