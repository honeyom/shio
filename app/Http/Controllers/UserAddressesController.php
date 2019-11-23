<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressesController extends Controller
{
    //
    public function create(){
        return view('',['address'=>new UserAddress()]);
    }

}
