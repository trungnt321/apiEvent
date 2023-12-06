<?php

namespace App\Http\Controllers;

use App\Models\event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\EventResources;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class eventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/event",
     *     summary="Get all events records",
     *     tags={"Event"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(property="message", type="string", example="Lấy dữ liệu thành công"),
     *              @OA\Property(property="statusCode", type="integer", example=200),
     *              @OA\Property(property="metadata",type="array",
     *                  @OA\Items(
     *                      type="object",
     *                       @OA\Property(property="name", type="string", example="Event Name"),
     *                       @OA\Property(property="location", type="string", example="Ha Noi"),
     *                       @OA\Property(property="contact", type="string", example="0986567467"),
     *                       @OA\Property(property="user_id", type="integer", example=2),
     *                       @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                       @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                  )
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy bản ghi",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Không tìm thấy bản ghi"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *      @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $event = event::all();
            return response()->json([
                'metadata' => $event,
                'message' => 'Get All Records Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'statusCode' => $e instanceof HttpException
                    ? $e->getStatusCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR
            ],  $e instanceof HttpException
                ? $e->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/searchEvent",
     *     summary="Tìm kiếm sự kiện theo tên ",
     *     tags={"Event"},
     *     operationId="Tìm kiếm sự kiện",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Event Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="EVent information retrieved successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                       @OA\Property(property="name", type="string", example="Event Name"),
     *                       @OA\Property(property="location", type="string", example="Ha Noi"),
     *                       @OA\Property(property="contact", type="string", example="0986567467"),
     *                       @OA\Property(property="user_id", type="integer", example=2),
     *                       @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                       @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function searchEvent(Request $request)
    {
        try {
            if ($request->name == "" || $request->name == null) {
                $request->name == "";
            }
            $events = DB::table('events')->where('name', 'like', "%{$request->name}%")->get();
            return response()->json([
                'metadata' => $events,
                'message' => 'Lấy các bản ghi thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
                'statusCode' => $e instanceof HttpException
                    ? $e->getStatusCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR
            ],  $e instanceof HttpException
                ? $e->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/event",
     *     tags={"Event"},
     *     summary="Store a new event record",
     *     description="Thêm mới bản ghi với dữ liệu được cung cấp",
     *     operationId="storeEvent",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Event Name"),
     *             @OA\Property(property="location", type="string", example="Hai Phong"),
     *             @OA\Property(property="contact", type="string", example="0983467584"),
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *             @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thêm mới dữ liệu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tạo mới bản ghi thành công"),
     *             @OA\Property(property="statusCode", type="int", example=200),
     *             @OA\Property(property="metadata", type="array",
     *                  @OA\Items(type="object",
     *                           @OA\Property(property="name", type="string", example="Event Name"),
     *                           @OA\Property(property="location", type="string", example="Ha Noi"),
     *                           @OA\Property(property="contact", type="string", example="0986567467"),
     *                           @OA\Property(property="user_id", type="integer", example=2),
     *                           @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                           @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Sai validate",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Sai validate"),
     *             @OA\Property(property="statusCode", type="int", example=422),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống"),
     *             @OA\Property(property="statusCode", type="int", example=500),
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        //Check valiadate
        $validate = Validator::make($request->all(), [
            'name' => ['required'],
            'location' => ['required'],
            'contact' => [
                'required',
                'regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/'
            ],
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', [1, 2]);
                })
            ],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time']
        ], [
            'name.required' => 'Không để trống name của của sự kiện nhập',
            'location.required' => 'Không được để trống địa điểm của sự kiện',
            'contact.required' => 'Không được để trống phần liên lạc',
            'contact.regex' => 'Định dạng số điện thoại được nhập không đúng',
            'user_id.required' => 'User Id không được để trống',
            'start_time.required' => 'Ngày khởi đầu của event không được để trống',
            'end_time.required' => 'Ngày kết thúc của event không được để trống',
            'end_time.after' => 'Ngày kết thúc của dự án phải lớn hơn ngày bắt đầu'
        ]);
        if ($validate->fails()) {
            return response([
                "status" => "error",
                "message" => $validate->errors(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $logUserRole = auth()->user()->role;
        if ($logUserRole == 1 || $logUserRole == 2) {
            //Only staff and admin can make event
            try {
                $event = event::create($request->all());
                return response()->json([
                    'metadata' => $event,
                    'message' => 'Create Record Successfully',
                    'status' => 'success',
                    'statusCode' => Response::HTTP_OK
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
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
        return response([
            "status" => "error",
            "message" => "Only Employees and managers are allowed to add new records",
            "statusCode" => Response::HTTP_CONFLICT
        ], Response::HTTP_CONFLICT);
    }

    /**
     * @OA\Get(
     *      path="/api/event/{id}",
     *      operationId="getEventsById",
     *      tags={"Event"},
     *      summary="Get events by ID",
     *      description="Lấy dữ liệu sự kiện từ một id cho trước",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID sự kiện ",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Get One Record Successfully"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Event Name"),
     *                           @OA\Property(property="location", type="string", example="Ha Noi"),
     *                           @OA\Property(property="contact", type="string", example="0986567467"),
     *                           @OA\Property(property="user_id", type="integer", example=2),
     *                           @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                           @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bản ghi không tồn tại",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
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
            $event = event::findOrFail($id);
            return response()->json([
                'metadata' => $event,
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

    public function eventStatistics(Request $request)
    {
        //Nếu không có request thì là mặc định tuần hiện tại
        if ($request == null) {
            $today = Carbon::now();

            // Lấy thông tin về tuần và năm
            $weekNumber = $today->weekOfYear;
            $year = $today->year;
            return $weekNumber;
        }
    }

    /**
     * /**
     * @OA\Put(
     *     path="/api/event/{id}",
     *     operationId="updateEvent",
     *     tags={"Event"},
     *     summary="Update Event",
     *     description="Sửa dữ liệu của một sự kiện ",
     *     @OA\Parameter(
     *         name="id",
     *         description="ID của một sự kiện",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Event Name"),
     *             @OA\Property(property="location", type="string", example="Hai Phong"),
     *             @OA\Property(property="contact", type="string", example="0983118678"),
     *             @OA\Property(property="status", type="integer", example=0),
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sửa dữ liệu thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Update One Record Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="location", type="string", example="Hai Phong"),
     *                 @OA\Property(property="contact", type="string", example="0983118678"),
     *                 @OA\Property(property="status", type="integer", example=0),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="start_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                 @OA\Property(property="end_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bản ghi không tồn tại ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại "),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        //Check validate
        $validate = Validator::make($request->all(), [
            'name' => ['required'],
            'location' => ['required'],
            'contact' => [
                'required',
                'regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/'
            ],
            'status' => [
                'required',
                Rule::in([0, 1])
            ],
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', [1, 2]);
                })
            ],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time']
        ], [
            'name.required' => 'Không để trống name của của sự kiện nhập',
            'location.required' => 'Không được để trống địa điểm của sự kiện',
            'contact.required' => 'Không được để trống phần liên lạc',
            'contact.regex' => 'Định dạng số điện thoại được nhập không đúng',
            'status.required' => 'Trạng thái của sự kiện không được để trống',
            'user_id.required' => 'User Id không được để trống',
            'start_time.required' => 'Ngày khởi đầu của event không được để trống',
            'end_time.required' => 'Ngày kết thúc của event không được để trống',
            'end_time.after' => 'Ngày kết thúc của dự án phải lớn hơn ngày bắt đầu'
        ]);
        if ($validate->fails()) {
            return response([
                "status" => "error",
                "message" => $validate->errors(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $logUserRole = auth()->user()->role;
        if ($logUserRole == 1 || $logUserRole == 2) {
            //Check role
            $event = event::findOrFail($id);
            try {
                $event->update($request->all());
                return response()->json([
                    'metadata' => $event,
                    'message' => 'Update One Record Successfully',
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
        return response([
            "status" => "error",
            "message" => "Only Employees and managers are allowed to edit records",
            "statusCode" => Response::HTTP_CONFLICT
        ], Response::HTTP_CONFLICT);
    }

    /**
     * @OA\Delete(
     *     path="/api/event/{id}",
     *     summary="Xóa một bản ghi",
     *     tags={"Event"},
     *     @OA\Parameter(
     *         name="events",
     *         in="path",
     *         required=true,
     *         description="events record model",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Xóa một bản ghi thành công"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bản ghi không tồn tại",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi hệ thống",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi hệ thống"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $event = event::findOrFail($id);
            if (!$event) {
                return response()->json([
                    'message' => 'Không tồn tại bản ghi',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
            $event->delete();
            return response()->json([
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
