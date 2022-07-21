<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'caption',
        'type'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'counts',
        'group',
        'react_count',
        'react_status'
    ];

    /**
     * Get user who posted
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get media resource if have.
     */
    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Get react count resource if have.
     */
    public function getReactCountAttribute()
    {
        $react_type_1 = ReactPost::where([
            ['post_id', '=', $this->id],
            ['type', '=', 1]
        ])->count();
        $react_type_2 = ReactPost::where([
            ['post_id', '=', $this->id],
            ['type', '=', 2]
        ])->count();
        $react_type_3 = ReactPost::where([
            ['post_id', '=', $this->id],
            ['type', '=', 3]
        ])->count();
        $react_type_4 = ReactPost::where([
            ['post_id', '=', $this->id],
            ['type', '=', 4]
        ])->count();
        $react_type_5 = ReactPost::where([
            ['post_id', '=', $this->id],
            ['type', '=', 5]
        ])->count();
        $react_type_6 = ReactPost::where([
            ['post_id', '=', $this->id],
            ['type', '=', 6]
        ])->count();

        $react_count["react_type_1"] = $react_type_1;
        $react_count["react_type_2"] = $react_type_2;
        $react_count["react_type_3"] = $react_type_3;
        $react_count["react_type_4"] = $react_type_4;
        $react_count["react_type_5"] = $react_type_5;
        $react_count["react_type_6"] = $react_type_6;

        return $react_count;
    }

    /**
     * Get counts user resource if have.
     */
    public function getCountsAttribute()
    {
        $react_count = ReactPost::where('post_id', '=', $this->id)
            ->count();
        $comment_count = CommentPost::where('post_id', '=', $this->id)
            ->count();

        $counts["react_count"] = $react_count;
        $counts["comment_count"] = $comment_count;

        return $counts;
    }

    /**
     * Get group resource if have.
     */
    public function getGroupAttribute()
    {
        $group_temp = PostGroup::with(['group:id,name'])
            ->where('post_id', '=', $this->id)
            ->first();

        if ($group_temp) {
            $group['id'] = $group_temp->group->id;
            $group['name'] = $group_temp->group->name;
            return $group;
        }
        return null;
    }

    /**
     * Get react status of user logged with post.
     */
    public function getReactStatusAttribute()
    {
        $react = ReactPost::where([
            ['post_id', '=', $this->id],
            ['user_id', '=', auth()->id()]
        ])->first();

        $react_status = 0;
        if ($react) {
            $react_status = $react->type;
        }
        return $react_status;
    }
}
