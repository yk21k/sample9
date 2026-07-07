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
        'phone',
        'password',
        'email_verified',
        'email_verify_token',
        'stripe_account_id',
        'stripe_account_name'
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

    public function shops()
    {
        return $this->hasMany(Shop::class); // 店舗が複数ならこれ
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

    public function forChart()
    {
        return $this->hasOne(Shop::class, 'user_id');
    }

    public function subOrders()
    {
        return $this->hasMany(SubOrder::class, 'seller_id');
    }

    public function forMails()
    {
        return $this->hasMany(Mails::class, 'user_id');
    }

    // public function shopMember()
    // {
    //     return $this->hasOne(ShopMember::class);
    // }

    public function shopMember()
    {
        return $this->hasOne(
            ShopMember::class,
            'user_id', // shop_members側
            'id'       // users側
        );
    }

    public function currentShop()
    {
        // ========================================
        // owner の shop
        // ========================================
        if ($this->shop) {
            return $this->shop;
        }

        // ========================================
        // manager / staff の shop
        // ========================================
        if ($this->shopMember) {
            return $this->shopMember->shop;
        }

        // ========================================
        // 運営(admin)
        // ========================================
        return null;
    }

    public function productEditDrafts()
    {
        return $this->hasMany(
            ProductEditDraft::class,
            'created_by'
        );
    }


}
