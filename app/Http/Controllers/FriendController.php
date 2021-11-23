<?php

namespace App\Http\Controllers;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use App\Models\Friend;
use App\Service\jwtService;
use Illuminate\Http\Request;
use MongoDB\Client as Mongo;
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
        $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
        $friend=(new Mongo)->jtchat->users->findOne(['_id'=>new \MongoDB\BSON\ObjectId($request->id)]);
        if(!isset($friend))
        {
                    return response()->error([
                        'message' => 'Friend not exist'
                    ], 404);
        }
        $useradd=(new Mongo)->jtchat->users->updateOne(
            ['_id'=>new \MongoDB\BSON\ObjectId($user['_id'])],
            ['$push'=>['friends'=>
                ['_id'=>new \MongoDB\BSON\ObjectId($friend['_id'])
                ]
                ]]
            );
        $friendadd=(new Mongo)->jtchat->users->updateOne(
            ['_id'=>new \MongoDB\BSON\ObjectId($friend['_id'])],
            ['$push'=>['friends'=>
                ['_id'=>new \MongoDB\BSON\ObjectId($user['_id'])
                ]
                ]]
            );

        return response()->success([
            'message' => 'Friend Added'
        ], 200);
        // $user=User::where('email','=',$this->data['email'])->first();
        // $userf=$user->friends()->get();
        // $friends=$userf->toArray();
        // if(!empty($friends))
        // {
        //     foreach($friends as $friend)
        //     {
        //         dd($friend);
        //         if($friend['_id']==$request->id)
        //         {
        //             return response()->json([
        //                 'message' => 'Friend Aready exist'
        //             ], 201);
        //         }
        //         else{
        //             return response()->json([
        //                 'message' => 'No such user exist'
        //             ], 201);
        //         }
        //     }
        // }
        // // dd($user);
        // $user=$user->friends()->attach([$request->id]);
        // return response()->json([
        //     'message' => 'Friend Added'
        // ], 201);
    }

    public function removefriend(Request $request)
    {
        $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
        $friend=(new Mongo)->jtchat->users->findOne(['_id'=>new \MongoDB\BSON\ObjectId($request->id)]);

        $userremove = (new Mongo)->jtchat->users->updateOne(
            ["_id"=>new \MongoDB\BSON\ObjectId($user['_id'])],
            ['$pull'=>['friends'=>
            ['_id'=>new \MongoDB\BSON\ObjectId($request->id)]]
            ]
        );

        $friendremove = (new Mongo)->jtchat->users->updateOne(
            ["_id"=>new \MongoDB\BSON\ObjectId($request->id)],
            ['$pull'=>['friends'=>
            ['_id'=>new \MongoDB\BSON\ObjectId($user['_id'])]]
            ]
        );

        return response()->success([
                'message' => 'Friend Deleted'
            ], 201);
        // $user=User::where('email','=',$this->data['email'])->first();
        // $userf=$user->friends()->get();
        // $friends=$userf->toArray();
        // if(!empty($friends))
        // {
        //     foreach($friends as $friend)
        //     {

        //         if($friend['_id']==$request->id)
        //         {
        //             $user=$user->friends()->detach([$request->id]);
        //             return response()->json([
        //                 'message' => 'Friend remove successfully'
        //             ], 201);
        //         }
        //     }
        // }

        // return response()->json([
        //     'message' => 'Friend not exist'
        // ], 201);
    }

    public function viewfriend(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $user=$user->friends()->get();
        dd($user->toArray());
    }


}
