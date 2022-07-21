<?php

namespace App\Http\Controllers\Api;

use App\Models\Group;
use App\Models\Media;
use App\Models\MemberGroup;
use App\Models\Post;
use App\Models\PostGroup;
use Illuminate\Http\Request;
use Validator;

class GroupController extends BaseController
{
    public function getGroups()
    {
        $my_groups = Group::with(['admin:id,name,avatar'])
            ->where('admin_id', '=', auth()->id())
            ->orderBy('name', 'asc')
            ->get();

        $join_group_ids = MemberGroup::where([
            ['user_id', '=', auth()->id()],
            ['member_group_status_code', '=', 1]
        ])->select('group_id')
            ->get();

        $join_groups = Group::with(['admin:id,name,avatar'])
            ->whereIn('id', $join_group_ids)
            ->orderBy('name', 'asc')
            ->get();

        $groups = $my_groups->merge($join_groups);

        $message = "Thành công";
        return $this->handleResponse(200, $groups, $message);
    }

    public function getSuggestGroups()
    {
        $suggest_groups = Group::where([
            ['type', '=', 1],
            ['admin_id', '!=', auth()->id()]
        ])->whereDoesntHave('members', function ($query) {
            return $query->where('user_id', '=', auth()->id());
        })->withCount(['members' => function ($query) {
            $query->where('member_group_status_code', '=', 1);
        }])
            ->orderBy('members_count', 'desc')
            ->take(20)
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $suggest_groups, $message);
    }

    public function getFeedGroup()
    {
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
            ->orderBy('created_at', 'desc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $posts, $message);
    }

    public function getGroupDetail($id)
    {
        $group = Group::with(['admin:id,name,avatar'])
            ->where('id', '=', $id)
            ->firstOrFail();

        $message = "Thành công";
        return $this->handleResponse(200, $group, $message);
    }

    public function getGroupPosts($id)
    {
        $post_ids = PostGroup::where('group_id', '=', $id)
            ->select('post_id')
            ->get();

        $posts = Post::with(['user:id,name,avatar', 'media'])
            ->whereIn('id', $post_ids)
            ->orderBy('created_at', 'desc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $posts, $message);
    }

    public function getMemberGroups($id)
    {
        $member_request = MemberGroup::with(['user:id,name,avatar'])
            ->where([
                ['group_id', '=', $id],
                ['member_group_status_code', '=', 1]
            ])
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $member_request, $message);
    }

    public function getGroupRequests($id)
    {
        $member_request = MemberGroup::with(['user:id,name,avatar'])
            ->where([
                ['group_id', '=', $id],
                ['member_group_status_code', '=', 0]
            ])
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $member_request, $message);
    }

    public function getMemberGroupRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'group_id' => 'required|integer|exists:groups,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $request_group = MemberGroup::where([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id
        ])->firstOrFail();

        $message = "Thành công";
        return $this->handleResponse(200, $request_group, $message);
    }

    public function acceptMemberGroupRequest($id)
    {
        $request_group = MemberGroup::where('id', '=', $id)
            ->firstOrFail();

        // check user logged in is admin of group or not?
        $check_permission = Group::where([
            ['id', '=', $request_group->group_id],
            ['admin_id', '=', auth()->id()]
        ])->first();

        if ($check_permission) {
            $request_group->member_group_status_code = 1;
            $request_group->save();

            $message = "Thành công";
            return $this->handleResponse(200, $request_group, $message);
        }

        $errors = "Unauthorized";
        $message = "Không được phép truy cập";
        return $this->handleError(401, $errors, $message);
    }

    public function deleteMemberGroupRequest($id)
    {
        $request_group = MemberGroup::where('id', '=', $id)
            ->firstOrFail();

        $request_group->delete();

        $message = "Xóa yêu cầu vào nhóm thành công";
        return $this->handleResponse(204, [], $message);
    }

    public function sendRequestJoinGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|integer|exists:groups,id',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $group = Group::where('id', '=', $request->group_id)
            ->firstOrFail();

        $request_group = MemberGroup::firstOrCreate([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id
        ]);

        if ($group->type == 1) {
            $request_group->member_group_status_code = 1;
            $request_group->save();
        } else {
            $request_group->member_group_status_code = 0;
            $request_group->save();
        }

        $request_group->load(['user:id,name,avatar']);

        $message = "Đã gửi yêu cầu thành công";
        return $this->handleResponse(201, $request_group, $message);
    }

    public function createGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:200',
            'cover_image' => 'string',
            'introduction' => 'string|max:1000',
            'type' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $group = Group::create([
            'admin_id' => $request->admin_id,
            'name' => $request->name,
            'type' => $request->type
        ]);

        if ($request->has('cover_image')) {
            $group->cover_image = $request->cover_image;
            $group->save();
        }

        if ($request->has('introduction')) {
            $group->introduction = $request->introduction;
            $group->save();
        }

        $return_group = Group::with(['admin:id,name,avatar'])
            ->where('id', '=', $group->id)
            ->first();

        $message = "Thành công";
        return $this->handleResponse(201, $return_group, $message);
    }

    public function updateCoverImageGroup(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cover_image' => 'required|image|mimes:jpeg,jpg,png,gif,webp'
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $group = Group::where('id', '=', $id)
            ->firstOrFail();

        $media = $this->uploadFileMedia($request->file('cover_image'), 'image');

        $media_store = Media::create([
            'type' => $media['type'],
            'name' => $media['name'],
            'domain' => $media['domain'],
            'path' => $media['path'],
            'src' => $media['src']
        ]);

        // delete old
        if (!empty($group->cover_image)) {
            $this->deleteFileMedia($group->cover_image);
            $temp = Media::where('src', 'like', $group->cover_image)
                ->first();
            if ($temp) {
                $temp->delete();
            }
        }

        // update
        $group->cover_image = $media_store->src;
        $group->save();

        $message = "Cập nhập nhóm thành công";
        return $this->handleResponse(201, $group->cover_image, $message);
    }

    public function updateGroup(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'introduction' => 'string|max:1000',
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $group = Group::where('id', '=', $id)
            ->firstOrFail();

        $group->name = $request->name;
        if ($request->has('introduction')) {
            if (empty($request->introduction)) {
                $group->introduction = NULL;
            } else {
                $group->introduction = $request->introduction;
            }
        }
        $group->save();

        $message = "Cập nhập nhóm thành công";
        return $this->handleResponse(201, $group, $message);
    }

    public function updateTypeGroup(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $group = Group::where('id', '=', $id)
            ->firstOrFail();

        $group->type = $request->type;
        $group->save();

        $message = "Cập nhập nhóm thành công";
        return $this->handleResponse(201, $group, $message);
    }

    public function deleteGroup()
    {
        // update later
    }
}
