<?php

namespace App\Http\Controllers;

use App\Models\feedback;
use Illuminate\Http\Request;
use App\Http\Resources\feedbackResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class feedbackController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/feedback",
     *     summary="Get all feedback records",
     *     tags={"feedback"},
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
     *           @OA\Property(
     *             property="content",
     *             type="integer",
     *             example="Bài toán cơ bản"
     *         ),
     *         @OA\Property(
     *             property="user_id",
     *             type="integer",
     *             example=1
     *         )
     * ,
     *         @OA\Property(
     *             property="event_id",
     *             type="integer",
     *             example=2
     *         )  ,
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
            $feedback = feedback::all();
            return response([
                "status" => "success",
                "payload" => feedbackResource::collection($feedback)
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
     *     path="/api/feedback",
     *     tags={"feedback"},
     *     summary="Store a new feedback record",
     *     description="Store a new feedback record with the provided data.",
     *     operationId="storefeedback",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2"),
     *             @OA\Property(property="content", type="string", example="Bài toán 2"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tạo mới thành công!!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"user_id": {"Không để trống ID người dùng"}}),
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required',
                'event_id' => 'required|exists:events,id',
                'user_id' => 'required|exists:users,id',
            ], [
                'content.required' => 'Không để trống nội dung' ,
                'event_id.required' => 'Không để trống ID sự kiện',
                'user_id.required' => 'Không để trống ID người dùng',
                'user_id.exists' => 'User không tồn tại.',
                'event_id.exists' => 'Sự kiện không tồn tại',
            ]);

            if($validator->fails()){
                return response(['status' => 'error', 'message' => $validator->errors()], 500);
            }

            feedback::create($request->all());
            return response([   "status" => "success",'message' =>'Tạo mới thành công!!'], 200);
        } catch (\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/feedback/{id}",
     *     tags={"feedback"},
     *     summary="Show a feedback record",
     *     description="Show details of a specific feedback record by ID.",
     *     operationId="showFeedback",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the feedback record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="event_id", type="integer", example="1"),
     *                 @OA\Property(property="user_id", type="integer", example="2"),
     *                 @OA\Property(property="content", type="string", example="Bài toán 2"),
     *                 @OA\Property(property="created_at", type="string", example="2023-01-01 12:00:00"),
     *                 @OA\Property(property="updated_at", type="string", example="2023-01-01 12:30:00"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Feedback record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy feedback record"),
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
            $feedback = feedback::findOrFail($id);

            return response([
                "status" => "success",
                "data" => $feedback,
            ], 200);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/feedback/{id}",
     *     tags={"feedback"},
     *     summary="Update a feedback record",
     *     description="Update an existing feedback record with the provided data.",
     *     operationId="updateFeedback",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the feedback record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2"),
     *             @OA\Property(property="content", type="string", example="Updated content"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cập nhật thành công!!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Feedback record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy feedback record"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"user_id": {"Không để trống ID người dùng"}}),
     *         )
     *     ),
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $feedback = feedback::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'content' => 'required',
                'event_id' => 'required|exists:events,id',
                'user_id' => 'required|exists:users,id',
            ], [
                'content.required' => 'Không để trống nội dung',
                'event_id.required' => 'Không để trống ID sự kiện',
                'user_id.required' => 'Không để trống ID người dùng',
                'user_id.exists' => 'User không tồn tại.',
                'event_id.exists' => 'Sự kiện không tồn tại',
            ]);

            if ($validator->fails()) {
                return response(['status' => 'error', 'message' => $validator->errors()], 500);
            }

            $feedback->update($request->all());

            return response(["status" => "success", 'message' => 'Cập nhật thành công!!'], 200);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/feedback/{id}",
     *     tags={"feedback"},
     *     summary="Delete a feedback record",
     *     description="Delete a feedback record by ID.",
     *     operationId="deleteFeedback",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the feedback record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Xóa thành công!!"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Feedback record not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy feedback record"),
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
            $feedback = Feedback::findOrFail($id);
            $feedback->delete();

            return response(["status" => "success", 'message' => 'Xóa thành công!!'], 200);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
