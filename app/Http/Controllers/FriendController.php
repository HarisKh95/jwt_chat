<?php

namespace App\Http\Controllers;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use App\Models\Friend;
use App\Service\jwtService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

class FriendController extends Controller
{
    protected $data;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    public function __construct(Request $request)
    {
        $this->data = (new jwtService)->gettokendecode($request->bearerToken());
    }

    public function addfriend(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $userf=$user->friends()->get();
        $friends=$userf->toArray();
        if(!empty($friends))
        {
            foreach($friends as $friend)
            {
                dd($friend);
                if($friend['_id']==$request->id)
                {
                    return response()->json([
                        'message' => 'Friend Aready exist'
                    ], 201);
                }
                else{
                    return response()->json([
                        'message' => 'No such user exist'
                    ], 201);
                }
            }
        }
        // dd($user);
        $user=$user->friends()->attach([$request->id]);
        return response()->json([
            'message' => 'Friend Added'
        ], 201);
    }

    public function removefriend(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $userf=$user->friends()->get();
        $friends=$userf->toArray();
        if(!empty($friends))
        {
            foreach($friends as $friend)
            {

                if($friend['_id']==$request->id)
                {
                    $user=$user->friends()->detach([$request->id]);
                    return response()->json([
                        'message' => 'Friend remove successfully'
                    ], 201);
                }
            }
        }

        return response()->json([
            'message' => 'Friend not exist'
        ], 201);
    }

    public function viewfriend(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $user=$user->friends()->get();
        dd($user->toArray());
    }


}
