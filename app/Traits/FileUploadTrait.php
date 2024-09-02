<?php
namespace App\Traits;
use Illuminate\Http\Request;

trait FileUploadTrait
{
    public function uploadFile(Request $request,$fieldName,$folderName,$diskName)
    {
        $originalName=$request->file($fieldName)->getClientOriginalName();
        $path=$request->file($fieldName)->storeAs($folderName,$file_name,$diskName);
        return $path;
    }

    public function fileUrl($path)
    {
        return Storage::url($path);
    }
}
