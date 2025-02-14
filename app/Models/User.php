<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends \TCG\Voyager\Models\User
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified',
        'email_verify_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $dates = ['deleted_at'];



    public function shop()
    {
        return $this->hasOne(Shop::class, 'user_id');
    }

    /**
     * @return void
     */
    public static function booted(): void
    {
        static::deleted(function ($user) {
            $user->shop()->delete();
        });
    }

    public function forInq()
    {
        return $this->hasOne(CustomerInquiry::class, 'user_id');
    }

    public function forInqShop()
    {
        return $this->hasOne(Shop::class, 'shop_id');
    }

    public function inqForShop()
    {
        return $this->hasOne(Inquiries::class, 'user_id');
    }

    public function deleteShop()
    {
        return $this->hasOne(DeleteShop::class, 'user_id');
    }
}
