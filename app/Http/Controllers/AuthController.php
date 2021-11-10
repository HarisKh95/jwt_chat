<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\jwtController;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Validation\Rules\Exists;

class AuthController extends Controller
{

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' =>Hash::make($request->password)]
                ));
        $mail=[
            'name'=>$request->name,
            'info'=>'Press the following link to verify your account',
            'Verification_link'=>url('api/verifyMail/'.$request->email)
        ];
        $jwt=(new jwtController)->gettokenencode($validator->validated());
        \Mail::to($request->email)->send(new \App\Mail\NewMail($mail));
        return response()->json([
            'message' => 'User successfully registered',
            'token'=>$jwt,
            'user' => $user
        ], 201);
    }

    public function login(Request $request){

        if($request->hasHeader('Authorization'))
        {
            try {
            $user=(new jwtController)->gettokendecode($request->bearerToken());
        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->json(['status' => 'Token is Invalid']);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->json(['status' => 'Token is Expired']);
            }else{
                return response()->json(['status' => "Authorization Token not found"]);
            }
        }
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
                        return response()->json(['error' => 'Unauthorized'], 401);
                    }
                }
                else
                {
                    return response()->json(['error' => 'Please verify the link first'], 401);
                }

            }
            else
            {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json([
                'message' => 'User successfully login',
                'bearer'=>$jwt,
                'user' => $data
            ], 201);
        }
        else
        {
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
                        $jwt=(new jwtController)->gettokenencode($data);
                    }
                    else
                    {
                        return response()->json(['error' => 'Unauthorized'], 401);
                    }
                }
                else
                {
                    return response()->json(['error' => 'Please verify the link first'], 401);
                }

            }
            else
            {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json([
                'message' => 'User successfully login',
                'user' => $data,
                'bearer'=>$jwt
            ], 201);
        }

        return response()->json([
            'message' => 'login unsuccessfull. Make Sure input or token is given',
        ], 201);

    }

    public function verify($email)
    {
        if(User::where("email",$email)->value('verify') == 1)
        {
            return response()->json([
                'message' => 'You have already verified your account',
            ],200);
        }
        else
        {
            $update=User::where("email",$email)->update(["verify"=>1]);
            if($update){
                return response()->json([
                    'message' => 'Your account is verified. ',
                ],200);
            }else{
                return response()->json([
                    'message' => 'Invalid Email. ',
                ],200);
            }
        }
    }

    public function list(Request $request)
    {
        $user=(new jwtController)->gettokendecode($request->bearerToken());
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
    }
}
