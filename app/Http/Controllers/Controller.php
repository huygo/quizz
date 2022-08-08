<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $statusCode = 200;
    protected $message = "successfully";
    protected $statusApi = true;
    protected $limit = 50;
    protected $page = 1;
    protected $keyJwt = 'hihi';
    protected $ENDPOINT_API = 'https://quanghuy.me/api';
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** code ramdom **/
    protected function code_ramdom($lenght = 12)
    {            
         $letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
         $str = substr(str_shuffle($letters), 0, $lenght);
         return $str;
    }

    /**
     * @param string $type
     * @param string $input
     * @return array
     */
    protected function validatePhoneNumber($input = '')
    {
        $result = [];
        $msg = '';
        if (!empty($input) && $input != '') {
            $regex = '/^[0-9]{9,20}$/';
            if (!preg_match($regex, $input)) {
                $msg = __('Số điện thoại phải là ký tự số và có từ 9 đến 12 ký tự');
            }
        } else {
            $msg = __('Vui lòng nhập số điện thoại');
        }
        if (!empty($msg)) {
            $result = ['name' => 'phone', 'msg' => $msg];
        }
        return $result;
    }

    /**
     * @param string $type
     * @param string $input
     * @return array
     */
    protected function validateEmail($input = '')
    {
        $result = [];
        $msg = '';
        if (!empty($input) && $input != '') {
            if (filter_var($input, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $input)) {

            } else {
                $msg = __('Email không đúng định dạng');
            }
        } else {
            $msg = __('Vui lòng nhập email');
        }
        if (!empty($msg)) {
            $result = ['name' => 'email', 'msg' => $msg];
        }
        return $result;

    }

    protected function callApi($url = '', $datapost = array(),$filename = '',$checkLog = 0, $method = 'post',$login=false){
        $bearer = session('ACCOUNT_LOGIN');
		if ($method == 'post') {
            if ($login==true) {
                $response = Http::post($this->ENDPOINT_API.$url, $datapost);
            }else{
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$bearer
                ])->post($this->ENDPOINT_API.$url, $datapost);
            }
		}elseif($method == 'get'){
            $response = Http::get($this->ENDPOINT_API.$url);
		}
        $dataApi = $response->json();
        if ($checkLog == 1) {        	
        	// $this->writelog($filename,json_encode(array('data'=>$dataApi)));
        }		
		
        if (isset($dataApi) && !empty($dataApi['code']) && $dataApi['code'] == 200) {
            $dataApi['errorCode']= '';  
        	return $dataApi;
        }elseif(!empty($dataApi['code']) && $dataApi['code'] == 401){
            Session::forget('ACCOUNT_LOGIN');
            Session::flush();
            // $dataApi['errorCode']= isset($dataApi['code'])?$dataApi['code']:'error';            
        	// return $dataApi;
            $result = ['status' => 100000];
            die(json_encode($result));
        }else{
            $dataApi['errorCode']= isset($dataApi['code'])?$dataApi['code']:'error';            
        	return $dataApi;
        }
	}
}
