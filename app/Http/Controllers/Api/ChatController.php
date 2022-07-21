<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class ChatController extends BaseController
{
    public function getConversations()
    {
        $conversations = Conversation::with(['receiver:id,name,avatar'])
            ->where('sender_id', '=', auth()->id())
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $conversations, $message);
    }

    public function createConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $conversation = Conversation::with(['receiver:id,name,avatar'])
            ->where([
                ['sender_id', '=', auth()->id()],
                ['receiver_id', '=', $request->receiver_id]
            ])->first();

        // get if exists
        if ($conversation) {
            $message = "Tạo thành công";
            return $this->handleResponse(201, $conversation, $message);
        }

        // create one if have one
        $conversation_temp = Conversation::with(['receiver:id,name,avatar'])
            ->where([
                ['sender_id', '=', $request->receiver_id],
                ['receiver_id', '=', auth()->id()]
            ])->first();
        if ($conversation_temp) {
            $conversation_temp2 = Conversation::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $request->receiver_id,
                'room_chat_id' => $conversation_temp->room_chat_id
            ]);
            $conversation_temp2->load(['receiver:id,name,avatar']);
            $message = "Tạo thành công";
            return $this->handleResponse(201, $conversation_temp2, $message);
        }

        // else create new all
        $uuid = Str::uuid()->toString();
        Conversation::insert([
            [
                'sender_id' => auth()->id(),
                'receiver_id' => $request->receiver_id,
                'room_chat_id' => $uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sender_id' => $request->receiver_id,
                'receiver_id' => auth()->id(),
                'room_chat_id' => $uuid,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        $conversation_return = Conversation::with(['receiver:id,name,avatar'])
            ->where([
                ['sender_id', '=', auth()->id()],
                ['receiver_id', '=', $request->receiver_id]
            ])->first();

        $message = "Thành công";
        return $this->handleResponse(201, $conversation_return, $message);
    }
}
