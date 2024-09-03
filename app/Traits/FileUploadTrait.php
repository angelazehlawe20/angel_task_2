<?php
namespace App\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


trait FileUploadTrait
{
    public function uploadFile(Request $request,$fieldName,$folderName,$diskName)
    {
        $originalName=$request->file($fieldName)->getClientOriginalName();
        $path=$request->file($fieldName)->storeAs($folderName,$originalName,$diskName);
        return $path;
    }

    public function fileUrl($path)
    {
        return Storage::url($path);
    }
}
