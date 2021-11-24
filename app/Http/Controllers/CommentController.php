<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use App\Notifications\CommentNotification;
use App\Models\Comment;
use Exception;
use App\Http\Requests\CommentStoreRequest;
use Illuminate\Support\Facades\Notification;

class CommentController extends Controller
{
    protected $data;
    /**
     * Creates a new authenticatable user from Firebase.
     */
    public function __construct(Request $request)
    {
        $this->data = (new jwtController)->gettokendecode($request->bearerToken());
    }

    public function commentcreate(CommentStoreRequest $request)
    {
        try {
            $commentData=[];
            $user=User::where('email',$this->data['email'])->first();
            $commentData['name']=$user->name;
            $post=User::where('email',$request->email);
            $post=$post->first();
            $post=$post->posts();
            $post=$post->where('id',$request->id)->first();
            $commentData['post_id']=$post->id;
            $commentData['post_name']=$post->name;
            $comment=new Comment();
            $comment->comment=$request->comment;
            $commentData['comment']=$request->comment;
            $comment->user()->associate($user);
            $comment->post()->associate($post);
            $comment=$comment->save();
            Notification::send($user, new CommentNotification($commentData));
            return response()->success([
                'message' => 'Comment Created',
                'Post' => $post,
                'Status'=>$comment
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),406);
        }
    }

    public function commentupdate(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            // dd($user->id);
            $comment=Comment::where([
                ['id',$request->id],
                ['user_id',$user->id],
                ['post_id',$request->p_id]
                ])->first();

            if(!isset($comment))
            {
                throw new Exception('Comment Not exist');
            }
            else
            {
                if($comment->user_id==$user->id)
                {
                    $comment->comment=$request->comment;
                    $comment->save();
                    return response()->success([
                        'message' => 'Comment Updated',
                        'Status'=>'Success'
                    ], 201);
                }
                else
                {
                    throw new Exception('Not your comment');
                }
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(),406);
        }
    }

    public function commentpost(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $comment=Comment::where([
                ['user_id',$user->id],
                ['post_id',$request->p_id]
                ])->first();
            if(!isset($comment))
            {
                throw new Exception('Comment Not exist');
            }
            else
            {
                    return response()->success([
                        'message' => 'Your comments',
                        'Status'=>'Success',
                        'data'=>$comment
                    ], 200);
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(),406);
        }
    }

    public function commentdelete(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $comment=Comment::where([
                ['id',$request->id],
                ['user_id',$user->id],
                ['post_id',$request->p_id]
                ])->first();

            if(!isset($comment))
            {
                throw new Exception('Comment Not exist');
            }
            else
            {
                if($comment->user_id==$user->id)
                {
                    $comment->delete();
                    return response()->success([
                        'message' => 'Comment Deleted',
                        'Status'=>'Success'
                    ], 200);
                }
                else
                {
                    throw new Exception('Not your comment');
                }
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }

    }
}
