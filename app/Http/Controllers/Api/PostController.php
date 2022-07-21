<?php

namespace App\Http\Controllers\Api;

use App\Models\CommentPost;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostGroup;
use App\Models\ReactComment;
use App\Models\ReactPost;
use Illuminate\Http\Request;
use Validator;

class PostController extends BaseController
{
    public function getPostDetail($id)
    {
        $post = Post::with(['user:id,name,avatar', 'media'])
            ->findOrFail($id);

        $message = "Thành công";
        return $this->handleResponse(200, $post, $message);
    }

    public function createPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required|string|max:5000',
            'type' => 'required|integer',
            'media_id' => 'integer|exists:media,id',
            'group_id' => 'integer|exists:groups,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $post = Post::create([
            'user_id' => auth()->id(),
            'caption' => $request->caption,
            'type' => $request->type
        ]);

        if ($request->has('media_id')) {
            $post->media_id = $request->media_id;
            $post->save();
        }

        if ($post->type == 2) {
            PostGroup::create([
                'group_id' => $request->group_id,
                'post_id' => $post->id
            ]);
        }

        $post->load(['user:id,name,avatar', 'media']);

        $message = "Tạo bài viết hành công";
        return $this->handleResponse(201, $post, $message);
    }

    public function getTotalReactPost($id)
    {
        $react_type_1 = ReactPost::where([
            ['post_id', '=', $id],
            ['type', '=', 1]
        ])->count();
        $react_type_2 = ReactPost::where([
            ['post_id', '=', $id],
            ['type', '=', 2]
        ])->count();
        $react_type_3 = ReactPost::where([
            ['post_id', '=', $id],
            ['type', '=', 3]
        ])->count();
        $react_type_4 = ReactPost::where([
            ['post_id', '=', $id],
            ['type', '=', 4]
        ])->count();
        $react_type_5 = ReactPost::where([
            ['post_id', '=', $id],
            ['type', '=', 5]
        ])->count();
        $react_type_6 = ReactPost::where([
            ['post_id', '=', $id],
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

    public function getReactUserPost($id)
    {
        $reacts = ReactPost::with(['user:id,name,avatar'])
            ->where('post_id', '=', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $reacts, $message);
    }

    public function deletePost($id)
    {
        $post = Post::with(['media'])
            ->findOrFail($id);

        $media = $post->media;

        $comments = CommentPost::with(['media'])
            ->where('post_id', '=', $id)
            ->get();

        foreach ($comments as $comment) {
            ReactComment::where('comment_id', '=', $comment->id)
                ->delete();

            $media = $comment->media;

            $comment->delete();

            if (!empty($media)) {
                // delete related   
                $this->deleteFileMedia($media->src);
                Media::destroy($media->id);
            }
        }

        if ($post->type == 2) {
            $post_group = PostGroup::where('post_id', '=', $post->id)
                ->first();
            if ($post_group) {
                $post_group->delete();
            }
        }

        $post->delete();

        if (!empty($media)) {
            // delete related   
            $this->deleteFileMedia($media->src);
            Media::destroy($media->id);
        }

        $message = "Xóa bài viết thành công";
        return $this->handleResponse(204, [], $message);
    }

    public function reactPost(Request $request, $id)
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
            $react = ReactPost::updateOrCreate(
                ['post_id' => $id, 'user_id' => $request->user_id],
                ['type' => $request->type]
            );
        } else {
            $react = ReactPost::where([
                ['post_id', '=', $id],
                ['user_id', '=', $request->user_id]
            ])->firstOrFail();

            $react->delete();
        }

        $message = "Thả cảm xúc bài viết thành công";
        return $this->handleResponse(201, $react, $message);
    }
}
