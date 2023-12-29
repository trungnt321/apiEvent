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

/**
 * @OA\Post(
 *     path="/api/chat",
 *     tags={"Chat"},
 *     summary="Gửi tin nhắn với thời gian thực",
 *     description="Endpoint để gửi tin nhắn và nhận các bản ghi tin nhắn mà người dùng vừa gửi.",
 *     operationId="Gửi tin nhắn sự kiện",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="content", type="string", example="Xin chào các bạn!"),
 *             @OA\Property(property="sender_id", type="integer", example=1),
 *             @OA\Property(property="event_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Dữ liệu trả về khi thành công",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Gửi tin nhắn thành công"),
 *             @OA\Property(property="statusCode", type="integer", example=200),
 *             @OA\Property(property="metadata", type="object",
 *                     @OA\Property(property="content", type="string", example="Xin chào các bạn!"),
 *                     @OA\Property(property="event_id", type="integer", example=1),
 *                     @OA\Property(property="sender_id", type="integer", example=1),
 *                     @OA\Property(property="sender_info", type="object",
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="name", type="string", example="Người gửi"),
 *                         @OA\Property(property="email", type="string", example="example@example.com"),
 *                         @OA\Property(property="phone", type="string", example="123456789"),
 *                         @OA\Property(property="role", type="string", example="user"),
 *                         @OA\Property(property="google_id", type="string", example="137518716745268"),
 *                         @OA\Property(property="avatar", type="string", example="https://example.com/avatar.jpg"),
 *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28T17:02:29Z"),
 *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28T17:02:29Z")
 *                     
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Lỗi validate",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Lỗi validate"),
 *             @OA\Property(property="statusCode", type="integer", example=422)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi hệ thống",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Lỗi hệ thống"),
 *             @OA\Property(property="statusCode", type="integer", example=500)
 *         )
 *     )
 * )
 */
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
                "message" => $validator->errors()->all(),
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
