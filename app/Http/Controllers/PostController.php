<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\jwtController;
use App\Models\User;
use App\Models\Post;
use App\Http\Requests\PostStoreRequest;
use Exception;
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

    public function postcreate(PostStoreRequest $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $post = new Post;
            if($request->has('photo'))
            {
                $data = $request->photo;

                //get the base-64 from data
                $base64_str = substr($data, strpos($data, ",")+1);

                //decode base64 string
                $image = base64_decode($base64_str);
                $imageName = Str::random(10) . '.jpg';
                Storage::disk('local')->put($imageName, $image);
                // $storagePath = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
                $post->file='storage/path/public/'.$imageName;
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
        } catch (Exception $e) {
            return response()->error($e->getMessage(),406);
        }
    }

    public function showpost_public(Request $request)
    {
        try {
            $post=Post::where("visibile",'!=',0)->get();

            return response()->success([
                'message' => 'Post Public',
                'user' => $post->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }
    }

    public function showpost_user(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $posts=$user->posts();
            $posts=$posts->with('comments')->get();
            $friend=$user->friends()->get();
            return response()->success([
                'message' => 'User Posts',
                'user' => $posts,
                'Friends'=>$friend
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }


    }

    public function showsinglepost_user(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $posts=$user->posts();
            $posts=$posts->where('name',$request->name)->with('comments')->get();
            $friend=$user->friends()->get();
            return response()->success([
                'message' => 'User Posts',
                'user' => $posts,
                'Friends'=>$friend
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }


    }

    public function showpost_private_user(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $posts=$user->posts()->where("visibile",'!=',1)->get();
            return response()->success([
                'message' => 'User Private Posts',
                'user' => $posts
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }
    }

    public function update_post(Request $req)
    {
        try {
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
            return response()->success([
                'message' => 'User Post Updated',
                'status' => $post
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }


    }

    public function remove_post(Request $req)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $post=$user->posts()->where("id",'=',$req->id)->delete();
            return response()->success([
                'message' => 'User selected Post deleted',
                'status' => $post
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }
    }
}
