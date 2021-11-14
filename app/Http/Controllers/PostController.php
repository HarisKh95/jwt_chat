<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class PostController extends Controller
{
    protected $data;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    public function __construct(Request $request)
    {
        $this->data = (new jwtController)->gettokendecode($request->bearerToken());
    }

    public function postcreate(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $post = new Post;
        if($request->has('photo'))
        {
            $data = $request->photo;

            //get the base-64 from data
            $base64_str = substr($data, strpos($data, ",")+1);

            //decode base64 string
            $image = base64_decode($base64_str);
            Storage::disk('local')->put('imgage.png', $image);
            $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
            $post->file=$storagePath.'imgage.png';
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
            'message' => 'Post created'
        ], 201);
    }

    public function showpost_public(Request $request)
    {
        $post=Post::where("visibile",'!=',0)->get();

        return response()->json([
            'message' => 'Post Public',
            'user' => $post->toArray()
        ], 201);

    }

    public function showpost_user(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $posts=$user->posts()->get();

        return response()->json([
            'message' => 'User Posts',
            'user' => $posts
        ], 201);

    }

    public function showpost_private_user(Request $request)
    {
        $user=User::where('email','=',$this->data['email'])->first();
        $posts=$user->posts()->where("visibile",'!=',1)->get();

        return response()->json([
            'message' => 'User Private Posts',
            'user' => $posts
        ], 201);

    }

    public function update_post(Request $req)
    {
        $user=User::where('email','=',$this->data['email'])->first();
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
        $user=User::where('email','=',$this->data['email'])->first();
        $post=$user->posts()->where("id",'=',$req->id)->delete();
        return response()->json([
            'message' => 'User selected Post deleted',
            'status' => $post
        ], 201);

    }
}
