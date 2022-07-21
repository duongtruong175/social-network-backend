<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'room_chat_id'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_friend'
    ];

    /**
     * Get user send friend request.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get user receiver friend request.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get relation between the user and user logged in.
     */
    public function getIsFriendAttribute()
    {
        $relation = RelationshipFriend::where([
            ['sender_id', '=', $this->sender_id],
            ['receiver_id', '=', $this->receiver_id],
            ['friend_status_code', '=', 1]
        ])->orWhere(function ($query) {
            $query->where('sender_id', '=', $this->receiver_id)
                ->where('receiver_id', '=', $this->sender_id)
                ->where('friend_status_code', '=', 1);
        })->first();
        if ($relation) {
            return true;
        }
        return false;
    }
}
