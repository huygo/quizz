<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Exams;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class HomepageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
        $query = Subject::leftJoin('department', 
            function($join) {
              $join->on('subject.department_id', '=', 'department.id');
              $join->on('subject.del_flg', '=', 'department.del_flg');
            });
        $query->select('subject.id','subject.name','subject.department_id','department.name as department','subject.image','subject.created_date','subject.updated_date');
        $query->where('subject.del_flg', 0);
        $query->orderBy('subject.department_id', 'ASC');
        $data= $query->paginate($datapost['per_page'])->toArray();
        if (isset($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $count_exams = Exams::select('id')->where('subject_id',$value['id'])->where('del_flg',0)->get()->count();
                $data['data'][$key]['count_exams']= isset($count_exams)?$count_exams:0;
            }
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

    public function timeStamp(Request $request){
        return response()->json([
            'code'=>$this->statusCode,
            'success' => $this->statusApi,
            'message' => $this->message,
            'data'=>['timestamp'=>date("Y-m-d H:i:s")],
        ], $this->statusCode);
    }
    
}
