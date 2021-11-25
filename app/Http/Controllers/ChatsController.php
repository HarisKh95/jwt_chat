<?php

namespace App\Http\Controllers;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;
class ChatsController extends Controller
{

    // protected $data;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    // public function __construct(Request $request)
    // {
    //     $this->data = (new jwtController)->gettokendecode($request->bearerToken());
    // }

    public function fetchMessages()
    {
        try {
            return Message::with('user')->get();
        } catch (Exception $e) {
            return response()->error($e->getMessage(),406);
        }

    }
    /**
     * Persist message to database
     *
     * @param  Request $request
     * @return Response
     */
    public function sendMessage(Request $request)
    {
        try {
            $user=User::where('email',$request->data['email'])->first();
            $message = $user->messages()->create([
                'message' => $request->message,
                'reciever_id'=>$request->id
            ])->save();

            return response()->success([
                'message' => 'Message Sent'
            ], 200);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),406);
        }

    }
}
