<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BaseController extends Controller
{
    // format response
    public function handleResponse($code = 200, $data = [], $message)
    {
        $res = [
            'code' => $code,
            'data' => $data,
            'message' => $message
        ];
        return response()->json($res, $code);
    }

    // format error response
    public function handleError($code = 400, $errors = [], $message)
    {
        $res = [
            'code' => $code,
            'errors' => $errors,
            'message' => $message
        ];
        return response()->json($res, $code);
    }

    // define function to use on all Controller

    // upload a file from form request to server
    public function uploadFileMedia(UploadedFile $file, $file_type)
    {
        // define name file
        $extension = $file->getClientOriginalExtension();
        $name = Str::random(6) . '-' . Str::random(8) . '-' . time() . '.' . $extension;

        // define path
        $domain = config('constants.domain');
        if ($file_type == 'video') {
            $folder = config('constants.video_save_folder');
        } else { // default
            $folder = config('constants.image_save_folder');
        }
        $directory = public_path() . '/' . $folder . '/';

        $file->move($directory, $name);

        $media = [
            'type' => $file_type,
            'name' => $name,
            'domain' => $domain,
            'path' => $folder . '/' . $name,
            'src' => $domain . '/' . $folder . '/' . $name
        ];
        return $media;
    }

    // delete a file from server
    public function deleteFileMedia($file_path)
    {
        // convert from http url to local
        $filename = basename($file_path);
        $path = public_path() . '/' . config('constants.image_save_folder') . '/' . $filename;

        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
