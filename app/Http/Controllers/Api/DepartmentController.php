<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Exams;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class DepartmentController extends Controller
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
        $query = Department::where('del_flg', 0);
        $query->orderBy('id', 'ASC');
        if (isset($datapost['name']) && $datapost['name']!=''){
            $query->where('name','LIKE',"%".$datapost['name']."%");
        }
        $data= $query->paginate($datapost['per_page']);
        $data->each(function ($department) {
            $subject = Subject::where('department_id',$department->id)->where('del_flg',0)->orderByDesc('id')->limit(2)->get();
            $department->subjects= $subject ?? '';
        });
        $data= $data->toArray();
        $page= array(
            'current_page' => $data['current_page'] ?? '',
            'from' => $data['from'] ?? '',
            'to' => $data['to'] ?? '',
            'last_page' => $data['last_page'] ?? '',
            'current_page' => $data['current_page'] ?? '',
            'total' => $data['total'] ?? '',
            'per_page' => $data['per_page'] ?? '',
        );
        return response()->json([
            'code'=>$this->statusCode,
            'success' => $this->statusApi,
            'message' => $this->message,
            'data'=> $data['data'] ?? '',
            'page'=>$page
        ], $this->statusCode);
    }

    public function detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'required|numeric|min:1',
            'per_page' => 'required|numeric|min:1|max:100',
            'department_id' => 'required|numeric|min:1',
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
        $checkdepartment = Department::where('del_flg', 0)->where('id',$datapost['department_id'])->first();
        if (empty($checkdepartment)) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>['department_id'=>[0=>'department_id do not match']]
            ], 404);
        }
        $department=$checkdepartment->toArray();
        $request->merge([
            'page' => $datapost['page'],
        ]);
        $query = Subject::leftJoin('department', 
            function($join) {
              $join->on('subject.department_id', '=', 'department.id');
              $join->on('subject.del_flg', '=', 'department.del_flg');
            });
        $query->select('subject.id','subject.name','subject.department_id','department.name as department','subject.image','subject.created_date','subject.updated_date');
        $query->where('subject.department_id', $checkdepartment['id']);
        $query->where('subject.del_flg', 0);
        $query->orderBy('subject.department_id', 'ASC');
        $data= $query->paginate($datapost['per_page'])->toArray();
        if (isset($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $count_exams = Exams::select('id')->where('subject_id',$value['id'])->where('del_flg',0)->get()->count();
                $data['data'][$key]['count_exams']= isset($count_exams)?$count_exams:0;
                // $exams = Exams::where('subject_id',$value['id'])->where('del_flg',0)->paginate(10)->toArray();
                // $data['data'][$key]['subject']= isset($exams['data'])?$exams['data']:[];
            }
        }
        $department['subject']=$data['data'];
        $page= array(
            'current_page' => $data['current_page'] ?? '',
            'from' => $data['from'] ?? '',
            'to' => $data['to'] ?? '',
            'last_page' => $data['last_page'] ?? '',
            'current_page' => $data['current_page'] ?? '',
            'total' => $data['total'] ?? '',
            'per_page' => $data['per_page'] ?? '',
        );
        return response()->json([
            'code'=>$this->statusCode,
            'success' => $this->statusApi,
            'message' => $this->message,
            'data'=> $department ?? '',
            'page'=>$page
        ], $this->statusCode);
    }

    
}
