<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Http\Controllers\jwtController;
use App\Service\jwtService;
use App\Models\User;
use App\Models\Post;
use MongoDB\Client as Mongo;
use App\Notifications\CommentNotification;
use App\Models\Comment;
use App\Http\Requests\CommentStoreRequest;
use Exception;
use Illuminate\Support\Facades\Notification;

class CommentController extends Controller
{
    protected $data;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    public function __construct(Request $request)
    {
        // $this->data = (new jwtController)->gettokendecode($request->bearerToken());
        $this->data = (new jwtService)->gettokendecode($request->bearerToken());
    }

    public function commentcreate(CommentStoreRequest $request)
    {
        try {
        // $commentData=[];
        // $user=User::where('email',$this->data['email'])->first();
        // $commentData['name']=$user->name;
        // $userpost=User::where('email',$request->email)->first();
        // $post=User::where('email',$request->email);
        // $post=$post->first();
        // $post=$post->posts();
        // $post=$post->find(array('_id' =>$request->_id))->first();
        // $post=$post->where('_id',"ObjectId(".$request->_id.")")->first();

        $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
        $post=(new Mongo)->jtchat->posts->findOne(
            ['_id'=>new \MongoDB\BSON\ObjectId($request->id)]
            );
            if(!isset($post))
            {
                throw new Exception('Post Not exist');
            }
        $commentData['post_id']=$post['_id'];
        $commentData['post_name']=$post['name'];
        $commentData['comment']=$request['comment'];
        $userpost=(new Mongo)->jtchat->users->findOne(['_id'=>new \MongoDB\BSON\ObjectId($post['user_id'])]);
        $commentData['user_name']=$user['name'];
        $post=(new Mongo)->jtchat->posts->updateOne(
            ['_id'=>new \MongoDB\BSON\ObjectId($request->id)],
            ['$push'=>['comment'=>
                ['_id'=>new \MongoDB\BSON\ObjectId(),
                 'user_id'=>$user['_id'],
                'comment'=>$request->comment
                ]
                ]]

            );
        // if(!isset($post))
        // {
        //     return response()->json([
        //         'message' => 'Post Not exist'
        //     ], 201);
        // }
        // $commentData['post_id']=$post->_id;
        // $commentData['post_name']=$post->name;
        // $comment=new Comment();
        // $comment->comment=$request->comment;
        // $commentData['comment']=$request->comment;
        // $comment->user()->associate($user);
        // $comment->post()->associate($post);
        // $comment=$comment->save();
        // dd('hit');
        // Notification::send($userpost, new CommentNotification($commentData));
        // Notification::send($userpost, new CommentNotification($commentData));
        \Mail::to($userpost['email'])->send(new \App\Mail\CommentNotification($commentData));
        return response()->success([
            'message' => 'Comment Created'
        ], 200);
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage()
            , 201);
        }
    }

    public function commentupdate(Request $request)
    {
        try {
            $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
            $post=(new Mongo)->jtchat->posts->findOne(
                ['$and'=>[
                    ['_id'=>new \MongoDB\BSON\ObjectId($request->id),
                    'comment._id'=>new \MongoDB\BSON\ObjectId($request->c_id),
                    'comment.user_id'=>new \MongoDB\BSON\ObjectId($user['_id'])]
                    ]]
                );

                // dd($post);
                if(!isset($post))
                {
                    throw new Exception('Post Not exist');
                }

            $comment = (new Mongo)->jtchat->posts->updateOne(
                    ["_id"=>new \MongoDB\BSON\ObjectId($request->id),
                    "comment._id"=>new \MongoDB\BSON\ObjectId($request->c_id)],
                    ['$set'=>['comment.$.comment'=>$request->comment]]
            );

            return response()->success([
                        'message' => 'Comment Updated'
                    ], 201);
            // $user=User::where('email','=',$this->data['email'])->first();
            // dd($user->id);
            // $comment=Comment::where([
            //     ['id',$request->id],
            //     ['user_id',$user->id],
            //     ['post_id',$request->p_id]
            //     ])->first();

            // if(!isset($comment))
            // {
            //     return response()->json([
            //         'message' => 'Comment Not exist',
            //         'Status'=>'failed'
            //     ], 201);
            // }
            // else
            // {
            //     if($comment->user_id==$user->id)
            //     {
            //         $comment->comment=$request->comment;
            //         $comment->save();
            //         return response()->json([
            //             'message' => 'Comment Updated',
            //             'Status'=>'Success'
            //         ], 201);
            //     }
            //     else
            //     {
            //         return response()->json([
            //             'message' => 'Not your comment',
            //             'Status'=>'failed'
            //         ], 201);
            //     }
            // }
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage()
            , 201);
        }

    }

    public function commentpost(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $comment=Comment::where([
            ['user_id',$user->id],
            ['post_id',$request->p_id]
            ])->get();
        // dd($comment->toArray());
        if(!isset($comment))
        {
            return response()->json([
                'message' => 'Comment Not exist',
                'Status'=>'failed'
            ], 201);
        }
        else
        {
                return response()->json([
                    'message' => 'Your comments',
                    'Status'=>'Success',
                    'data'=>$comment
                ], 201);
        }
    }

    public function commentdelete(Request $request)
    {
        try {
            $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
            $post=(new Mongo)->jtchat->posts->findOne(
                ['$and'=>[
                    ['_id'=>new \MongoDB\BSON\ObjectId($request->id),
                    'comment._id'=>new \MongoDB\BSON\ObjectId($request->c_id),
                    'comment.user_id'=>new \MongoDB\BSON\ObjectId($user['_id'])]
                    ]]
                );

                // dd($post);
                if(!isset($post))
                {
                    throw new Exception('Post Not exist');
                }

            $comment = (new Mongo)->jtchat->posts->updateOne(
                    ["_id"=>new \MongoDB\BSON\ObjectId($request->id),
                    "comment._id"=>new \MongoDB\BSON\ObjectId($request->c_id)],
                    ['$pull'=>['comment'=>
                    ['_id'=>new \MongoDB\BSON\ObjectId($request->c_id)]]
                    ]
            );

            return response()->success([
                        'message' => 'Comment Deleted'
                    ], 201);
            // $user=User::where('email','=',$this->data['email'])->first();
            // $comment=Comment::where([
            //     ['id',$request->id],
            //     ['user_id',$user->id],
            //     ['post_id',$request->p_id]
            //     ])->first();

            // if(!isset($comment))
            // {
            //     return response()->json([
            //         'message' => 'Comment Not exist',
            //         'Status'=>'failed'
            //     ], 201);
            // }
            // else
            // {
            //     if($comment->user_id==$user->id)
            //     {
            //         $comment->delete();
            //         return response()->json([
            //             'message' => 'Comment Deleted',
            //             'Status'=>'Success'
            //         ], 201);
            //     }
            //     else
            //     {
            //         return response()->json([
            //             'message' => 'Not your comment',
            //             'Status'=>'failed'
            //         ], 201);
            //     }
            // }
        } catch (Exception $e) {
            return response()->error(
                $e->getMessage()
            , 201);
        }

    }
}
