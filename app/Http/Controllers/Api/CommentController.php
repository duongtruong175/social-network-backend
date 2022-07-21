<?php

namespace App\Http\Controllers\Api;

use App\Models\CommentPost;
use App\Models\Media;
use App\Models\ReactComment;
use Illuminate\Http\Request;
use Validator;

class CommentController extends BaseController
{
    public function getComments(Request $request)
    {
        $post_id = $request->query('post_id');

        $comments = CommentPost::with(['user:id,name,avatar', 'media'])
            ->where('post_id', '=', $post_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $comments, $message);
    }

    public function createComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'post_id' => 'required|integer|exists:posts,id',
            'media_id' => 'integer|exists:media,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $comment = CommentPost::create([
            'post_id' => $request->post_id,
            'user_id' => auth()->id(),
            'content' => $request->content
        ]);

        if ($request->has('media_id')) {
            $comment->media_id = $request->media_id;
            $comment->save();
        }

        $comment->load(['user:id,name,avatar', 'media']);

        $message = "Tạo bình luận hành công";
        return $this->handleResponse(201, $comment, $message);
    }

    public function getTotalReactComment($id)
    {
        $react_type_1 = ReactComment::where([
            ['comment_id', '=', $id],
            ['type', '=', 1]
        ])->count();
        $react_type_2 = ReactComment::where([
            ['comment_id', '=', $id],
            ['type', '=', 2]
        ])->count();
        $react_type_3 = ReactComment::where([
            ['comment_id', '=', $id],
            ['type', '=', 3]
        ])->count();
        $react_type_4 = ReactComment::where([
            ['comment_id', '=', $id],
            ['type', '=', 4]
        ])->count();
        $react_type_5 = ReactComment::where([
            ['comment_id', '=', $id],
            ['type', '=', 5]
        ])->count();
        $react_type_6 = ReactComment::where([
            ['comment_id', '=', $id],
            ['type', '=', 6]
        ])->count();

        $react_count["react_type_1"] = $react_type_1;
        $react_count["react_type_2"] = $react_type_2;
        $react_count["react_type_3"] = $react_type_3;
        $react_count["react_type_4"] = $react_type_4;
        $react_count["react_type_5"] = $react_type_5;
        $react_count["react_type_6"] = $react_type_6;

        $message = "Thành công";
        return $this->handleResponse(200, $react_count, $message);
    }

    public function getReactUserComment($id)
    {
        $reacts = ReactComment::with(['user:id,name,avatar'])
            ->where('comment_id', '=', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $reacts, $message);
    }

    public function reactComment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|integer',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        if ($request->type > 0) {
            $react = ReactComment::updateOrCreate(
                ['comment_id' => $id, 'user_id' => $request->user_id],
                ['type' => $request->type]
            );
        } else {
            $react = ReactComment::where([
                ['comment_id', '=', $id],
                ['user_id', '=', $request->user_id]
            ])->firstOrFail();

            $react->delete();
        }

        $message = "Thả cảm xúc bình luận thành công";
        return $this->handleResponse(201, $react, $message);
    }

    public function deleteComment($id)
    {
        $comment = CommentPost::with(['media'])
            ->findOrFail($id);

        ReactComment::where('comment_id', '=', $id)->delete();

        $media = $comment->media;

        $comment->delete();

        if (!empty($media)) {
            // delete related   
            $this->deleteFileMedia($media->src);
            Media::destroy($media->id);
        }

        $message = "Xóa thông báo thành công";
        return $this->handleResponse(204, [], $message);
    }
}
