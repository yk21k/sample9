<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopMember extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'shop_id',
        'user_id',
        'name',
        'file1',
        'file2',
        'role',
        'email',
        'invite_token',
        'invited_at',
        'invite_expires_at',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    public function isManagerOrOwner()
    {
        return in_array($this->role, ['owner', 'manager']);
    }

    public function canEditProduct()
    {
        return in_array($this->role, ['owner', 'manager', 'staff']);
    }

    public function canManageStaff()
    {
        return in_array($this->role, ['owner', 'manager']);
    }

    public function inviteLogs()
    {
        return $this->hasMany(InviteLog::class);
    }

    public function isRegistered()
    {
        return !is_null($this->user_id);
    }

    public function isInvited()
    {
        return
            !is_null($this->invite_token)
            &&
            !is_null($this->invite_expires_at)
            &&
            now()->lt($this->invite_expires_at);
    }

    public function isExpired()
    {
        return
            !is_null($this->invite_expires_at)
            &&
            now()->gte($this->invite_expires_at);
    }

    public function isPending()
    {
        return
            is_null($this->user_id)
            &&
            is_null($this->invite_token);
    }
}
