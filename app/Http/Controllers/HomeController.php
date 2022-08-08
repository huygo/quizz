<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $hihi= $this->code_ramdom();
        return view('layouts.home',compact('hihi'));
    }

    public function test()
    {
        $datalogin= $this->callApi('/account/show',['id'=>23],'account',0,'post',false);
        echo "sjdkifhjskdf";
        print_r($datalogin);
    }
}
