<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\post;
class PostController extends Controller
{

    public function postcreate(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $post = new post;
        if($request->has('file'))
        {
            $post->file = $request->file;
        }
        if($request->has('name') && $request->has('body'))
        {
            $post->name = $request->name;
            $post->body = $request->body;
        }
        if($request->has('visible'))
        {
            $post->visibile = $request->visible;
        }

        $post = $user->posts()->save($post);

        return response()->json([
            'message' => 'Post created',
            'user' => $data
        ], 201);
    }

    public function showpost_public(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $post=Post::where("visibile",'!=',1)->get();

        return response()->json([
            'message' => 'Post Public',
            'user' => $post->toArray()
        ], 201);

    }

    public function showpost_user(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $posts=$user->posts()->get();

        return response()->json([
            'message' => 'User Posts',
            'user' => $posts
        ], 201);

    }

    public function showpost_private_user(Request $request)
    {
        $data=(new jwtController)->gettokendecode($request->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $posts=$user->posts()->where("visibile",'=',1)->get();

        return response()->json([
            'message' => 'User Private Posts',
            'user' => $posts
        ], 201);

    }

    public function update_post(Request $req)
    {
        $data=(new jwtController)->gettokendecode($req->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $post=new Post();
        if($req->has('file'))
        {
            $post->file = $req->file;
        }
        if($req->has('name') && $req->has('body'))
        {
            $post->name = $req->name;
            $post->body = $req->body;
        }
        if($req->has('visible'))
        {
            $post->visibile = $req->visible;
        }
        $post=$user->posts()->where('id',$req->id)->update($post->toArray());
        return response()->json([
            'message' => 'User Post Updated',
            'status' => $post
        ], 201);

    }


    public function remove_post(Request $req)
    {
        $data=(new jwtController)->gettokendecode($req->bearerToken());
        $user=User::where('email','=',$data['email'])->first();
        $post=$user->posts()->where("id",'=',$req->id)->delete();
        return response()->json([
            'message' => 'User selected Post deleted',
            'status' => $post
        ], 201);

    }
}
