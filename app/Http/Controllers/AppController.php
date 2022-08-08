<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AppController extends Controller
{
    /*
        login
    */
    public function login(Request $request){
        if ($request->session()->exists('ACCOUNT_LOGIN')) {
            return redirect()->back();
        }else{
            return view('layouts.login', ['errors' => '']);
        }
    }

    public function accountlogin(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email',
            'password' => 'bail|required',
        ]);
        if ($validator->fails()) {
            return view('layouts.login', ['errors' => 'Email hoặc password không hợp lệ!']);
        }
        $login = $request->only(['email', 'password']);
        $datalogin= $this->callApi('/account/login',$login,'login',0,'post',true);
        if (isset($datalogin) && empty($datalogin['errorCode'])) {
            $request->session()->put('ACCOUNT_INFO', $datalogin['data']);
            $request->session()->put('ACCOUNT_LOGIN', $datalogin['token']);
            return redirect()->route('homepage');
        }else{
            $mess= isset($datalogin['message'])?$datalogin['message']:'';
            return view('layouts.login', ['errors' => $mess]);
        }
        return redirect()->route('login');
        
    }

    public function logout(Request $request){
        $request->session()->forget('ACCOUNT_LOGIN');
        $request->session()->flush();
        return redirect()->route('login');
    }
}
