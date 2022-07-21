<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'media_id',
        'end_time'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'end_time' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'viewed',
        'viewer_count'
    ];

    /**
     * Get the user that owns the story.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the media.
     */
    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Get viewer count
     */
    public function getViewerCountAttribute()
    {
        $viewer_count = ViewerStory::where([
            ['story_id', '=', $this->id]
        ])->count();

        return $viewer_count;
    }

    /**
     * Check user logged in view story or not ?
     */
    public function getViewedAttribute()
    {
        $viewer = ViewerStory::where([
            ['story_id', '=', $this->id],
            ['user_id', '=', auth()->id()]
        ])->first();

        if ($viewer) {
            return true;
        }
        return false;
    }
}
