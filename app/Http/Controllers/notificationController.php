<?php

namespace App\Http\Controllers;

use App\Models\notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailApi;
use App\Http\Resources\notificationResource;
use Illuminate\Support\Facades\Validator;
use App\Models\event;
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
     *             @OA\Property(property="message", type="string", example="Get All Records Successfully"),
     *             @OA\Property(
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
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 11:30:00")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Record not exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Record not exists"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $notification = notification::all();

            return response()->json([
                'metadata' => $notification,
                'message' => 'Get All records Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }catch(\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function test(){
        $currentDateTime = Carbon::now();
        $fiveHoursAgo = $currentDateTime->subHours(5)->toDateTimeString();
        $events = event::where('start_time', '>', $fiveHoursAgo)
            ->with(['attendances.user', 'user','user.receivedNotifications'])
            ->whereDate('start_time', '=', $currentDateTime->toDateString())
            ->where('status', 1)
            ->get();
        foreach ($events as $item) {

            dd($item->user->receivedNotifications->last()->content);
            foreach($item->attendances as $userSend){
                $data = [
                    'title' => "EMAIL NHẮC NHỞ SỰ KIỆN " . $item->name,
                    'message' =>$item->user->receivedNotifications->last()->content,
                ];
//                $userSend->user->email
                dd($userSend->user);
            }

        }


    }

    /**
     * @OA\Post(
     *     path="/api/notification",
     *     tags={"notification"},
     *     summary="Store a new notification",
     *     description="Store a new notification with the provided data.",
     *     operationId="storeNotification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Notification content"),
     *             @OA\Property(property="time_send", type="string", format="date-time", example="2023-11-28T17:02:29"),
     *             @OA\Property(property="receiver_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object", example={
    "id": 1,
    "content": "<h1 style='color:red;'>Test message</h1>",
    "time_send": "2023-11-25 01:42:27",
    "sent_at": "2023-11-25 01:50:33",
    "receiver_id": 1,
    "created_at": "2023-11-24T18:50:33.000000",
    "updated_at": "2023-11-24T18:50:33.000000Z"
    }),
     *             @OA\Property(property="message", type="string", example="Create One Record Successfully"),
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
     *                 "content": {"Content cannot be empty"},
     *                 "time_send": {"Time send cannot be empty"},
     *                 "receiver_id": {"User ID cannot be empty"}
     *             }),
     *             @OA\Property(property="statusCode", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User does not exist"),
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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'content' => 'required',
                'time_send' => 'required',
                'receiver_id' => 'required|exists:users,id',
            ], [
                'title.required' => 'Title cannot be empty',
                'content.required' => 'Content cannot be empty',
                'receiver_id.required' => 'User ID cannot be empty',
                'receiver_id.exists' => 'User does not exist',
                'event_id.exists' => 'Event does not exist',
            ]);

            if($validator->fails()){
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $notification = notification::create($request->all());
            return response()->json([
                'metadata' => $notification,
                'message' => 'Create One Record Successfully',
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
     *     description="Send an email with the provided data.",
     *     operationId="sendEmail",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Email Title"),
     *             @OA\Property(property="message", type="string", example="Email Content"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object", example={"title": "Email Title", "message": "Email Content"}),
     *             @OA\Property(property="message", type="string", example="Email sent successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
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
    public function create(Request $request)
    {

        try {
            $data = [
                'title' => $request->title,
                'message' => $request->message,
            ];
            Mail::to($request->email)->send(new EmailApi($data));
            return response()->json([
                'metadata' => $data,
                'message' => 'Send to '.$request->email.' successfully',
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
     *     path="/api/notifications/{id}",
     *     tags={"notification"},
     *     summary="Get a notification by ID",
     *     description="Get a notification by its ID.",
     *     operationId="getNotificationById",
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
     *             @OA\Property(property="metadata", type="object", example={
     *                 "id": 1,
     *                 "content": "Nội dung 1",
     *                 "content": "<h1 style='color:red;'>Test message</h1>",
     *                 "time_send": "2023-11-28T17:02:29.000000Z",
     *                 "receiver_id": 1,
     *                 "created_at": "2023-11-28T17:02:29.000000Z",
     *                 "updated_at": "2023-11-28T17:02:29.000000Z"
     *             }),
     *             @OA\Property(property="message", type="string", example="Get One Record Successfully"),
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
    public function show($id)
    {
        try {
            $notification = notification::findOrFail($id);
            return response()->json([
                'metadata' => $notification,
                'message' => 'Get One Record Successfully',
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
     *     summary="Update a notification by ID",
     *     description="Update a notification by its ID with the provided data.",
     *     operationId="updateNotificationById",
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
     *             @OA\Property(property="message", type="string", example="Updated Content"),
     *             @OA\Property(property="email", type="string", format="email", example="updated@example.com"),
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
            ], [
                'title.required' => 'Title cannot be empty',
                'message.required' => 'Content cannot be empty',
                'email.required' => 'Email address cannot be empty',
                'email.email' => 'Invalid email address',
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $notification->update($request->all());

            return response()->json([
                'metadata' => $notification,
                'message' => 'Update One Record Successfully',
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
     *     path="/api/notifications/{id}",
     *     tags={"notification"},
     *     summary="Delete a notification by ID",
     *     description="Delete a notification by its ID.",
     *     operationId="deleteNotificationById",
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
     *             @OA\Property(property="message", type="string", example="Delete One Record Successfully"),
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
    public function destroy($id)
    {
        try {
            $notification = notification::findOrFail($id);
            if (!$notification) {
                return response()->json([
                    'message' => 'Record not exists',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
            $notification->delete();

            return response()->json([
                'message' => 'Delete One Record Successfully',
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
