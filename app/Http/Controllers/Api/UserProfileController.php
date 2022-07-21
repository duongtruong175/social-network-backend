<?php

namespace App\Http\Controllers\Api;

use App\Models\Media;
use App\Models\Post;
use App\Models\RelationshipFriend;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Validator;

use function PHPUnit\Framework\isEmpty;

class UserProfileController extends BaseController
{
    public function profile($id)
    {
        $user = User::where('id', '=', $id)
            ->first();

        if ($user) {
            $message = "Thành công";
            return $this->handleResponse(200, $user, $message);
        }

        $message = "Tài khoản $id không tồn tại";
        return $this->handleError(404, [], $message);
    }


    public function updateAvatar(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->id == $id) {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,jpg,png,gif,webp'
            ]);

            if ($validator->fails()) {
                $message = "Một số dữ liệu cung cấp không hợp lệ";
                return $this->handleError(422, $validator->errors(), $message);
            }

            $media = $this->uploadFileMedia($request->file('avatar'), 'image');

            $media_store = Media::create([
                'type' => $media['type'],
                'name' => $media['name'],
                'domain' => $media['domain'],
                'path' => $media['path'],
                'src' => $media['src']
            ]);

            $user->avatar = $media_store->src;
            $user->save();

            $post = Post::create([
                'user_id' => $user->id,
                'caption' => "Đã cập nhập ảnh đại diện",
                'type' => 1
            ]);
            $post->media_id = $media_store->id;
            $post->save();
            $post_return = Post::with(['user:id,name,avatar', 'media'])
                ->where('id', '=', $post->id)
                ->first();

            $data['avatar'] = $user->avatar;
            $data['post'] = $post_return;

            $message = "Cập nhập tài khoản thành công";
            return $this->handleResponse(201, $data, $message);
        }

        $errors = "Unauthorized";
        $message = "Không được phép truy cập";
        return $this->handleError(401, $errors, $message);
    }

    public function updateCoverImage(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->id == $id) {
            $validator = Validator::make($request->all(), [
                'cover_image' => 'required|image|mimes:jpeg,jpg,png,gif,webp'
            ]);

            if ($validator->fails()) {
                $message = "Một số dữ liệu cung cấp không hợp lệ";
                return $this->handleError(422, $validator->errors(), $message);
            }

            $media = $this->uploadFileMedia($request->file('cover_image'), 'image');

            $media_store = Media::create([
                'type' => $media['type'],
                'name' => $media['name'],
                'domain' => $media['domain'],
                'path' => $media['path'],
                'src' => $media['src']
            ]);

            $user->cover_image = $media['src'];
            $user->save();

            $post = Post::create([
                'user_id' => $user->id,
                'caption' => "Đã cập nhập ảnh bìa",
                'type' => 1
            ]);
            $post->media_id = $media_store->id;
            $post->save();
            $post_return = Post::with(['user:id,name,avatar', 'media'])
                ->where('id', '=', $post->id)
                ->first();

            $data['cover_image'] = $user->cover_image;
            $data['post'] = $post_return;

            $message = "Cập nhập tài khoản thành công";
            return $this->handleResponse(201, $data, $message);
        }

        $errors = "Unauthorized";
        $message = "Không được phép truy cập";
        return $this->handleError(401, $errors, $message);
    }

    public function updateProfile(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->id == $id) {
            $input = $request->all();

            if ($request->has('name')) {
                $validator = Validator::make($input, [
                    'name' => 'required|string|min:2|max:30'
                ]);

                if ($validator->fails()) {
                    $message = "Một số dữ liệu cung cấp không hợp lệ";
                    return $this->handleError(422, $validator->errors(), $message);
                }
                $user->name = $request->name;
            }
            if ($request->has('phone')) {
                $validator = Validator::make($input, [
                    'phone' => 'required|string|regex:/(0)[0-9]{9}/'
                ]);

                if ($validator->fails()) {
                    $message = "Một số dữ liệu cung cấp không hợp lệ";
                    return $this->handleError(422, $validator->errors(), $message);
                }
                $user->phone = $request->phone;
            }
            if ($request->has('birthday')) {
                // check years old (> 14)
                $day = date('Y-m-d', strtotime('-14 years'));

                $validator = Validator::make($input, [
                    'birthday' => 'required|date|before:' . $day
                ]);

                if ($validator->fails()) {
                    $message = "Một số dữ liệu cung cấp không hợp lệ";
                    return $this->handleError(422, $validator->errors(), $message);
                }
                $user->birthday = $request->birthday;
            }
            if ($request->has('gender')) {
                $validator = Validator::make($input, [
                    'gender' => 'required|string'
                ]);

                if ($validator->fails()) {
                    $message = "Một số dữ liệu cung cấp không hợp lệ";
                    return $this->handleError(422, $validator->errors(), $message);
                }
                $user->gender = $request->gender;
            }
            if ($request->has('relationship_status')) {
                if (empty($request->relationship_status)) {
                    $user->relationship_status = NULL;
                } else {
                    $user->relationship_status = $request->relationship_status;
                }
            }
            if ($request->has('website')) {
                if (empty($request->website)) {
                    $user->website = NULL;
                } else {
                    $user->website = $request->website;
                }
            }
            if ($request->has('short_description')) {
                $validator = Validator::make($input, [
                    'short_description' => 'max:100'
                ]);

                if ($validator->fails()) {
                    $message = "Một số dữ liệu cung cấp không hợp lệ";
                    return $this->handleError(422, $validator->errors(), $message);
                }
                if (empty($request->short_description)) {
                    $user->short_description = NULL;
                } else {
                    $user->short_description = $request->short_description;
                }
            }
            if ($request->has('hometown')) {
                if (empty($request->hometown)) {
                    $user->hometown = NULL;
                } else {
                    $user->hometown = $request->hometown;
                }
            }
            if ($request->has('current_residence')) {
                if (empty($request->current_residence)) {
                    $user->current_residence = NULL;
                } else {
                    $user->current_residence = $request->current_residence;
                }
            }
            if ($request->has('job')) {
                // covert json string to php array if send request from mobie
                if (empty($request->job['company']) || empty($request->job['position'])) {
                    $request->job = json_decode($request->job, true);
                }
                if (empty($request->job) || empty($request->job['company']) || empty($request->job['position'])) {
                    $user->job = NULL;
                } else {
                    $validator = Validator::make($request->job, [
                        'job' => 'array:company,position'
                    ]);

                    if ($validator->fails()) {
                        $message = "Một số dữ liệu cung cấp không hợp lệ";
                        return $this->handleError(422, $validator->errors(), $message);
                    }
                    $job = $user->job;
                    $job['company'] = $request->job['company'];
                    $job['position'] = $request->job['position'];
                    $user->job = $job;
                }
            }
            if ($request->has('education')) {
                // covert json string to php array if send request from mobie
                if (empty($request->education['school']) || empty($request->education['majors'])) {
                    $request->education = json_decode($request->education, true);
                }
                if (empty($request->education) || empty($request->education['school']) || empty($request->education['majors'])) {
                    $user->education = NULL;
                } else {
                    $validator = Validator::make($request->education, [
                        'education' => 'array:school,majors'
                    ]);

                    if ($validator->fails()) {
                        $message = "Một số dữ liệu cung cấp không hợp lệ";
                        return $this->handleError(422, $validator->errors(), $message);
                    }
                    $education = $user->education;
                    $education['school'] = $request->education['school'];
                    $education['majors'] = $request->education['majors'];
                    $user->education = $education;
                }
            }

            $user->save();
            $message = "Cập nhập tài khoản thành công";
            return $this->handleResponse(201, $user, $message);
        }

        $errors = "Unauthorized";
        $message = "Không được phép truy cập";
        return $this->handleError(401, $errors, $message);
    }

    public function getPhotoAlbum($id)
    {
        $user = User::findOrFail($id);

        $album = Media::join('posts', 'media.id', '=', 'posts.media_id')
            ->where([
                ['posts.user_id', '=', $id],
                ['posts.type', '=', 1],
                ['media.type', 'like', 'image']
            ])->select('media.src')
            ->get();

        $photos = [];
        if (isEmpty($user->avatar)) {
            array_push($photos, $user->avatar);
        }
        if (isEmpty($user->cover_image)) {
            array_push($photos, $user->cover_image);
        }
        foreach ($album as $i) {
            array_push($photos, $i->src);
        }

        $message = "Thành công";
        return $this->handleResponse(200, $photos, $message);
    }

    public function getVideoAlbum($id)
    {
        $album = Media::join('posts', 'media.id', '=', 'posts.media_id')
            ->where([
                ['posts.user_id', '=', $id],
                ['posts.type', '=', 1],
                ['media.type', 'like', 'video']
            ])->select('media.src')
            ->get();

        $videos = [];

        foreach ($album as $i) {
            array_push($videos, $i->src);
        }

        $message = "Thành công";
        return $this->handleResponse(200, $videos, $message);
    }

    public function getTopFriends($id)
    {
        $temp = User::whereHas('receiver', function (Builder $query) use ($id) {
            $query->where([
                ['friend_status_code', '=', 1],
                ['sender_id', '=', $id]
            ]);
        })->where('user_status_code', '!=', 3)
            ->select('id', 'name', 'avatar');

        $friends = User::whereHas('sender', function (Builder $query) use ($id) {
            $query->where([
                ['friend_status_code', '=', 1],
                ['receiver_id', '=', $id]
            ]);
        })->where('user_status_code', '!=', 3)
            ->select('id', 'name', 'avatar')
            ->union($temp)
            ->take(10)
            ->get();

        // bug: relation attribute is relation bettween destination user and logged in user

        $message = "Thành công";
        return $this->handleResponse(200, $friends, $message);
    }

    public function getAllFriends($id)
    {
        $temp = User::whereHas('receiver', function (Builder $query) use ($id) {
            $query->where([
                ['friend_status_code', '=', 1],
                ['sender_id', '=', $id]
            ]);
        })->where('user_status_code', '!=', 3)
            ->select('id', 'name', 'avatar');

        $friends = User::whereHas('sender', function (Builder $query) use ($id) {
            $query->where([
                ['friend_status_code', '=', 1],
                ['receiver_id', '=', $id]
            ]);
        })->where('user_status_code', '!=', 3)
            ->select('id', 'name', 'avatar')
            ->union($temp)
            ->get();

        // bug: relation attribute is relation bettween destination user and logged in user

        $message = "Thành công";
        return $this->handleResponse(200, $friends, $message);
    }

    public function getPosts($id)
    {
        $posts = Post::with(['user:id,name,avatar', 'media'])
            ->where([
                ['user_id', '=', $id],
                ['type', '=', 1]
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $posts, $message);
    }
}
