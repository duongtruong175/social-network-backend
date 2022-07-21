<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\PostGroup;
use App\Models\RelationshipFriend;
use App\Models\Story;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FeedController extends BaseController
{
    public function getStories()
    {
        // get friends
        $sender_ids = RelationshipFriend::whereHas('receiver', function (Builder $query) {
            $query->where('user_status_code', '!=', 3);
        })->where([
            ['friend_status_code', '=', 1],
            ['sender_id', '=', auth()->id()]
        ])->select('receiver_id as user_id');

        $user_ids = RelationshipFriend::whereHas('sender', function (Builder $query) {
            $query->where('user_status_code', '!=', 3);
        })->where([
            ['friend_status_code', '=', 1],
            ['receiver_id', '=', auth()->id()]
        ])->select('sender_id as user_id')
            ->union($sender_ids)
            ->get();

        $ids = [];
        array_push($ids, auth()->id());
        foreach ($user_ids as $i) {
            array_push($ids, $i->user_id);
        }

        $stories = Story::with(['user:id,name,avatar', 'media'])
            ->whereIn('user_id', $ids)
            ->where('end_time', '>=', now())
            ->orderBy('end_time', 'desc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $stories, $message);
    }

    public function getFeedPosts()
    {
        // get friends
        $sender_ids = RelationshipFriend::whereHas('receiver', function (Builder $query) {
            $query->where('user_status_code', '!=', 3);
        })->where([
            ['friend_status_code', '=', 1],
            ['sender_id', '=', auth()->id()]
        ])->select('receiver_id as user_id');

        $user_ids = RelationshipFriend::whereHas('sender', function (Builder $query) {
            $query->where('user_status_code', '!=', 3);
        })->where([
            ['friend_status_code', '=', 1],
            ['receiver_id', '=', auth()->id()]
        ])->select('sender_id as user_id')
            ->union($sender_ids)
            ->get();

        $ids = [];
        array_push($ids, auth()->id());
        foreach ($user_ids as $i) {
            array_push($ids, $i->user_id);
        }

        // posts of friend
        $friend_posts = Post::with(['user:id,name,avatar', 'media'])
            ->whereIn('user_id', $ids)
            ->where('type', '=', 1);
        
        // kết hợp với bài viết trong nhóm
        $my_group_ids = Group::where('admin_id', '=', auth()->id())
            ->select('id')
            ->get();

        $join_group_ids = MemberGroup::where([
            ['user_id', '=', auth()->id()],
            ['member_group_status_code', '=', 1]
        ])->select('group_id')
            ->get();

        // get all group join in
        $group_ids = [];
        foreach ($my_group_ids as $i) {
            array_push($group_ids, $i->id);
        }
        foreach ($join_group_ids as $i) {
            array_push($group_ids, $i->group_id);
        }
        // get all post id of all group
        $post_ids = PostGroup::whereIn('group_id', $group_ids)
            ->select('post_id')
            ->get();

        $posts = Post::with(['user:id,name,avatar', 'media'])
            ->whereIn('id', $post_ids)
            ->union($friend_posts)
            ->orderBy('created_at', 'desc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $posts, $message);
    }
}
