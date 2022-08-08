<?php

namespace App\Http\Controllers;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('layouts.customer.index');
        }else{
            return view('errors.404');
        }
    }

    public function search(Request $request){
        if ($request->isMethod('post')) {
            $datapost = $request->all();
            $kh= $this->listKhachhang($datapost);
            if (isset($kh) && empty($kh['errorCode']) && !empty($kh['data'])) {
                $data = isset($kh['data'])?$kh['data']:'';
                $page= isset($kh['page'])?$kh['page']:'';
                $view = view('layouts.customer.search',compact('data','page'));
                return $view->render();
            }else{
                $view = view('layouts.noitem');
                return $view->render();
            }
        }
    }

    private function listKhachhang($datapost=array()){
        $post= [];
        $post['page']= isset($datapost['page'])?$datapost['page']:1;
        $post['per_page']= isset($datapost['record'])?$datapost['record']:25;
        $post['name']= isset($datapost['name'])?$datapost['name']:'';
        $post['phone']= isset($datapost['phone'])?$datapost['phone']:'';
        $kh= $this->callApi('/customer/list',$post,'account',0,'post',false);
        return $kh;
    }

    //Xuất excel 
    public function excel(Request $request){
        if ($request->isMethod('post')) {
            $data =$request->all();
            $datakh = $this->listKhachhang($data);
            $itemsExcel = array();
            if (isset($datakh) && empty($datakh['errorCode']) && !empty($datakh['data'])) {
                foreach ($datakh['data'] as $key => $value) {
                    $itemsExcel[$key]['Name'] = $value['name'];
                    $itemsExcel[$key]['Điện thoại'] = $value['phone'];
                    $itemsExcel[$key]['Email'] = $value['email'];
                    $itemsExcel[$key]['Registrar'] = $value['registrar'];
                }
                $result = array ('status' => 1, 'itemsExcel' => $itemsExcel);
                return json_encode($result);
            }else{
                $itemsExcel['status'] = 0;
                return json_encode($itemsExcel);
            }
        }
    }

}
