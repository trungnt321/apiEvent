<?php

namespace App\Http\Controllers;

use App\Models\atendance;
use App\Http\Resources\AtendanceResources;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class atendanceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/atendances",
     *     summary="Get all attendance records",
     *     tags={"Attendances"},
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
            $atendance = atendance::all();
            return response([
                "status" => "success",
                "payload" => AtendanceResources::collection($atendance)
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
     *     path="/api/attendances",
     *     tags={"Attendances"},
     *     summary="Store a new attendance record",
     *     description="Store a new attendance record with the provided data.",
     *     operationId="storeAttendance",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2"),
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
                'event_id' => [
                    'required'
                ],
                'user_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(function ($query) {
                        $query->whereIn('role', [1, 2]);
                    })
//                    Rule::unique('atendance')->where(function ($query) use ($request) {
//                        return $query->where('event_id', $request->event_id)
//                            ->where('user_id', $request->user_id);
//                    }),
                ],
            ], [
                'event_id.required' => 'Không để trống ID sự kiện',
                'user_id.required' => 'Không để trống ID người dùng',
                'user_id.exists' => 'Chức vụ không hợp lệ.'
            ]);

            if($validator->fails()){
                return response(['status' => 'error', 'message' => $validator->errors()], 500);
            }

            atendance::create($request->all());
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
     *      path="/api/attendances/{id}",
     *      operationId="getAttendanceById",
     *      tags={"Attendances"},
     *      summary="Get attendance by ID",
     *      description="Get a specific attendance by its ID.",
     *      @OA\Parameter(
     *          name="id",
     *          description="Attendance ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *@OA\Property(
     *     property="payload",
     *     type="object",
     *     @OA\Property(
     *         property="id",
     *         type="string",
     *         example="1"
     *     ),
     *     @OA\Property(
     *         property="user_id",
     *         type="integer",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="event_id",
     *         type="integer",
     *         example=2
     *     ),
     *     @OA\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time",
     *         example="2023-11-23 11:20:22"
     *     ),
     *     @OA\Property(
     *         property="updated_at",
     *         type="string",
     *         format="date-time",
     *         example="2023-11-23 11:20:22"
     *     ),
     * )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attendance not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
     *          ),
     *      ),
     * )
     */
    public function show($id)
    {
        try {
            $attendance = Atendance::findOrFail($id);
            return response([
                "status" => "success",
                "payload" => new AtendanceResources($attendance),
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response([
                "status" => "error",
                "message" => "Bản ghi không tồn tại",
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/attendances/{id}",
     *      operationId="updateAttendance",
     *      tags={"Attendances"},
     *      summary="Update attendance",
     *      description="Update a specific attendance.",
     *      @OA\Parameter(
     *          name="atendance",
     *          description="Attendance model",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *             @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2"),
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *          ),
     *      ),
     * )
     */
    public function update(Request $request, atendance $atendance)
    {
        $atendance->update($request->all());
        return response([
            "status" => "success",
            "payload" => new AtendanceResources($atendance)
        ], 200);
    }

    /**
     * @OA\Delete(
     *      path="/api/attendances/{id}",
     *      operationId="deleteAttendance",
     *      tags={"Attendances"},
     *      summary="Delete attendance",
     *      description="Delete a specific attendance.",
     *      @OA\Parameter(
     *          name="atendance",
     *          description="Attendance model",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Bản ghi đã được xóa thành công"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attendance not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="error"),
     *              @OA\Property(property="message", type="string", example="Internal Server Error"),
     *          ),
     *      ),
     * )
     */
    public function destroy(atendance $atendance)
    {
        try {
            if (!$atendance) {
                return response([
                    "status" => "error",
                    "message" => "Bản ghi không tồn tại",
                ], Response::HTTP_NOT_FOUND);
            }

            $atendance->delete();

            return response([
                "status" => "success",
                "message" => "Bản ghi đã được xóa thành công",
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
