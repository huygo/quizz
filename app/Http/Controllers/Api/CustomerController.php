<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Khachhang;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Elasticsearch\ClientBuilder;

use Tymon\JWTAuth\Exceptions\JWTException;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|numeric|min:1',
            'per_page' => 'required|numeric|min:1|max:100',
        ]);
        if($validator->fails()){
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $datapost = $request->all();
        $request->merge([
            'page' => $datapost['page'],
        ]);
        if (isset($datapost['name']) && !empty($datapost['name']) && isset($datapost['phone']) && !empty($datapost['phone'])) {
            $data =Khachhang::where('status',1)->where('name', 'LIKE','%'.$datapost['name'].'%')->where('phone', 'LIKE','%'.$datapost['phone'].'%')->paginate($datapost['per_page'])->toArray();
        }elseif (isset($datapost['name']) && !empty($datapost['name'])) {
            $data =Khachhang::where('status',1)->where('name', 'LIKE','%'.$datapost['name'].'%')->paginate($datapost['per_page'])->toArray();
        }elseif (isset($datapost['phone']) && !empty($datapost['phone'])) {
            $data =Khachhang::where('status',1)->where('phone', 'LIKE','%'.$datapost['phone'].'%')->paginate($datapost['per_page'])->toArray();
        }else{
            $data =Khachhang::where('status',1)->paginate($datapost['per_page'])->toArray();
        }
        $page= array(
            'current_page' => isset($data['current_page'])?$data['current_page']:'',
            'from' => isset($data['from'])?$data['from']:'',
            'to' => isset($data['to'])?$data['to']:'',
            'last_page' => isset($data['last_page'])?$data['last_page']:'',
            'current_page' => isset($data['current_page'])?$data['current_page']:'',
            'total' => isset($data['total'])?$data['total']:'',
            'per_page' => isset($data['per_page'])?$data['per_page']:'',
        );
        return response()->json([
            'code'=>$this->statusCode,
            'success' => $this->statusApi,
            'message' => $this->message,
            'data'=>isset($data['data'])?$data['data']:'',
            'page'=>$page
        ], $this->statusCode);
    }

    public function detail(Request $request){
        $id = $request->only('id');
        $validator = Validator::make($id, [
            'id' => 'required|numeric|min:1',
        ]);
        if($validator->fails()){
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $customer = Khachhang::find($id);
        if (!$customer || count($customer)==0) {
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => 'Sorry, Customer not found.'
            ], 400);
        }
        return response()->json([
            'code'=>200,
            'success' => true,
            'message' => 'successfully',
            'data'=>isset($customer)?$customer:'',
        ], 200);
    }

    public function delete(Request $request){
        $id = $request->only('id');
        $validator = Validator::make($id, [
            'id' => 'required|numeric|min:1',
        ]);
        if($validator->fails()){
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        
    }

    
}
