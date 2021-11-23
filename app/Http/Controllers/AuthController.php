<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\post;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\jwtController;
use App\Http\Requests\UserStoreRequest;
use App\Service\jwtService;
use Exception;
use MongoDB\Client as Mongo;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Validation\Rules\Exists;

class AuthController extends Controller
{

    public function register(UserStoreRequest $request) {

        $validator=$request;
        $user=(new Mongo)->jtchat->users->insertOne(
            array_merge(
                $validator->validated(),
                ['password' =>Hash::make($request->password)],
                ['verify' =>0]
            ));
        // $user = User::create(array_merge(
        //             $validator->validated(),
        //             ['password' =>Hash::make($request->password)]
        //         ));
        $mail=[
            'name'=>$request->name,
            'info'=>'Press the following link to verify your account',
            'Verification_link'=>url('api/user/verifyMail/'.$request->email)
        ];
        // $jwt=(new jwtController)->gettokenencode($validator->validated());
        $jwt=(new jwtService)->gettokenencode($validator->validated());
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
                // $user=(new jwtController)->gettokendecode($request->bearerToken());
            $user=(new jwtService)->gettokendecode($request->bearerToken());
        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->error(['status' => 'Token is Invalid'],400);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->error(['status' => 'Token is Expired'],400);
            }else{
                return response()->error(['status' => "Authorization Token not found"]);
            }
        }
            // $authenticate=User::query();
            // $authenticate=$authenticate->where('email',$user['email'])->get();
            $authenticate=(new Mongo)->jtchat;
            $authenticate=$authenticate->users->find(["email"=>$user['email']])->toArray();
            $jwt=$request->bearerToken();
            if(isset($authenticate))
            {
                if($authenticate[0]['verify']==1)
                {
                    if (Hash::check($user['password'], $authenticate[0]['password'])) {
                        $data['id']=$authenticate[0]['_id'];
                        $data['name']=$authenticate[0]['name'];
                        $data['email']=$authenticate[0]['email'];
                        $data['password']=$authenticate[0]['password'];
                    }
                    else
                    {
                        return response()->error(['error' => 'Unauthorized'], 401);
                    }
                }
                else
                {
                    return response()->error(['error' => 'Please verify the link first'], 401);
                }

            }
            else
            {
                return response()->error(['error' => 'Unauthorized'], 401);
            }

            return response()->success([
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
            // $authenticate=User::query();
            // $authenticate=$authenticate->where('email',$user['email'])->get();
            $authenticate=(new Mongo)->jtchat;
            $authenticate=$authenticate->users->find(["email"=>$user['email']])->toArray();
            if(isset($authenticate))
            {
                if($authenticate[0]['verify']==1)
                {
                    if (Hash::check($user['password'], $authenticate[0]['password'])) {
                        $data['name']=$authenticate[0]['name'];
                        $data['email']=$authenticate[0]['email'];
                        $data['password']=$user['password'];
                        $jwt=(new jwtService)->gettokenencode($data);
                        // $jwt=(new jwtController)->gettokenencode($data);
                    }
                    else
                    {
                        return response()->error(['Message' => 'Unauthorized'], 401);
                    }
                }
                else
                {
                    return response()->error(['Message' => 'Please verify the link first'], 401);
                }

            }
            else
            {
                return response()->error(['Message' => 'Unauthorized'], 401);
            }

            return response()->success([
                'message' => 'User successfully login',
                'bearer'=>$jwt
            ], 201);
        }

        return response()->error([
            'message' => 'login unsuccessfull. Make Sure input or token is given',
        ], 201);

    }

    public function verify($email)
    {
        // if(User::where("email",$email)->value('verify') == 1)
        $users=(new Mongo)->jtchat;
        $user=$users->users->find(["email"=>$email])->toArray();
        if($user[0]['verify'])
        {
            return response()->error([
                'message' => 'You have already verified your account',
            ],201);
        }
        else
        {
            if($users->users->updateOne([ 'email' => $email ],[ '$set' => [ 'verify' => 1 ]]))
            {
                return response()->success([
                    'message' => 'Your account is verified. ',
                ],200);
            }else{
                return response()->error([
                    'message' => 'Invalid Email. ',
                ],201);
            }
        }
    }

}
