<?php

namespace App\Http\Controllers;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use App\Models\Friend;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

class FriendController extends Controller
{
    // protected $data;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    // public function __construct(Request $request)
    // {
    //     $this->data = (new jwtController)->gettokendecode($request->bearerToken());
    // }

    public function addfriend(Request $request)
    {
        try {
            $user=User::where('email','=',$request->data['email'])->first();
            $userf=$user->friends()->get();
            $friends=$userf->toArray();
            if(!empty($friends))
            {
                foreach($friends as $friend)
                {

                    if($friend['pivot']['friend_id']==$request->id)
                    {
                        throw new Exception('Friend Aready exist');
                    }
                }
            }
            $user=$user->friends()->attach([$request->id]);
            return response()->success([
                'message' => 'Friend Added'
            ],200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),406);
        }

    }

    public function removefriend(Request $request)
    {
        try {
            $user=User::where('email','=',$request->data['email'])->first();
            $userf=$user->friends()->get();
            $friends=$userf->toArray();
            if(!empty($friends))
            {
                foreach($friends as $friend)
                {

                    if($friend['pivot']['friend_id']==$request->id)
                    {
                        $user=$user->friends()->detach([$request->id]);
                        return response()->success([
                            'message' => 'Friend remove successfully'
                        ], 200);
                    }
                }
            }
            throw new Exception('Friend not exist');

        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }

    }

    public function viewfriend(Request $request)
    {
        try {
            $user=User::where('email','=',$request->data['email'])->first();
            $user=$user->friends()->get();
            dd($user->toArray());
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }

    }
}
