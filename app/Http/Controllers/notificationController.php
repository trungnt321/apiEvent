<?php

namespace App\Http\Controllers;

use App\Models\notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailApi;
use App\Http\Resources\notificationResource;
use Illuminate\Support\Facades\Validator;

class notificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notification",
     *     summary="Get all notification records",
     *     tags={"notification"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     * @OA\Property(
     *     property="payload",
     *     type="array",
     *     @OA\Items(
     *         type="object",
     *         @OA\Property(
     *             property="id",
     *             type="string",
     *             example="1"
     *         ),
     *     @OA\Property(
     *             property="content",
     *             type="integer",
     *             example="Bài toán cơ bản"
     *         ),
     *           @OA\Property(
     *             property="time_send",
     *          type="string",
     *          format="date-time",
     *          example="2023-11-23 11:20:22"
     *         ),
     *           @OA\Property(
     *             property="sent_at",
     *          type="string",
     *          format="date-time",
     *          example="2023-11-23 11:21:22"
     *         ),
     *         @OA\Property(
     *             property="receiver_id",
     *             type="integer",
     *             example=1
     *         )
     * ,
     *      @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     *     example="2023-11-23 11:20:22"
     * ),
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time",
     *     example="2023-11-23 11:20:22"
     * )
     *     )
     * )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $notification = notification::all();
            return response([
                "status" => "success",
                "payload" => notificationResource::collection($notification)
            ], 200);
        }catch(\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 200);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notification/send",
     *     tags={"notification"},
     *     summary="Send Email",
     *     description="Send Email to Users",
     *     operationId="notification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Thư nhắc nhở sự kiện"),
     *             @OA\Property(property="message", type="string", example="<h1 style='color:red;'>This is a Heading</h1>"),
     *             @OA\Property(property="email", type="string", example="example@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email sent successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example="not found")
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = [
                'title' => $request->title,
                'message' => $request->message,
            ];
            Mail::to($request->email)->send(new EmailApi($data));
            return response([
                "status" => "success",
                "message" => 'Email sent successfully'
            ], 200);
        }catch (\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/notification/{id}",
     *     tags={"notification"},
     *     summary="Show a notification",
     *     description="Show details of a specific notification by ID.",
     *     operationId="showNotification",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the notification",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="title", type="string", example="Thư nhắc nhở sự kiện"),
     *                 @OA\Property(property="message", type="string", example="<h1 style='color:red;'>This is a Heading</h1>"),
     *                 @OA\Property(property="email", type="string", example="example@example.com"),
     *                 @OA\Property(property="created_at", type="string", example="2023-01-01 12:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-01-01 12:30:00"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thông báo"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi nội bộ của máy chủ"),
     *         )
     *     ),
     * )
     */
    public function show($id)
    {
        try {
            $notification = notification::findOrFail($id);

            return response([
                "status" => "success",
                "data" => $notification,
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notification",
     *     tags={"notification"},
     *     summary="Create Time send Email",
     *     description="Send Email to Users",
     *     operationId="notificationCreate",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Nội dung content"),
     *             @OA\Property(property="time_send", type="date", example="2023-11-24 15:38:55"),
     *             @OA\Property(property="receiver_id", type="int", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email sent successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example="not found")
     *         )
     *     ),
     * )
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required',
                'time_send' => 'required',
                'receiver_id' => 'required|exists:users,id',
            ], [
                'content.required' => 'Không để trống nội dung' ,
                'receiver_id.required' => 'Không để trống ID người dùng',
                'receiver_id.exists' => 'User không tồn tại.',
                'event_id.exists' => 'Sự kiện không tồn tại',
            ]);

            if($validator->fails()){
                return response(['status' => 'error', 'message' => $validator->errors()], 500);
            }

            notification::create($request->all());
            return response([   "status" => "success",'message' =>'Tạo mới thành công!!'], 200);
        } catch (\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/notification/update/{id}",
     *     tags={"notification"},
     *     summary="Update a notification",
     *     description="Update an existing notification with the provided data.",
     *     operationId="updateNotification",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the notification",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="message", type="string", example="Updated Message"),
     *             @OA\Property(property="email", type="string", example="updated@example.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Notification updated successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thông báo"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"title": {"Không để trống tiêu đề"}}),
     *         )
     *     ),
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $notification = notification::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'message' => 'required',
                'email' => 'required|email',
            ], [
                'title.required' => 'Không để trống tiêu đề',
                'message.required' => 'Không để trống nội dung',
                'email.required' => 'Không để trống địa chỉ email',
                'email.email' => 'Địa chỉ email không hợp lệ',
            ]);

            if ($validator->fails()) {
                return response(['status' => 'error', 'message' => $validator->errors()], 500);
            }

            $notification->update($request->all());

            return response(["status" => "success", 'message' => 'Notification updated successfully'], 200);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/notification/delete/{id}",
     *     tags={"notification"},
     *     summary="Delete a notification",
     *     description="Delete a notification by ID.",
     *     operationId="deleteNotification",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the notification",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy thông báo"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi nội bộ của máy chủ"),
     *         )
     *     ),
     * )
     */
    public function destroy($id)
    {
        try {
            $notification = notification::findOrFail($id);
            $notification->delete();

            return response(["status" => "success", 'message' => 'Notification deleted successfully'], 200);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

}
