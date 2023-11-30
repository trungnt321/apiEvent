<?php

namespace App\Http\Controllers;

use App\Models\feedback;
use Illuminate\Http\Request;
use App\Http\Resources\feedbackResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     *             @OA\Property(property="message", type="string", example="Get All records Successfully"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="event_id", type="integer", example="2"),
     *                     @OA\Property(property="user_id", type="integer", example="3"),
     *                     @OA\Property(property="content", type="string", example="Feedback content"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="statusCode", type="int", example=500)
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $feedback = feedback::all();
            return response()->json([
                'metadata' => $feedback,
                'message' => 'Get All records Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }catch(\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'statusCode' => $e instanceof HttpException
                    ? $e->getStatusCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR // Internal Server Error by default
            ], $e instanceof HttpException
                ? $e->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/feedback",
     *     tags={"feedback"},
     *     summary="Store a new feedback record",
     *     description="Store a new feedback record with the provided data.",
     *     operationId="storeFeedback",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2"),
     *             @OA\Property(property="content", type="string", example="Feedback content"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Create Record Successfully"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="event_id", type="integer", example="2"),
     *                 @OA\Property(property="user_id", type="integer", example="3"),
     *                 @OA\Property(property="content", type="string", example="Feedback content"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"user_id": {"User does not exist"}}),
     *             @OA\Property(property="statusCode", type="int", example=500)
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
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
                'content.required' => 'Content cannot be empty',
                'event_id.required' => 'Event ID cannot be empty',
                'user_id.required' => 'User ID cannot be empty',
                'user_id.exists' => 'User does not exist',
                'event_id.exists' => 'Event does not exist',
            ]);

            if($validator->fails()){
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $feedback = feedback::create($request->all());
            return response()->json([
                'metadata' => $feedback,
                'message' => 'Create Record Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'statusCode' => $e instanceof HttpException
                    ? $e->getStatusCode()
                    : 500 // Internal Server Error by default
            ], $e instanceof HttpException
                ? $e->getStatusCode()
                : 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/feedback/{id}",
     *     summary="Get a feedback record by ID",
     *     tags={"feedback"},
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
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Get One Record Successfully"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="event_id", type="integer", example="2"),
     *                 @OA\Property(property="user_id", type="integer", example="3"),
     *                 @OA\Property(property="content", type="string", example="Feedback content"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Record not exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Record not exists"),
     *             @OA\Property(property="statusCode", type="int", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="statusCode", type="int", example=500)
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $feedback = feedback::findOrFail($id);

            return response()->json([
                'metadata' => $feedback,
                'message' => 'Get One Record Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => "Record not exists",
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/feedback/{id}",
     *     tags={"feedback"},
     *     summary="Update a feedback record by ID",
     *     description="Update an existing feedback record with the provided data.",
     *     operationId="updateFeedback",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the feedback record to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2"),
     *             @OA\Property(property="content", type="string", example="Updated content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Update One Record Successfully"),
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="id", type="integer", example="1"),
     *                 @OA\Property(property="user_id", type="integer", example="2"),
     *                 @OA\Property(property="event_id", type="integer", example="1"),
     *                 @OA\Property(property="content", type="string", example="Updated content"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:25:22")
     *             )
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
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
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
                'content.required' => 'Content cannot be empty',
                'event_id.required' => 'Event ID cannot be empty',
                'user_id.required' => 'User ID cannot be empty',
                'user_id.exists' => 'User does not exist',
                'event_id.exists' => 'Event does not exist',
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $feedback->update($request->all());

            return response()->json([
                'metadata' => $feedback,
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
     *     path="/api/feedback/{id}",
     *     tags={"feedback"},
     *     summary="Delete a feedback record by ID",
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
     *             @OA\Property(property="message", type="string", example="Delete One Record Successfully"),
     *             @OA\Property(property="statusCode", type="int", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Record not exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Record not exists"),
     *             @OA\Property(property="statusCode", type="int", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="statusCode", type="int", example=500)
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $feedback = Feedback::findOrFail($id);
            if (!$feedback) {
                return response()->json([
                    'message' => 'Record not exists',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
            $feedback->delete();

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
