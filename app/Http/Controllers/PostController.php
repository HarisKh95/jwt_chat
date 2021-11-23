<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\jwtController;
use App\Service\jwtService;
use App\Models\User;
use App\Models\Post;
use App\Http\Requests\PostStoreRequest;
use Exception;
use MongoDB\Client as Mongo;
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
        $this->data = (new jwtService)->gettokendecode($request->bearerToken());
        // $this->data = (new jwtController)->gettokendecode($request->bearerToken());
    }

    public function postcreate(PostStoreRequest $request)
    {
        try {
        // $user=User::where('email','=',$this->data['email'])->first();
        $user=(new Mongo)->jtchat->users->findOne(["email"=>$this->data['email']]);
        // dd($user['_id']);
        // $post = new Post;
        $post=array('user_id'=>(string)$user['_id']);
        // dd($post);
        if($request->has('photo'))
        {
            $data = $request->photo;

            //decode base64 string
            $image = base64_decode($data);
            $imageName = Str::random(10) . '.jpg';
            Storage::disk('local')->put($imageName, $image);
            // $post->file='storage/path/public/'.$imageName;
            $post=array_merge($post,array('file'=>'storage/path/public/'.$imageName));
        }
        else{
            $post=array_merge($post,array('file'=>Null));
        }
        if($request->has('name') && $request->has('body'))
        {
            // $post->name = $request->name;
            // $post->body = $request->body;
            $post=array_merge($post,array('name'=>$request->name,'body'=>$request->body));
        }
        if($request->has('visible'))
        {
            // $post->visibile = $request->visible;
            $post=array_merge($post,array('visible'=>$request->visible));
        }
        $post=array_merge($post,array('comment'=>[]));
        // $post = $user->posts()->save($post);
        $user=(new Mongo)->jtchat->posts->insertOne($post);
        return response()->success([
            'message' => 'Post created'
        ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }

    }

    public function showpost_public(Request $request)
    {
        try {
            $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
            $posts=(new Mongo)->jtchat->posts->find(
                ['$and'=>[
                // ['_id'=>new \MongoDB\BSON\ObjectId($req->id)],
                ['user_id'=>(string)$user['_id']],
                ['visible'=>'1']
                ]]
                )->toArray();
            return response()->success([
                'message' => 'User Public Posts',
                'user' => $posts
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }

    public function showpost_user(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $posts=$user->posts();
            $posts=$posts->with('comments')->get();
            $friend=$user->friends()->get();
            return response()->json([
                'message' => 'User Posts',
                'user' => $posts,
                'Friends'=>$friend
            ], 201);

        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }

    }

    public function showsinglepost_user(Request $request)
    {
        try {
            $user=User::where('email','=',$this->data['email'])->first();
            $posts=$user->posts();
            $posts=$posts->where('name',$request->name)->with('comments')->get();
            $friend=$user->friends()->get();
            return response()->json([
                'message' => 'User Posts',
                'user' => $posts,
                'Friends'=>$friend
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }


    }

    public function showpost_private_user(Request $request)
    {
        try {
        // $user=User::where('email','=',$this->data['email'])->first();
        // $posts=$user->posts()->where("visibile",'!=',1)->get();
        $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
        $posts=(new Mongo)->jtchat->posts->find(
            ['$and'=>[
            // ['_id'=>new \MongoDB\BSON\ObjectId($req->id)],
            ['user_id'=>(string)$user['_id']],
            ['visible'=>'0']
            ]]
            )->toArray();

        return response()->json([
            'message' => 'User Private Posts',
            'user' => $posts
        ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }


    }

    public function update_post(Request $req)
    {
        try {
        // $user=User::where('email','=',$this->data['email'])->first();
        $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
        $post=(new Mongo)->jtchat->posts->find(
            ['$and'=>[
            ['_id'=>new \MongoDB\BSON\ObjectId($req->id)],
            ['user_id'=>(string)$user['_id']
            ]]]
            )->toArray();
            // dd($req->all());
            if(!isset($post))
            {
                throw new Exception('Post not found');
            }

        // $post=new Post();
        $post=array();
        if($req->has('file'))
        {
            // $post->file = $req->file;
            $post =array_merge($post,array('file'=>$req->file));
        }
        if($req->has('name') && $req->has('body'))
        {
            // $post->name = $req->name;
            // $post->body = $req->body;
            $post =array_merge($post,array('name'=>$req->name,'body'=>$req->body));
        }
        if($req->has('visible'))
        {
            // $post->visibile = $req->visible;
            $post =array_merge($post,array('visible'=>$req->visible));
        }
        // $post=$user->posts()->where('id',$req->id)->update($post->toArray());
                    // dd($post);
        $post=(new Mongo)->jtchat->posts->updateOne(
            ['$and'=>[
            ['_id'=>new \MongoDB\BSON\ObjectId($req->id)],
            ['user_id'=>(string)$user['_id']
            ]]],
            [ '$set' => $post]
            );
        return response()->success(
            [
            'message' => 'User Post Updated'
            ], 201);

        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }

    }

    public function remove_post(Request $req)
    {
        try {
        // $user=User::where('email','=',$this->data['email'])->first();
        // $post=$user->posts()->where("id",'=',$req->id)->delete();
        $user=(new Mongo)->jtchat->users->findOne(['email'=>$this->data['email']]);
        $post=(new Mongo)->jtchat->posts->find(
            ['$and'=>[
            ['_id'=>new \MongoDB\BSON\ObjectId($req->id)],
            ['user_id'=>(string)$user['_id']
            ]]]
            )->toArray();
            // dd($req->all());
            if(!isset($post))
            {
                throw new Exception('Post not found');
            }
            $post=(new Mongo)->jtchat->posts->findOneAndDelete(
                ['$and'=>[
                ['_id'=>new \MongoDB\BSON\ObjectId($req->id)],
                ['user_id'=>(string)$user['_id']
                ]]]
                );
        return response()->success([
            'message' => 'User selected Post deleted'
        ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }

    public function list(Request $request)
    {
        try {
        // $user=(new jwtService)->gettokendecode($request->bearerToken());
        $users=(new Mongo)->jtchat;
        $alluser=$users->users->find(["email"=>[ '$ne'=> $this->data['email'] ]])->toArray();
        // $alluser=User::query()->where('email','!=',$this->data['email'])->get();
        // dd($alluser);
        if(empty($alluser))
        {
            throw new Exception('Currently no user exists');
        }
        // $index=0;
        // foreach($alluser as $user)
        // {
        //     $data[$index]['name']=$user->name;
        //     $data[$index]['email']=$user->email;
        //     $index++;
        // }
        return response()->success([
            'message' => 'All users list',
            'user' => $alluser
        ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(), 401);
        }
    }

}
