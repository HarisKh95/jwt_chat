<?php

namespace App\Http\Middleware;
// use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Closure;
use Illuminate\Http\Request;
use App\Service\jwtService;
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

        // $key = "example_key";
        // JWT::$leeway = 60;
        try {
            $decoded = (new jwtService)->gettokendecode($request->bearerToken());
            // $decoded = JWT::decode($request->bearerToken(), new Key($key, 'HS256'));
            // $decoded_array = (array) $decoded;
            // $decoded_data = (array) $decoded_array['data'];

            $user=User::query();
            $user=$user->where('email',$decoded['email'])->first();
            if(isset($user))
            {

                if($user->verify==1)
                {

                    if (!Hash::check($decoded['password'], $user->password)) {
                        throw new Exception('Not a valid user token');
                    }

                }
                else
                {

                    throw new Exception('Please verify the link first');
                }
            }
            else
            {

                throw new Exception('Not a valid user token');
            }
        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->error('Token is Invalid',401);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->error('Token is Expired',401);
            }else{
                return response()->error($e->getMessage(),401);
            }
        }
        $request=$request->merge(array("data" => $decoded));
        return $next($request);
    }
}
