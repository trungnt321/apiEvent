<?php

namespace App\Http\Controllers;

use App\Models\chat;
use Illuminate\Http\Request;
use App\Events\chatRealTime;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Pusher\Pusher;

class chatController extends Controller
{

    public function sendMessage(Request $request)
    {
         //Request ở đây là tin nhắn 
        $validator = Validator::make($request->all(),[
            'content'=>'required',
            'sender_id'=>'required',
            'event_id'=>'required',
        ],[
            'content.required'=>'Nội dung tin nhắn không được để trống',
            'sender_id.required'=>'Id người gửi không được để trống',
            'event_id.required'=>'Id event không được đẻ trống',
        ]);
        if ($validator->fails()) {
            return response([
                "status" => "error",
                "message" => $validator->errors(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        chat::create($request->all());
        //Trả về dữ liệu
        $userChat = chat::with('senderInfo')->orderBy('created_at','desc')->first();

        broadcast(new chatRealTime($userChat))->toOthers();

        return response()->json([
            'metadata' => $userChat,
            'message' => 'Gửi tin nhắn thành công!',
            'status' => 'success',
            'statusCode' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    public function showMessageInEvent($event_id){
        $chat = chat::where('event_id','=',$event_id)->with('senderInfo')->get();
        return $chat;
    }

    public function showTaskbar(){
        
    }
}
