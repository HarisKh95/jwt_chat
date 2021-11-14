<?php

namespace App\Http\Controllers;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatsController extends Controller
{

    protected $data;

    /**
     * Creates a new authenticatable user from Firebase.
     */
    public function __construct(Request $request)
    {
        $this->data = (new jwtController)->gettokendecode($request->bearerToken());
    }

    public function fetchMessages()
    {
      return Message::with('user')->get();
    }
    /**
     * Persist message to database
     *
     * @param  Request $request
     * @return Response
     */
    public function sendMessage(Request $request)
    {
    $user=User::where('email',$this->data['email'])->first();
    $message = $user->messages()->create([
        'message' => $request->message,
        'reciever_id'=>$request->id
    ])->save();

    return response()->json([
        'message' => 'Message Sent'
    ], 201);
    }
}
