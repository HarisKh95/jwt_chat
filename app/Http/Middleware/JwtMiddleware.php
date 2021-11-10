<?php

namespace App\Http\Middleware;
// use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Closure;
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

        $key = "example_key";
        JWT::$leeway = 60;
        try {
            $decoded = JWT::decode($request->bearerToken(), new Key($key, 'HS256'));
            $decoded_array = (array) $decoded;
            $decoded_data = (array) $decoded_array['data'];
            $user=User::query();
            $user=$user->where('email',$decoded_data['email'])->get();
            if($user[0]->verify==1)
            {
                if(!isset($user))
                {
                    return response()->json(['status' => 'Not a valid user token']);
                }
                else
                {
                    if (!Hash::check($decoded_data['password'], $user[0]->password)) {
                        return response()->json(['status' => 'Not a valid user token']);
                    }
                }
            }
            else
            {
                return response()->json(['error' => 'Please verify the link first'], 401);
            }

        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->json(['status' => 'Token is Invalid']);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->json(['status' => 'Token is Expired']);
            }else{
                return response()->json(['status' => "Authorization Token not found"]);
            }
        }
        return $next($request);
    }
}
