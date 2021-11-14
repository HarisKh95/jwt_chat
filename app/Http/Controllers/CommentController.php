<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class CommentController extends Controller
{

    public function commentcreate(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $post=$user->posts();
        $post=$post->where('id',$request->id)->first();
        $comment=new Comment();
        $comment->comment=$request->comment;
        $comment->user()->associate($user);
        $comment->post()->associate($post);
        $comment=$comment->save();
        return response()->json([
            'message' => 'Comment Created',
            'Post' => $post,
            'Status'=>$comment
        ], 201);
    }

    public function commentupdate(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        // dd($user->id);
        $comment=Comment::where([
            ['id',$request->id],
            ['user_id',$user->id],
            ['post_id',$request->p_id]
            ])->first();

        if(!isset($comment))
        {
            return response()->json([
                'message' => 'Comment Not exist',
                'Status'=>'failed'
            ], 201);
        }
        else
        {
            if($comment->user_id==$user->id)
            {
                $comment->comment=$request->comment;
                $comment->save();
                return response()->json([
                    'message' => 'Comment Updated',
                    'Status'=>'Success'
                ], 201);
            }
            else
            {
                return response()->json([
                    'message' => 'Not your comment',
                    'Status'=>'failed'
                ], 201);
            }
        }
    }

    public function commentpost(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $comment=Comment::where([
            ['user_id',$user->id],
            ['post_id',$request->p_id]
            ])->get();
        dd($comment->toArray());
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
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $comment=Comment::where([
            ['id',$request->id],
            ['user_id',$user->id],
            ['post_id',$request->p_id]
            ])->first();

        if(!isset($comment))
        {
            return response()->json([
                'message' => 'Comment Not exist',
                'Status'=>'failed'
            ], 201);
        }
        else
        {
            if($comment->user_id==$user->id)
            {
                $comment->delete();
                return response()->json([
                    'message' => 'Comment Deleted',
                    'Status'=>'Success'
                ], 201);
            }
            else
            {
                return response()->json([
                    'message' => 'Not your comment',
                    'Status'=>'failed'
                ], 201);
            }
        }
    }
}
