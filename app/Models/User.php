<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'name',
        'birthday',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'job' => 'array',
        'education' => 'array'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'counts',
        'relation'
    ];

    /**
     * Get counts user resource if have.
     */
    public function getCountsAttribute()
    {
        $friend_count = RelationshipFriend::where('friend_status_code', '=', 1)
            ->where(function ($query) {
                $query->where('sender_id', '=', $this->id)
                    ->orWhere('receiver_id', '=', $this->id);
            })->count();
        $post_count = Post::where('user_id', '=', $this->id)
            ->count();
        $react_count = ReactPost::join('posts', 'posts.id', '=', 'react_post.post_id')
            ->where('posts.user_id', '=', $this->id)
            ->count();

        $counts["follow_count"] = $friend_count;
        $counts["react_count"] = $react_count;
        $counts["post_count"] = $post_count;
        $counts["friend_count"] = $friend_count;

        return $counts;
    }

    /**
     * Get relation between the user and user logged in.
     */
    public function getRelationAttribute()
    {
        $relation = RelationshipFriend::where(function ($query) {
            $query->where('sender_id', '=', $this->id)
                ->Where('receiver_id', '=', auth()->id());
        })->orWhere(function ($query) {
            $query->where('sender_id', '=', auth()->id())
                ->Where('receiver_id', '=', $this->id);
        })->first();

        if ($relation) {
            if ($relation->friend_status_code == 1) {
                return "friend";
            }
            if ($relation->friend_status_code == 0) {
                if ($relation->sender_id == $this->id) {
                    return "sender";
                }
                if ($relation->receiver_id == $this->id) {
                    return "receiver";
                }
            }
        }

        return "";
    }

    /**
     * Get friend
     */
    public function sender()
    {
        return $this->hasMany(RelationshipFriend::class, 'sender_id', 'id');
    }

    /**
     * Get friend
     */
    public function receiver()
    {
        return $this->hasMany(RelationshipFriend::class, 'receiver_id', 'id');
    }
}
