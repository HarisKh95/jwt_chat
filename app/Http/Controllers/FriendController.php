<?php

namespace App\Http\Controllers;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use App\Models\Friend;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

class FriendController extends Controller
{

    public function addfriend(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $userf=$user->friends()->get();
        // $user=$user->where('id',$request->id);
        $friends=$userf->toArray();
        if(!empty($friends))
        {
            foreach($friends as $friend)
            {

                if($friend['pivot']['friend_id']==$request->id)
                {
                    return response()->json([
                        'message' => 'Friend Aready exist'
                    ], 201);
                }
            }
        }
        $user=$user->friends()->attach([$request->id]);
        return response()->json([
            'message' => 'Friend Added'
        ], 201);
    }

    public function removefriend(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $userf=$user->friends()->get();
        $friends=$userf->toArray();
        if(!empty($friends))
        {
            foreach($friends as $friend)
            {

                if($friend['pivot']['friend_id']==$request->id)
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
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $user=$user->friends()->get();
        dd($user->toArray());
    }


}
