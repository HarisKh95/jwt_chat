<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\post;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\jwtController;
use App\Service\jwtService;
use App\Http\Requests\UserStoreRequest;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Validation\Rules\Exists;

class AuthController extends Controller
{

    public function register(UserStoreRequest $request)
    {
        try {
            $validator=$request;
            $user = User::create(array_merge(
                        $validator->validated(),
                        ['password' =>Hash::make($request->password)]
                    ));
            $mail=[
                'name'=>$request->name,
                'info'=>'Press the following link to verify your account',
                'Verification_link'=>url('api/user/verifyMail/'.$request->email)
            ];
            $jwt=(new jwtService)->gettokenencode($validator->validated());
            // \Mail::to($request->email)->send(new \App\Mail\NewMail($mail));
            dispatch(new \App\Jobs\SendEmailVerify($request->email,$mail));
            return response()->success([
                'message' => 'User successfully registered',
                'token'=>$jwt,
                'user' => $user
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),203);
        }

    }

    public function login(Request $request){

        if($request->hasHeader('Authorization'))
        {
            try {
            $user=(new jwtService)->gettokendecode($request->bearerToken());
            $authenticate=User::query();
            $authenticate=$authenticate->where('email',$user['email'])->get();
            $jwt=$request->bearerToken();
            if(isset($authenticate))
            {
                if($authenticate[0]->verify==1)
                {
                    if (Hash::check($user['password'], $authenticate[0]->password)) {
                        $data['id']=$authenticate[0]->id;
                        $data['name']=$authenticate[0]->name;
                        $data['email']=$authenticate[0]->email;
                        $data['password']=$authenticate[0]->password;
                    }
                    else
                    {
                        throw new Exception('Unauthorized');
                    }
                }
                else
                {
                    throw new Exception('Please verify the link first');
                }

            }
            else
            {
                throw new Exception('Unauthorized');
            }

            return response()->success([
                'message' => 'User successfully login',
                'bearer'=>$jwt,
                'user' => $data
            ], 201);
        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->error(['status' => 'Token is Invalid'],401);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->error(['status' => 'Token is Expired'],401);
            }else{
                return response()->error($e->getMessage(),401);
            }
        }

        }
        else
        {
            try {
                $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                    'password' => 'required|string|min:6',
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }
                $user=$validator->validated();
                $authenticate=User::query();
                $authenticate=$authenticate->where('email',$user['email'])->get();
                if(isset($authenticate))
                {
                    if($authenticate[0]->verify==1)
                    {
                        if (Hash::check($user['password'], $authenticate[0]->password)) {
                            $data['name']=$authenticate[0]->name;
                            $data['email']=$authenticate[0]->email;
                            $data['password']=$user['password'];
                            $jwt=(new jwtService)->gettokenencode($data);
                        }
                        else
                        {
                            throw new Exception('Unauthorized');
                        }
                    }
                    else
                    {
                        throw new Exception('Please verify the link first');
                    }

                }
                else
                {
                    throw new Exception('Unauthorized');
                }

                return response()->success([
                    'message' => 'User successfully login',
                    'user' => $data,
                    'bearer'=>$jwt
                ], 201);
            } catch (Exception $e) {
                if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                    return response()->error('Token is Invalid',401);
                }else if ($e instanceof \Firebase\JWT\ExpiredException){
                    return response()->error('Token is Expired',401);
                }else{
                    return response()->error($e->getMessage(),401);
                }
            }
        }

        return response()->error([
            'message' => 'login unsuccessfull. Make Sure input or token is given',
        ], 201);

    }

    public function verify($email)
    {
        try {
            if(User::where("email",$email)->value('verify') == 1)
            {
                throw new Exception('You have already verified your account');
            }
            else
            {
                $update=User::where("email",$email)->update(["verify"=>1]);
                if($update){
                    throw new Exception('Your account is verified.');
                }else{
                    throw new Exception('Invalid Email. ');
                }
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(),200);
        }

    }

    public function list(Request $request)
    {
        try {
            $user=(new jwtService)->gettokendecode($request->bearerToken());
            $alluser=User::query()->where('email','!=',$user['email'])->get();
            $index=0;
            foreach($alluser as $user)
            {
                $data[$index]['name']=$user->name;
                $data[$index]['email']=$user->email;
                $index++;
            }
            return response()->json([
                'message' => 'All users list',
                'user' => $data
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }

    }


}
