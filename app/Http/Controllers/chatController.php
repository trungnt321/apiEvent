<?php

namespace App\Http\Controllers;

use App\Models\chat;
use Illuminate\Http\Request;
use App\Events\chatRealTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class chatController extends Controller
{
    public function chatRealTimeAction(Request $request){
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
        $userChat = chat::with('BelongUser')->latest();
        broadcast(new chatRealTime($request))->toOthers();

        return response()->json([
            'metadata' => $userChat,
            'message' => 'Lấy thành công tất cả các bản ghi',
            'status' => 'success',
            'statusCode' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }
    
}
