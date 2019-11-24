<?php

namespace App;

use App\Models\Product;
use App\Models\UserAddress;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone', 'email', 'password', 'introduction', 'avatar',
        'weixin_openid', 'weixin_unionid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function address(){
        return $this->hasMany(UserAddress::class);
    }
    public function favoriteProducts(){
        return $this->belongsToMany(Product::class,'user_favorite_products')
            ->withTimestamps()//中间表带有时间戳字段
            ->orderBy('user_favorite_products.create_at','desc');
    }
}
