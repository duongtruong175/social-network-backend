<?php

namespace App\Http\Controllers\Api;

use App\Models\Media;
use App\Models\RelationshipFriend;
use App\Models\Story;
use App\Models\ViewerStory;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Validator;

class StoryController extends BaseController
{
    public function createStory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media' => 'required|image|mimes:jpeg,jpg,png,gif,webp',
            'type' => ['required', 'string', Rule::in(['image', 'video'])]
        ]);

        if ($validator->fails()) {
            $message = "Một số dữ liệu cung cấp không hợp lệ";
            return $this->handleError(422, $validator->errors(), $message);
        }

        $media_store = $this->uploadFileMedia($request->file('media'), $request->type);

        $media = Media::create([
            'type' => $media_store['type'],
            'name' => $media_store['name'],
            'domain' => $media_store['domain'],
            'path' => $media_store['path'],
            'src' => $media_store['src']
        ]);

        $story = Story::create([
            'user_id' => auth()->id(),
            'media_id' => $media->id,
            'end_time' => strtotime('+1 day')
        ]);

        $story->load(['user:id,name,avatar', 'media']);

        $message = "Tạo tin thành công";
        return $this->handleResponse(201, $story, $message);
    }

    public function viewStory($id)
    {
        $viewer_story = ViewerStory::firstOrCreate([
            'story_id' => $id,
            'user_id' => auth()->id()
        ]);

        $viewer_story->load(['user:id,name,avatar']);

        $message = "Xem tin thành công";
        return $this->handleResponse(201, $viewer_story, $message);
    }

    public function getViewerStory($id)
    {
        $viewer_story = ViewerStory::with(['user:id,name,avatar'])
            ->where('story_id', '=', $id)
            ->get();

        $message = "Thành công";
        return $this->handleResponse(200, $viewer_story, $message);
    }

    public function deleteStory($id)
    {
        $story = Story::with('media')->findOrFail($id);

        ViewerStory::where('story_id', '=', $story->id)
            ->delete();

        $media = $story->media;

        // delete related   
        $this->deleteFileMedia($media->src);
        $story->delete();
        Media::destroy($media->id);

        $message = "Xóa tin thành công";
        return $this->handleResponse(204, [], $message);
    }
}
