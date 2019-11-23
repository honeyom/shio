<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    //
    protected $fillable=['province','city','district','address','mob_phone','tel_phone','real_name','is_default','user_id'];
    public function  user(){
        return $this->belongsTo(User::class);
    }
    public function getFullAddressAttribute(){
        return "{$this->province}{$this->city}{$this->distrct}{$this->address}";
    }
}
