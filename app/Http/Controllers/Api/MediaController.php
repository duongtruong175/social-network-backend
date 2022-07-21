<?php

namespace App\Http\Controllers\Api;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class MediaController extends BaseController
{
    public function uploadMedia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', Rule::in(['image', 'video'])],
            'media' => 'required|file'
        ]);
        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        if ($request->type == 'image') {
            $validator_temp = Validator::make($request->all(), [
                'media' => 'image|mimes:jpeg,jpg,png,gif,webp'
            ]);

            if ($validator_temp->fails()) {
                $message = "Một số dữ liệu cung cấp không hợp lệ";
                return $this->handleError(422, $validator_temp->errors(), $message);
            }
        }
        else if ($request->type == 'video') {
            $validator_temp = Validator::make($request->all(), [
                'media' => 'mimetypes:video/mp4,video/mpeg,video/x-matroska,video/quicktime,video/x-msvideo,video/webm,video/3gpp'
            ]);

            if ($validator_temp->fails()) {
                $message = "Một số dữ liệu cung cấp không hợp lệ";
                return $this->handleError(422, $validator_temp->errors(), $message);
            }
        }
        $media_store = $this->uploadFileMedia($request->file('media'), $request->type);

        $media = Media::create([
            'type' => $media_store['type'],
            'name' => $media_store['name'],
            'domain' => $media_store['domain'],
            'path' => $media_store['path'],
            'src' => $media_store['src']
        ]);

        $message = "Tải file lên thành công";
        return $this->handleResponse(201, $media, $message);
    }
}
