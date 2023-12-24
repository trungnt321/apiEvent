<?php

namespace App\Http\Controllers;

use App\Models\atendance;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\User;
class atendanceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/atendances/join/{id_event}/{id_user}",
     *     summary="Lấy tất cả các bản ghi theo id sự kiện",
     *     tags={"Attendances"},
     *      description="
     *      - id_event là id sự kiện
     *      - id_user là id người thực hiện lấy
     *      - Endpoint trả về thôn tìn người dùng tham gia sự kiện.
     *      - Trả về thông tin của người dùng đã tham gia sự kiện.
     *      - Role được sử dụng là role nhân viên ,quản lí ",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Lấy thành công nhân viên tham gia sự kiện"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="event_id", type="integer", example=2),
     *                    @OA\Property(property="user", type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *     @OA\Property(property="phone", type="string", example="123456789"),
     *     @OA\Property(property="role", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     * ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     *                 )
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
    public function index($id_event,$id_user)
    {
        try {
            $user = User::find($id_user);
            if($user == null || $user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người dùng không hợp lệ",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $atendance = atendance::where('event_id',$id_event)->with('user')->get();
            return response()->json([
                'metadata' => $atendance,
                'message' => 'Lấy thành công tất cả các bản ghi',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
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
     *     path="/api/attendances",
     *     tags={"Attendances"},
     *     summary="Thêm người dùng vào sự kiện có sẵn",
     *      description="
     *      - Endpoint trả về danh sách dữ liệu dữ liệu người dùng của sự kiện đó
     *      - Role được sử dụng là role nhân viên ,quản lí ,sinh viên
     *     - event_id là id sự kiện tham gia
     *     - user_id là id của người dùng tham gia sự kiện",
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
     *             @OA\Property(property="message", type="string", example="Create Record Successfully"),
     *             @OA\Property(property="statusCode", type="int", example=200),
     *     @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="event_id", type="integer", example=2),
     *     *                    @OA\Property(property="user", type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *     @OA\Property(property="phone", type="string", example="123456789"),
     *     @OA\Property(property="role", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     * ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Sai validate",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"user_id": {"ID của người dùng không được để trống"}}),
     *             @OA\Property(property="statusCode", type="int", example=500),

     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'required',
                'user_id' => 'required|exists:users,id',
            ], [
                'event_id.required' => 'Id sự kiện không được để trống',
                'user_id.required' => 'Id người dùng không được để trống.',
                'user_id.exists' => 'Id người dùng không tồn tại.'
            ]);

            if ($validator->fails()) {
//                return response(['status' => 'error', 'message' => $validator->errors()], 500);
                return response([
                    "status" => "error",
                    "message" => $validator->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $atendance = atendance::where('user_id',$request->user_id)->where('event_id',$request->event_id)->first();
            if($atendance){
                return response([
                    "status" => "error",
                    "message" => "Người dùng đã tồn tại trong sự kiện này",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
           atendance::create($request->all());
            $usersInEvent = atendance::where('event_id', $request->event_id)
                ->with('user')
                ->get();
            return response()->json([
                'metadata' => $usersInEvent,
                'message' => 'Tạo mới bản ghi thành công',
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

    /**
     * @OA\Post(
     *     path="/api/attendances/add",
     *     tags={"Attendances"},
     *     summary="Thêm sinh viên vào sự kiện bằng email khi sinh viên đã đăng nhập ",
     *     description="
     *          - Endpoint này cho phép quản lí và nhân viên thêm sinh viên vào sự kiện bằng email
     *          - Trả về data của sinh viên sau khi được thêm mới bằng email
     *          - event_id là id sự kiện tham gia
     *          - email là email của người muốn thêm (Lưu ý : phải có trong hệ thống)
     *          - create_by là id của người thêm
     *          ",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example=1, description="Id của sự kiện."),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com", description="Email của sinh viên."),
     *             @OA\Property(property="create_by", type="int", example="1", description="Id người tạo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="event_id", type="integer", example=1),
     *                    @OA\Property(property="user", type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *     @OA\Property(property="phone", type="string", example="123456789"),
     *     @OA\Property(property="role", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     * ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-05 12:34:56"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-05 12:34:56")
     *             ),
     *             @OA\Property(property="message", type="string", example="Tạo mới bản ghi thành công"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"event_id": {"Id sự kiện không để trống."}, "email": {"Email không để trống."}}),
     *             @OA\Property(property="statusCode", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Id sự kiện không tồn tại."),
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

    public function addEmail(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|exists:events,id',
                'email' => 'required|exists:users,email',
                "create_by" => 'required|exists:users,id',
            ], [
                'event_id.required' => 'Id sự kiện không để trống.',
                'event_id.exists' => 'Id sự kiện không tồn tại.',
                'email.required' => 'Email không để trống.',
                'email.exists' => 'Email không tồn tại.',
                'create_by.required' => 'Id người tạo không để trống.',
                'create_by.exists' => 'Id người tạo không tồn tại.'
            ]);

            if ($validator->fails()) {
//                return response(['status' => 'error', 'message' => $validator->errors()], 500);
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
            $id = User::select('id')->where('email',$request->email)->first()->id;

            $atendance = atendance::where('user_id',$id)->where('event_id',$request->event_id)->first();
            if($atendance){
                return response([
                    "status" => "error",
                    "message" => "Người dùng đã tồn tại trong sự kiện này",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            atendance::create([
                "user_id" => (int)$id,
                "event_id" => $request->event_id
            ]);
            $usersInEvent = atendance::where('event_id', $request->event_id)
                ->with('user') // Đảm bảo Eloquent trả về thông tin người dùng liên quan
                ->get();
            return response()->json([
                'metadata' => $usersInEvent,
                'message' => 'Tạo mới bản ghi thành công ',
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

    /**
     * @OA\Get(
     *     path="/api/atendances/{id}",
     *     summary="Lấy ra thông tin người tham gia sự kiện",
     *     tags={"Attendances"},
     *     description="
     *          - Endpoint này lấy ra 1 bản ghi của sự kiện
     *          - id là id của atendances
     *          ",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của attendance",
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
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="event_id", type="integer", example=2),
     *     *                    @OA\Property(property="user", type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *     @OA\Property(property="phone", type="string", example="123456789"),
     *     @OA\Property(property="role", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     * ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
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
            $usersInEvent = atendance::findOrFail($id)
                ->with('user')
                ->get();
            return response()->json([
                'metadata' => $usersInEvent,
                'message' => 'Lấy 1 bản ghi thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response([
                "status" => "error",
                "message" => "Bản ghi không tồn tại",
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/atendances/{atendance}",
     *     summary="Update an attendance record",
     *     tags={"Attendances"},
     *     description="
     *          - Endpoint này cho phép quản lí và nhân viên cập nhật thông tin người tham gia sự kiện
     *          - Trả về data của tất cả người dùng thuộc sự kiện đó
     *          - event_id là id sự kiện tham gia
     *          - email là email của người muốn thêm (Lưu ý : phải có trong hệ thống)
     *          - update_by là id của người cập nhật (Lưu ý : phải có trong hệ thống)
     *          ",
     *     @OA\Parameter(
     *         name="atendance",
     *         in="path",
     *         required=true,
     *         description="Mô hình dữ liệu attendance",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2"),
     *             @OA\Property(property="update_by", type="integer", example="2")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Update One Record Successfully"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="event_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
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

    public function update(Request $request, atendance $atendance)
    {
        try {
            $user = User::find($request->update_by);
            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người tạo không hợp lệ.Vui lòng thử lại!!",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $atendance->update([
                "event_id" => $request->event_id,
                "user_id" => $request->user_id
            ]);

            $usersInEvent = atendance::where('event_id', $request->event_id)
                ->with('user') // Đảm bảo Eloquent trả về thông tin người dùng liên quan
                ->get();
            return response()->json([
                'metadata' => $usersInEvent,
                'message' => 'Cập nhật thành công',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response([
                "status" => "error",
                "message" => "Bản ghi không tồn tại",
                'statusCode' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

    }

    /**
     * @OA\Delete(
     *     path="/api/atendances/{id}/{id_user}",
     *     summary="Xóa 1 người dùng đã tham gia sự kiện",
     *     tags={"Attendances"},
     *     description="
     *          - Endpoint này sẽ xóa 1 sinh viên đang tham gia sự kiện đi
     *          - Role được sử dụng là role Quản lí
     *          - Xóa thành công sẽ trả lại data là các sinh viên còn lại sự kiện đó
     *          - id là id của bản ghi tham gia
     *          - id_user là id của người thực hiện xóa
     *          ",
     *     @OA\Parameter(
     *         name="atendance",
     *         in="path",
     *         required=true,
     *         description="Mô hình dữ liệu attendance",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Xóa bản ghi thành công"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="event_id", type="integer", example=2),
     *                    @OA\Property(property="user", type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *     @OA\Property(property="phone", type="string", example="123456789"),
     *     @OA\Property(property="role", type="integer", example=1),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     * ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-23 11:20:22")
     *                 )
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
    public function destroy($id,$id_user)
    {
        try {
            $atendance = atendance::find($id);
            if (!$atendance) {
                return response()->json([
                    'message' => 'Bản ghi không tồn tại',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
            $idEvent = $atendance->event_id;


            $user = User::find($id_user);
            if($user->role == 0 || $user->role == 1){
                return response([
                    "status" => "error",
                    "message" => "Role người xóa không hợp lệ.Vui lòng thử lại!!",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $atendance->delete();

            $usersInEvent = atendance::where('event_id',$idEvent)
                ->with('user')
                ->get();

            return response()->json([
                'metadata' => $usersInEvent,
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
