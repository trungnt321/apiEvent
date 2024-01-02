<?php

namespace App\Http\Controllers;

use App\Models\notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailApi;
use Illuminate\Support\Facades\Validator;
use App\Models\event;
class notificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notification/{id}",
     *     summary="Get all set notifications",
     *     tags={"notification"},
     *     description="
     *      - This endpoint retrieves information about all pre-set notifications.
     *      - Returns information about users who have set notifications.
     *      - Roles: Both Administrator and Student
     *      - id is the ID of the user making the request.
     *      - Sẽ có 1 số option param sau
     *     - page=<số trang> chuyển sang trang cần
     *     - limit=<số record> số record muốn lấy trong 1 trang
     *     - pagination=true|false sẽ là trạng thái phân trang hoặc không phân trang <mặc định là false phân trang>
     *     ",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully retrieved all records"),
     *         @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Nhắc nhở email"),
     *                     @OA\Property(property="content", type="string", example="<h1 style='color:red;'>Test message</h1>"),
     *                     @OA\Property(property="time_send", type="string", format="date-time", example="2023-11-25 01:42:27"),
     *                     @OA\Property(property="sent_at", type="string", format="date-time", example="2023-11-25 01:50:33"),
     *                     @OA\Property(property="receiver_id", type="integer", example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 11:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 11:30:00"),
     *                     @OA\Property(property="user_receiver", type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *     @OA\Property(property="phone", type="string", example="123456789"),
     *     @OA\Property(property="role", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     * )
     *                 )
     *             ),
     *                 @OA\Property(property="totalDocs", type="integer", example=16),
     *                 @OA\Property(property="limit", type="integer", example=10),
     *                 @OA\Property(property="totalPages", type="integer", example=2),
     *                 @OA\Property(property="page", type="integer", example=2),
     *                 @OA\Property(property="pagingCounter", type="integer", example=2),
     *                 @OA\Property(property="hasPrevPage", type="boolean", example=true),
     *                 @OA\Property(property="hasNextPage", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Record not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Record not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="System error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="System error"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function index($id,Request $request)
    {
        try {
            $user =  User::find($id);
            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người Get không hợp lệ.Vui lòng thử lại!!",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $status = $request->query('pagination', false);
            $query = notification::with('user_receiver');
            $notification = ($status) ? $query->get() : $query->paginate($limit, ['*'], 'page', $page);
            if ($page > $notification->lastPage()) {
                $page = 1;
                $notification = notification::with('user_receiver')->paginate($limit, ['*'], 'page', $page);
            }
            return response()->json(handleData($status,$notification), Response::HTTP_OK);
        }catch(\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }

//    public function test(){
//        $currentDateTime = Carbon::now();
//        $fiveHoursAgo = $currentDateTime->subHours(5)->toDateTimeString();
//        $events = event::where('start_time', '>', $fiveHoursAgo)
//            ->with(['attendances.user', 'user','user.receivedNotifications'])
//            ->whereDate('start_time', '=', $currentDateTime->toDateString())
//            ->where('status', 1)
//            ->get();
//        foreach ($events as $item) {
//
//            dd($item->user->receivedNotifications->last()->content);
//            foreach($item->attendances as $userSend){
//                $data = [
//                    'title' => "EMAIL NHẮC NHỞ SỰ KIỆN " . $item->name,
//                    'message' =>$item->user->receivedNotifications->last()->content,
//                ];
////                $userSend->user->email
//                dd($userSend->user);
//            }
//
//        }


//    }

    /**
     * @OA\Post(
     *     path="/api/notification",
     *     tags={"notification"},
     *     summary="Tạo mới 1 thông báo để chuẩn bị gửi",
     *     description="
     *      - Endpoint này cho phép người tạo mới thông báo cho sinh viên.
     *      - Trả về thông tin các thông báo đã tồn tại
     *      - Role được sử dụng là cả hai role nhân viên ,quản lí
     *      - title là tiêu đề của email muốn gửi
     *      - content là nội dung muốn gửi
     *      - time_send là thời gian gửi
     *      - create_by là id người thực hiện tạo",
     *     operationId="storeNotification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Notification content"),
     *              @OA\Property(property="title", type="string", example="title content"),
     *             @OA\Property(property="time_send", type="string", format="date-time", example="2023-11-28T17:02:29"),
     *             @OA\Property(property="receiver_id", type="integer", example=1),
     *             @OA\Property(property="create_by", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *            @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Nhắc nhở email"),
     *                     @OA\Property(property="content", type="string", example="<h1 style='color:red;'>Test message</h1>"),
     *                     @OA\Property(property="time_send", type="string", format="date-time", example="2023-11-25 01:42:27"),
     *                     @OA\Property(property="sent_at", type="string", format="date-time", example="2023-11-25 01:50:33"),
     *                     @OA\Property(property="receiver_id", type="integer", example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 11:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 11:30:00"),
     *                     @OA\Property(property="user_receiver", type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *     @OA\Property(property="phone", type="string", example="123456789"),
     *     @OA\Property(property="role", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     * )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Cài đặt gửi email thành công"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={
     *                 "title": {"Tiêu đề không được để trống"},
     *                 "content": {"Nội đung không được để trống"},
     *                 "create_by": {"ID người tạo không được để trống"},
     *                 "time_send": {"Thời gian không được để trống"},
     *                 "receiver_id": {"Người nhận không được để trống"}
     *             }),
     *             @OA\Property(property="statusCode", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Người dùng không tồn tại"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi máy chủ nội bộ"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'content' => 'required',
                'time_send' => 'required',
                'receiver_id' => 'required|exists:users,id',
                "create_by" =>  'required|exists:users,id',
            ], [
                'title.required' => 'Tiêu để không được để trống',
                'content.required' => 'Nội dung không được để trống',
                'receiver_id.required' => 'ID của người dùng không được để trống',
                'receiver_id.exists' => 'ID của người dùng không tồn tại',
                'event_id.exists' => 'Sự kiện không tồn tại',
                'create_by.required' => 'ID của người tạo không được để trống',
                'create_by.exists' => 'ID của người tạo không tồn tại'
            ]);

            if($validator->fails()){
                return response([
                    "status" => "error",
                    "message" => $validator->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $user =  User::find($request->create_by);

            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người tạo không hợp lệ.Vui lòng thử lại!!",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            notification::create($request->all());
            $notification = notification::with('user_receiver')->get();
            return response()->json([
                'metadata' => $notification,
                'message' => 'Tạo thông báo thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/notification/send",
     *     tags={"notification"},
     *     summary="Send an email",
     *     description="
     *      - Gửi email với dữ liệu được cung cấp
     *      - Trả lại message thông báo gửi thành công
     *      - Role được sử dụng là Nhân viên và Quản lí",
     *     operationId="sendEmail",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Email Title"),
     *             @OA\Property(property="message", type="string", example="Email Content"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="create_by", type="int", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object", example={"title": "Email Title", "message": "Email Content"}),
     *             @OA\Property(property="message", type="string", example="Gửi Email example@gmail.com thành công"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="integer", example=1)
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
    public function create(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required|exists:events,id',
                'message' => 'required|exists:users,email',
                'email' => 'required|email',
                "create_by" => 'required|exists:users,id',
            ], [
                'title.required' => 'Id sự kiện không để trống.',
                'message.required' => 'Email không để trống.',
                'create_by.required' => 'Id người tạo không để trống.',
                'create_by.exists' => 'Id người tạo không tồn tại.',
                'email.email' => 'Email không hợp lệ',
                'email.required' => 'Không để trống email',
            ]);


            if ($validator->fails()) {
                return response([
                    "status" => "error",
                    "message" => $validator->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $data = [
                'title' => $request->title,
                'message' => $request->message,
            ];
            Mail::to($request->email)->send(new EmailApi($data));
            $user =  User::find($request->create_by);

            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người tạo không hợp lệ.Vui lòng thử lại!!",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return response()->json([
                'metadata' => $data,
                'message' => 'Gửi '.$request->email.' thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }catch (\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    /**
     * @OA\Get(
     *     path="/api/notifications/show/{id}",
     *     tags={"notification"},
     *     summary="Lấy ra 1 ghi trong notification",
     *     description="
     *      -Lấy dữ liệu thông báo theo ID
     *      - Data trả về là thông tin của thông báo và thông tin người được gửi
     *      - Role thực hiện là Quản lí và nhân viên",
     *     operationId="getNotificationById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của thông báo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Nhắc nhở email"),
     *                 @OA\Property(property="content", type="string", example="<h1 style='color:red;'>Test message</h1>"),
     *                 @OA\Property(property="time_send", type="string", format="date-time", example="2023-11-25 01:42:27"),
     *                 @OA\Property(property="sent_at", type="string", format="date-time", example="2023-11-25 01:50:33"),
     *                 @OA\Property(property="receiver_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 11:00:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 11:30:00"),
     *                 @OA\Property(property="user_receiver", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                     @OA\Property(property="phone", type="string", example="123456789"),
     *                     @OA\Property(property="role", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Get One Record Successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bản ghi không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
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

    public function show($id)
    {
        try {
            $notification = notification::find($id)->with('user_receiver');
            return response()->json([
                'metadata' => $notification,
                'message' => 'Lấy 1 bản ghi thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response([
                "status" => "error",
                "message" => "Record not exists",
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }



    /**
     * @OA\Put(
     *     path="/api/notifications/{id}",
     *     tags={"notification"},
     *     summary="Cập nhật thông tin thông báo",
     *     description="
     *     - Cập nhật thông báo theo ID
     *     - Data trả về là dữ liệu của các thông báo và dữ liệu của user đã được cài đặt
     *     - id ở đây là id của thông báo
     *     - Role được cập nhật là Quản lí và nhân viên
     *     - id_user là id người thực hiện request update này",
     *     operationId="updateNotificationById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID thông báo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="message", type="string", example="Updated Content"),
     *             @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object", example={
     *                "id": 1,
    "title": "Title Cập nhật",
    "content": "<h1 style='color:red;'>Test message</h1>",
    "time_send": "2023-11-25 01:42:27",
    "sent_at": "2023-11-25 01:50:33",
    "receiver_id": 1,
    "created_at": null,
    "updated_at": "2023-11-24T18:50:33.000000Z"
     *             }),
     *             @OA\Property(property="message", type="string", example="Update One Record Successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Record not exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Record not exists"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
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
                'id_user' => 'required|exists:users,id',
            ], [
                'title.required' => 'Tiêu để không được để trống',
                'message.required' => 'Nội dung không được để trống',
                'email.required' => 'Email không được để trống',
                'email.email' => 'Email không đúng định dạng',
                'id_user.required' => 'Id người thực hiện không được để trống',
                'id_user.email' => 'id người thực hiện không tồn tại trong hệ thống',
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => "error",
                    "message" => $validator->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $user =  User::find($request->id_user);

            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người thực hiện không hợp lệ.Vui lòng thử lại!!",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $notification->update($request->all());
            $notification = notification::with('user_receiver')->get();
            return response()->json([
                'metadata' => $notification,
                'message' => 'Cập nhật thông báo thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/notifications/{id}/{id_user}",
     *     tags={"notification"},
     *     summary="Xóa thông báo",
     *     description="Xóa 1 thông báo đang tham gia sự kiện",
     *     operationId="deleteNotificationById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của thông báo",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         description="ID của người thực hiện xóa",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Xóa thành công thông báo"),
     *             @OA\Property(property="metadata", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Nhắc nhở email"),
     *                     @OA\Property(property="content", type="string", example="<h1 style='color:red;'>Test message</h1>"),
     *                     @OA\Property(property="time_send", type="string", format="date-time", example="2023-11-25 01:42:27"),
     *                     @OA\Property(property="sent_at", type="string", format="date-time", example="2023-11-25 01:50:33"),
     *                     @OA\Property(property="receiver_id", type="integer", example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 11:00:00"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 11:30:00"),
     *                     @OA\Property(property="user_receiver", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                         @OA\Property(property="phone", type="string", example="123456789"),
     *                         @OA\Property(property="role", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bản ghi không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
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

    public function destroy($id,$id_user)
    {
        try {
            $notification = notification::find($id);
            if (!$notification) {
                return response()->json([
                    'message' => 'Bản ghi không tồn tại',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
            $user = User::find($id_user);
            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người xóa không hợp lệ.Vui lòng thử lại!!",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $notification->delete();
            $notification = notification::with('user_receiver')->get();
            return response()->json([
                'metadata' => $notification,
                'message' => 'Xóa bản ghi thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
