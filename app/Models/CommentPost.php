<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class CommentPost extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comment_post';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'content'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'counts',
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
        $react_type_1 = ReactComment::where([
            ['comment_id', '=', $this->id],
            ['type', '=', 1]
        ])->count();
        $react_type_2 = ReactComment::where([
            ['comment_id', '=', $this->id],
            ['type', '=', 2]
        ])->count();
        $react_type_3 = ReactComment::where([
            ['comment_id', '=', $this->id],
            ['type', '=', 3]
        ])->count();
        $react_type_4 = ReactComment::where([
            ['comment_id', '=', $this->id],
            ['type', '=', 4]
        ])->count();
        $react_type_5 = ReactComment::where([
            ['comment_id', '=', $this->id],
            ['type', '=', 5]
        ])->count();
        $react_type_6 = ReactComment::where([
            ['comment_id', '=', $this->id],
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
        $react_count = ReactComment::where('comment_id', '=', $this->id)
            ->count();

        $counts["react_count"] = $react_count;

        return $counts;
    }

    /**
     * Get react status of user logged with comment.
     */
    public function getReactStatusAttribute()
    {
        $react = ReactComment::where([
            ['comment_id', '=', $this->id],
            ['user_id', '=', auth()->id()]
        ])->first();

        $react_status = 0;
        if ($react) {
            $react_status = $react->type;
        }
        return $react_status;
    }
}
