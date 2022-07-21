<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'name',
        'type'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'counts',
        'preview_member',
        'current_user_status'
    ];

    /**
     * Get admin group
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get member
     */
    public function members()
    {
        return $this->hasMany(MemberGroup::class);
    }

    /**
     * Get counts
     */
    public function getCountsAttribute()
    {
        $member_count = MemberGroup::where([
            ['group_id', '=', $this->id],
            ['member_group_status_code', '=', 1]
        ])->count();
        $post_count = PostGroup::where('group_id', '=', $this->id)
            ->count();

        $counts["member_count"] = $member_count + 1;
        $counts["post_count"] = $post_count;

        return $counts;
    }

    /**
     * Get preview member
     */
    public function getPreviewMemberAttribute()
    {
        $preview_member = MemberGroup::with(['user:id,name,avatar'])
            ->where([
                ['group_id', '=', $this->id],
                ['member_group_status_code', '=', 1]
            ])->first();

        return $preview_member;
    }

    /**
     * Get current user relation with group
     */
    public function getCurrentUserStatusAttribute()
    {
        if (auth()->id() == $this->admin_id) {
            return "joined";
        }
        $relation = MemberGroup::where([
            ['group_id', '=', $this->id],
            ['user_id', '=', auth()->id()]
        ])->first();

        if ($relation) {
            if ($relation->member_group_status_code == 0) {
                return "requested";
            }
            if ($relation->member_group_status_code == 1) {
                return "joined";
            }
        }
        return "guest";
    }
}
