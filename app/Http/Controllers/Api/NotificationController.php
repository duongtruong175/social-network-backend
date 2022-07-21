<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\Request;
use Validator;

class NotificationController extends BaseController
{
    public function getNotifications()
    {
        $notifications = Notification::where('receiver_id', '=', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $notifications, $message);
    }

    public function createNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'image_url' => 'string',
            'type' => 'required|integer',
            'content' => 'required|string',
            'url' => 'required|string'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $temp = Notification::where([
            ['receiver_id', '=', $request->receiver_id],
            ['type', '=', $request->type],
            ['url', '=', $request->url]
        ])->first();
        if ($temp) {
            $temp->delete();
        }

        $notification = Notification::create([
            'receiver_id' => $request->receiver_id,
            'type' => $request->type,
            'content' => $request->content,
            'url' => $request->url
        ]);

        if ($request->has('image_url')) {
            $notification->image_url = $request->image_url;
            $notification->save();
        }

        $message = "Thành công";
        return $this->handleResponse(201, $notification, $message);
    }

    public function readAllNotification()
    {
        Notification::where('receiver_id', '=', auth()->id())
            ->update([
                'notification_status_code' => 1
            ]);

        $message = "Thành công";
        return $this->handleResponse(204, [], $message);
    }

    public function updateNotification(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notification_status_code' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $notification = Notification::findOrFail($id);
        $notification->notification_status_code = $request->notification_status_code;
        $notification->save();

        $message = "Sửa thông báo thành công";
        return $this->handleResponse(201, $notification, $message);
    }

    public function deleteNotification($id)
    {
        $notification = Notification::findOrFail($id);

        $notification->delete();

        $message = "Xóa thông báo thành công";
        return $this->handleResponse(204, [], $message);
    }
}
