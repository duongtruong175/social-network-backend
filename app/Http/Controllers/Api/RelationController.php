<?php

namespace App\Http\Controllers\Api;

use App\Models\RelationshipFriend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Validator;

class RelationController extends BaseController
{
    public function getRelation($id)
    {
        $relation = RelationshipFriend::where([
            ['sender_id', '=', auth()->id()],
            ['receiver_id', '=', $id]
        ])->orWhere(function($query) use ($id) {
            $query->where('sender_id', '=', $id)
                  ->where('receiver_id', '=', auth()->id());
        })->firstOrFail();

        $message = "Thành công";
        return $this->handleResponse(200, $relation, $message);
    }

    public function getAllRelations()
    {
        $temp = RelationshipFriend::whereHas('receiver', function (Builder $query) {
            $query->where([
                ['user_status_code', '!=', 3]
            ]);
        })->where('sender_id', '=', auth()->id());

        $relations = RelationshipFriend::whereHas('sender', function (Builder $query) {
            $query->where([
                ['user_status_code', '!=', 3]
            ]);
        })->where('receiver_id', '=', auth()->id())
            ->union($temp)
            ->orderBy('updated_at', 'desc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $relations, $message);
    }

    public function createRelation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $relation = RelationshipFriend::where([
            ['sender_id', '=', auth()->id()],
            ['receiver_id', '=', $request->receiver_id]
        ])->orWhere(function ($query) use ($request) {
            $query->where('sender_id', '=', $request->receiver_id)
                ->where('receiver_id', '=', auth()->id());
        })->first();

        if ($relation) {
            $message = "Tạo thành công";
            return $this->handleResponse(201, $relation, $message);
        }

        $new_relation = RelationshipFriend::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id
        ]);

        $message = "Tạo thành công";
        return $this->handleResponse(201, $new_relation, $message);
    }

    public function updateRelation(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'friend_status_code' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $relation = RelationshipFriend::findOrFail($id);

        $relation->friend_status_code = $request->friend_status_code;
        $relation->save();

        $message = "Cập nhập thành công";
        return $this->handleResponse(201, $relation, $message);
    }

    public function deleteRelation($id)
    {
        $relation = RelationshipFriend::findOrFail($id);

        $relation->delete();

        $message = "Xóa thành công";
        return $this->handleResponse(204, [], $message);
    }
}
