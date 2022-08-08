<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Exams;
use App\Models\Questions;
use App\Models\Answers;
use App\Models\AccountLog;
use App\Models\AccountLogDetail;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\Exams\ExamsSubmitRequest;
use App\Http\Requests\Exams\ExamsDetailRequest;
use App\Http\Requests\Exams\ExamsListRequest;
use App\Services\LockingService;


class ExamsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(ExamsListRequest $request)
    {
        $datapost = $request->all();
        $request->merge([
            'page' => $datapost['page'],
        ]);
        $query = Exams::leftJoin('subject', 
            function($join) {
              $join->on('exams.subject_id', '=', 'subject.id');
              $join->on('exams.del_flg', '=', 'subject.del_flg');
            });
        $query->leftJoin('department', 
            function($join) {
              $join->on('exams.department_id', '=', 'department.id');
              $join->on('exams.del_flg', '=', 'department.del_flg');
            });
        $query->select('exams.id','exams.name','department.name as department','exams.department_id','subject.name as subject','exams.subject_id','exams.time_count','exams.image','exams.created_date','exams.updated_date');
        $query->where('exams.del_flg', 0);
        $query->orderBy('exams.id', 'ASC');
        if (isset($datapost['subject_id']) && $datapost['subject_id']!=''){
            $query->where('exams.subject_id',$datapost['subject_id']);
        }
        if (isset($datapost['department_id']) && $datapost['department_id']!=''){
            $query->where('exams.department_id',$datapost['department_id']);
        }
        $data= $query->paginate($datapost['per_page']);
        $data->each(function ($exam, $key) {
            $subject = Questions::where('exams_id',$exam->id)->where('del_flg',0)->count();
            $exam->question_count=$subject;
        });
        $data= $data->toArray();
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

    public function detail(ExamsDetailRequest $request)
    {
        try {
            if (!$user=JWTAuth::user()) {
                throw new \Exception("User not valid");
            }
            $datapost = $request->all();
            $request->merge([
                'page' => $datapost['page'],
            ]);
            $checkexams = Exams::where('del_flg', 0)->where('id',$datapost['exam_id'])->first();
            if (empty($checkexams)) {
                return response()->json([
                    'code'=>404,
                    'success' => false,
                    'message' =>['exam_id'=>[0=>'exam_id do not match']]
                ], 404);
            }
            //$exams=$checkexams->toArray();
            $request->merge([
                'page' => $datapost['page'],
            ]);
            $query = Questions::where('exams_id', $datapost['exam_id']);
            $query->where('del_flg', 0);
            $query->orderBy('id', 'ASC');
            $data= $query->paginate($datapost['per_page'])->toArray();
            if (count($data['data'])==0) {
                return response()->json([
                    'code'=>404,
                    'success' => false,
                    'message' =>['exam_id'=>[0=>'Đề thi chưa có câu hỏi nào!']]
                ], 404);
            }
            $keyLock='detail_'.$user['id'].'_'.$datapost['exam_id'] ?? 0;
            if (!LockingService::checkLocking($keyLock)) {
                throw new \Exception("Bạn chỉ được làm 5 lần 1 ngày/1 đề, để làm thêm vui lòng nâng cấp tài khoản");
            }
            // $checkaccountlog = AccountLog::where('del_flg', 0)
            //     ->where('exam_id',$datapost['exam_id'])
            //     ->where('account_id',$user['id'])
            //     ->first();
            DB::beginTransaction();
            $insert = new AccountLog;
            $insert->account_id = $user['id'];
            $insert->exam_id = $datapost['exam_id'];
            $insert->time_start = date("Y-m-d H:i:s");
            $insert->time_count = (int)$checkexams->time_count;
            if (!$insert->save()) {
                throw new \Exception("Save information failed");
            }
            $id_account_log= $insert->id;
            $data_insert_log_detail=[];
            if (isset($data['data'])) {
                foreach ($data['data'] as $key => $value) {
                    $answer = Answers::where('questions_id',$value['id'])->where('del_flg',0)->get()->toArray();
                    $data['data'][$key]['answers']= isset($answer)?$answer:[];
                    $data_insert_log_detail[] =['account_log_id'=>$id_account_log, 'questions_id'=> $value['id']];
                }
            }
            if (!AccountLogDetail::insert($data_insert_log_detail)) {
                    throw new \Exception("Save information failed");
            }
            DB::commit();
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
                'exam_id'=>$datapost['exam_id'],
                'page'=>$page
            ], $this->statusCode);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code'=> 404,
                'success' => false,
                'message' =>$e->getMessage(),
            ], 404);
        }
        
    }

    public function submit(ExamsSubmitRequest $request){
        $datapost = $request->all();
        $keyLock='submit_'.$datapost['exam_id'] ?? 0;
        try {
            if (LockingService::isLocking($keyLock, 5)) {
                throw new \Exception("Thao tác bị khóa, vui lòng thực hiện lại sau 5s");
            }
            if (!$user=JWTAuth::user()) {
                throw new \Exception("User not valid");
            }
            // lấy answer trả về
            $checkaccountlog = AccountLog::where('del_flg', 0)
            ->where('exam_id',$datapost['exam_id'])
            ->where('account_id',$user['id'])
            ->orderByDesc('id')
            ->first();
            if (empty($checkaccountlog)) {
                throw new \Exception("you haven't started your homework yet");
            }
            $checktime = date('Y-m-d H:i:s',strtotime('+'.$checkaccountlog->time_count.' minutes',strtotime($checkaccountlog->time_start)));
            // check thoi gian lam bai
            // if ($checktime<date("Y-m-d H:i:s")) {
            //     throw new \Exception("You've run out of time");
            // }
            $checkaccountlog->time_end= date("Y-m-d H:i:s");
            if (!$checkaccountlog->save()) {
               throw new \Exception("Save information failed");
            }
            $answer= $datapost['answer'];
            DB::beginTransaction();
            $data_log_detail= AccountLogDetail::where('account_log_id',$checkaccountlog->id)->get();
            $diem=0;
            $data_log_detail->each(function ($dataLog, $key) use($answer,$diem) {
                $result= Answers::select('id')->where('questions_id',$dataLog->questions_id)
                ->where('result',1)
                ->first();
                $log = AccountLogDetail::find($dataLog->id);
                foreach ($answer as $key1 => $value) {
                    if ($dataLog->questions_id==$value['quiz_id'] && $result->id==$value['answer']) {
                        $log->answer_id = $value['answer'];
                        $log->result = 1;
                        $log->update();
                        break;
                    }elseif ($dataLog->questions_id==$value['quiz_id'] && $result->id!=$value['answer']) {
                        $log->answer_id = $value['answer'];
                        $log->result = 0;
                        $log->update();
                        break;
                    }
                }
            });

            

            DB::commit();
            // response
            $query = Questions::where('exams_id', $datapost['exam_id']);
            $query->where('del_flg', 0);
            $query->orderBy('id', 'ASC');
            $data= $query->get()->toArray();
            foreach ($data as $key => $value) {
                
            }
            return response()->json([
                'code'=>200,
                'success' => true,
                'message' => 'successfully',
                'data'=> $data,
            ], 200);   
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$e->getMessage(),
            ], 404);
        } finally {
            LockingService::release($keyLock);
        }

    }


    
}
