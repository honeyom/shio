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
    public function edit(UserAddress $userAddress){
        $this->authorize('own',$userAddress);
        return view('',['address'=>$userAddress]);
    }
    public function update(UserAddress $userAddress,UserAddressRequest $request){
        $this->authorize('own',$userAddress);
        $userAddress->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
        return redirect()->route('user_address.index');
    }
    public function desctroy(UserAddress $userAddress){
        $this->authorize('own',$userAddress);
        $userAddress->delete();
        return redirect()->route('user_address.index');
    }

}
