<?php

namespace App\Http\Middleware;
// use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Closure;
use App\Service\jwtService;
use App\Http\Controllers\jwtController;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Auth;
class JwtMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        // $key = config('contants.secret');
        // JWT::$leeway = 60;
        try {
            $decoded = (new jwtService)->gettokendecode($request->bearerToken());
            // $decoded = (new jwtController)->gettokendecode($request->bearerToken());
            // JWT::decode($request->bearerToken(), new Key($key, 'HS256'));
            $decoded_array = (array) $decoded;
            $decoded_data = (array) $decoded_array;
            $user=User::query();
            $user=$user->where('email',$decoded_data['email'])->get();
            if(isset($user))
            {

                if($user[0]->verify==1)
                {

                    if (!Hash::check($decoded_data['password'], $user[0]->password)) {
                        return response()->json(['status' => 'Not a valid user token']);
                    }

                }
                else
                {

                    return response()->json(['error' => 'Please verify the link first'], 401);
                }
            }
            else
            {

                return response()->json(['status' => 'Not a valid user token']);
            }

        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->json(['status' => 'Token is Invalid','Error' => $e->getMessage()]);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->json(['status' => 'Token is Expired']);
            }else{
                return response()->json(['Message' => "Authorization Token not found",'Error' => $e->getMessage()]);
                // return response()->json(['status' => "Authorization Token not found"]);
            }
        }
        return $next($request);
    }
}
