<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationshipFriend extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'relationship_friend';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'relation',
        'user'
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
    public function getRelationAttribute()
    {
        if (($this->sender_id == auth()->id() || $this->receiver_id == auth()->id()) && $this->friend_status_code == 1) {
            return "friend";
        }
        if ($this->friend_status_code == 0) {
            if ($this->sender_id == auth()->id()) {
                return "sender";
            }
            if ($this->receiver_id == auth()->id()) {
                return "receiver";
            }
        }
        return "";
    }

    /**
     * Get information of user has relation with user logged in.
     */
    public function getUserAttribute()
    {
        if ($this->sender_id == auth()->id()) {
            $user = User::where('id', '=', $this->receiver_id)
                ->select('id', 'name', 'avatar')
                ->first();
            return $user;
        }
        if ($this->receiver_id == auth()->id()) {
            $user = User::where('id', '=', $this->sender_id)
                ->select('id', 'name', 'avatar')
                ->first();
            return $user;
        }
        return null;
    }
}
