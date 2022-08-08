<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Illuminate\Support\Facades\Hash;

use Tymon\JWTAuth\Exceptions\JWTException;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data= Account::all();
        return response()->json([
            'code'=>$this->statusCode,
            'success' => $this->statusApi,
            'message' => $this->message,
            'data'=>isset($data)?$data:'',
        ], $this->statusCode);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ipAddress = $request->ip();
        $response= array('status'=>200,'data'=>$ipAddress);
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = $request->only('id');
        $validator = Validator::make($id, [
            'id' => 'required|int',
        ]);
        //Send failed response if request is not valid
        if ($validator->fails()) {
            Log::channel('account')->error(\json_encode(['account_id'=>$request->id,'message' => $validator->messages()]));
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $account = Account::find($request->id);
        if (!$account) {
            Log::channel('account')->error(\json_encode(['account_id'=>$request->id,'message' => 'Sorry, account not found.']));
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => 'Sorry, Account not found.'
            ], 400);
        }
        return response()->json([
            'code'=>200,
            'success' => true,
            'message' => 'successfully',
            'data'=>isset($account)?$account:'',
        ], 200);
    }

    /*
        login
    */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email',
            'password' => 'bail|required',
        ]);
        if ($validator->fails()) {
            Log::channel('account')->error(\json_encode(['message' => $validator->messages()]));
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $credentials = $request->only(['email', 'password']);
        $login = Account::where('email',$credentials['email'])->where('status',1)->first();
        if (empty($login) || !Hash::check($credentials['password'],$login->password)) {
            Log::channel('account')->error(\json_encode(['data'=>$credentials,'message' => 'Username or password is notcorrect']));
            return response()->json([
                'code'=>400,
                'success' => false,
                'message' => 'Username or password is not correct'
            ], 400);
        }
        $ipAddress = $request->ip();
        $login->ip_login=$ipAddress;
        $useragent=$request->server('HTTP_USER_AGENT');
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
            $login->dcv_token=$useragent;
        }
        $login->save();
        $token = JWTAuth::fromUser($login);
        return response()->json([
            'code'=>200,
            'success' => true,
            'message' => 'successfully',
            'data'=>isset($login)?$login:'',
            'token'=>$token
        ], 200);
    }

    public function refresh()
    {
        try {
            return response()->json([
                'code'=>200,
                'success' => true,
                'message' => 'successfully',
                'data'=>'',
                'token'=>JWTAuth::refresh()
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>'could_not_refresh_token'
            ], 404);
        }
    }

    public function authenticate(Request $request)
    {
      $credentials = $request->only('email', 'password');
      try {
          if (!$token=JWTAuth::attempt($credentials)) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>'invalid_credentials'
            ], 404);
          }
      } catch (JWTException $e) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>'could_not_create_token'
            ], 404);
      }
      return response()->json(compact('token'));
    }


    public function getAuthenticatedUser()
    {
        try {
          if (!$user=JWTAuth::parseToken()->authenticate()) {
                  return response()->json(['user_not_found'], 404);
          }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json([
            'code'=>200,
            'success' => true,
            'message' => 'successfully',
            'data'=>isset($user)?$user:'',
        ], 200);
    }

    public function register(Request $request){
        $datasave = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:9',
            're_password' => 'required|string|min:9',
        ]);
        if($validator->fails()){
            Log::channel('account')->error('register '.$validator->messages());
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        if ($datasave['password']!=$datasave['re_password']) {
            Log::channel('account')->error('register password and re_password do not match');
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>['password'=>[0=>'password and re_password do not match']]
            ], 404);
        }
        $regex = '/^(?=.*\d)(?=.*[A-Z])(?=.*[@%!#$^*])[0-9A-Za-z_@%!#$^*]{9,50}$/';
        if (!preg_match($regex, $datasave['password'])) {
            Log::channel('account')->error('register Password must be at least 9 characters long and must contain at least 1 lowercase letter, 1 uppercase letter, 1 number, and 1 special character belonging to @#$%^*');
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>['password'=>[0=>'Password must be at least 9 characters long and must contain at least 1 lowercase letter, 1 uppercase letter, 1 number, and 1 special character belonging to @#$%^*']]
            ], 404);
        }
        $check = Account::where('email', '=',$datasave['email'])->first();
        if ($check!=null) {
            Log::channel('account')->error('register This email already exists');
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>['email'=>[0=>'This email already exists']]
            ], 404);
        }
        $datasave['status']=1;
        $datasave['password']=Hash::make($datasave['password']);
        $user = Account::create($datasave);
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'code'=>200,
            'success' => true,
            'message' => 'successfully',
            'data'=>isset($user)?$user:'',
            'token'=>$token
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function forgotPassword(Request $request){
        $datasave = $request->all();
        $validator = Validator::make($datasave, [
            'email' => 'bail|required|email',
        ]);
        if ($validator->fails()) {
            Log::channel('account')->error(\json_encode(['message' => $validator->messages()]));
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        $check = Account::where('email', '=',$datasave['email'])->first();
        if (empty($check)) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>'This email already exists '
            ], 404);
        }
        $token= $this->randomString();
        $check->action_code=$token;
        $check->action_date=date("Y-m-d H:i:s");
        $check->save();
        return response()->json([
            'code'=>200,
            'success' => true,
            'message' => 'successfully',
            'action_code'=> $token,
        ], 200);
    }

    public function forgotPasswordSucess(Request $request){
        $datasave = $request->all();
        $validator = Validator::make($datasave, [
            'email' => 'bail|required|email',
            'action_code' => 'required|string|min:6',
            'password' => 'required|string|min:9',
            're_password' => 'required|string|min:9',
        ]);
        if ($validator->fails()) {
            Log::channel('account')->error(\json_encode(['message' => $validator->messages()]));
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>$validator->messages()
            ], 404);
        }
        if ($datasave['password']!=$datasave['re_password']) {
            Log::channel('account')->error('register password and re_password do not match');
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>['password'=>[0=>'password and re_password do not match']]
            ], 404);
        }
        $regex = '/^(?=.*\d)(?=.*[A-Z])(?=.*[@%!#$^*])[0-9A-Za-z_@%!#$^*]{9,50}$/';
        if (!preg_match($regex, $datasave['password'])) {
            Log::channel('account')->error('register Password must be at least 9 characters long and must contain at least 1 lowercase letter, 1 uppercase letter, 1 number, and 1 special character belonging to @#$%^*');
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>['password'=>[0=>'Password must be at least 9 characters long and must contain at least 1 lowercase letter, 1 uppercase letter, 1 number, and 1 special character belonging to @#$%^*']]
            ], 404);
        }
        $check = Account::where('email',$datasave['email'])->where('action_code',$datasave['action_code'])->first();
        if (empty($check)) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>'This email or action_code already exists '
            ], 404);
        }
        $action_date = date('Y-m-d H:i:s', strtotime($check->action_date.' +30 minutes'));
        if (date("Y-m-d H:i:s")>$action_date) {
            return response()->json([
                'code'=>404,
                'success' => false,
                'message' =>'action_code is expired '
            ], 404);
        }
        $check->password=Hash::make($datasave['password']);
        $check->action_code=null;
        $check->action_date=null;
        $check->save();
        return response()->json([
            'code'=>200,
            'success' => true,
            'message' => 'successfully',
        ], 200);
    }

    private function randomString($chars = 6)
    {
        $this->autoRender = false;
        $letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $str = substr(str_shuffle($letters), 0, $chars);
        return $str;
    }

    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
