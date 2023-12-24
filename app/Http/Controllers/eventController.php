<?php

namespace App\Http\Controllers;

use App\Models\event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\EventResources;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class eventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/event",
     *     summary="Lấy tất cả các sự kiện",
     *     tags={"Event"},
     *      description="
     *      - Endpoint trả về thông tin của tất cả các sự kiện
     *      - Role được sử dụng là role của tất cả
     *      - Trả về thông tin của tất cả các sự kiện đã diễn ra
     * ",
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
     *                       @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                       @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                       @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                             @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     *                       @OA\Property(property="attendances_count", type="interger", example=3),
     *                       @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
     *                 )
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
            $event = event::withCount('attendances')->with('user')->get();
            $returnData = $event->map(function ($event) {
                $imageUrl = asset("Upload/{$event->banner}");
                $event->banner = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
                return $event;
            });
            return response()->json([
                'metadata' => $returnData,
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
     *     path="/api/event/notification",
     *     tags={"Event"},
     *     summary="lấy ra sự kiện sắp diễn ra  trước 24h ",
     *     description="
     * - Endpoint trả về các bản ghi sự kiện diễn ra trước 24h
     * -Role được sử dụng quản lí, nhân viên
     * -id_user_get là id của người thực hiện lấy thông báo
     * - user trả về là thông tin của người tạo sự kiện
     * ",
     *     operationId="notificationEvent24h",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id_user_get", type="integer", example="1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dữ liệu trả về thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Lấy các sự kiện sắp diễn ra thành công"),
     *             @OA\Property(property="statusCode", type="int", example=200),
     *             @OA\Property(property="metadata", type="array",
     *                  @OA\Items(type="object",
     *                           @OA\Property(property="name", type="string", example="Event Name"),
     *                           @OA\Property(property="location", type="string", example="Ha Noi"),
     *                           @OA\Property(property="contact", type="string", example="0986567467"),
     *                           @OA\Property(property="user_id", type="integer", example=2),
     * @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                           @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                           @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                  @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     *                           @OA\Property(property="user", type="array", @OA\Items(
     *                              type="object",
     *                      @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Mr. Stewart Boehm"),
     *                     @OA\Property(property="email", type="integer", example="upton.tessie@example.com"),
     *                     @OA\Property(property="phone", type="integer", example="2023-12-20T04:57:16.000000Z"),
     *                     @OA\Property(property="role", type="integer", example="205.840.6294"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                          )),
     *                         @OA\Property(property="attendances", type="array", @OA\Items(
     *                              type="object",
     *                     @OA\Property(property="id", type="interger", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="event_id", type="integer", example=2),
     *                          )),
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
    public function indexNotification(Request $request)
    {
        try {

            $validator = Validator::make($request->all(),[
                'id_user_get'=>'required'

            ],[
                'id_user_get.required' => 'id người lấy thông báo không được để trống',
            ]);
            if($validator->fails()){
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $currentDateTime = \Illuminate\Support\Carbon::now();
            $dateCr = $currentDateTime->toDateTimeString();
            $fiveHoursAhead = $currentDateTime->addHours(24)->toDateTimeString();
            $user = User::find($request->id_user_get);
            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người dùng không hợp lệ",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $events = event::where('start_time', '>=', $dateCr)
                ->with(['attendances.user', 'user'])
                ->where('start_time', '<', $fiveHoursAhead)
                ->where('status', 1)
                ->get();

            return response()->json([
                'metadata' => $events,
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
     *  description="
     * - Request cần nhập vào là tên sự kiện
     * - Tên sự kiện chỉ cần nhập gần giống, không nhất thiết phải giống hẳn
     * - Endpoit trả ra là những sự kiện có tên dạng vậy",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Event Name"),
     *                   
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
     * @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                       @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                       @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                   @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     *                       @OA\Property(property="attendances_count", type="interger", example=3),
     * @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
     *                 )
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
            $event = event::where('name', 'like', "%{$request->name}%")->withCount('attendances')->with('user')->get();
            $returnData = $event->map(function ($event) {
                $imageUrl = asset("Upload/{$event->banner}");
                $event->banner = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
                return $event;
            });
            return response()->json([
                'metadata' => $returnData,
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
     *     path="/api/recreateEvent",
     *     tags={"Event"},
     *     summary="Thêm mới bản ghi với dữ liệu được cung cấp",
     *     description="
     * - Endpoint trả về bản ghi mới được thêm vào 
     * -Role đước sử dụng là nhân viên, quản lí
     * -id là id của sự kiện cần được tạo lại
     * -name là tên sự kiện 
     * -location là nơi tổ chức sự kiện 
     * -contact là liên lạc bằng số điện thoại
     * -banner là ảnh của sự kiện
     * -user_id là id của user tổ chức sự kiện này
     * -start_time là thời gian bắt đầu sự kiện
     * -end_time là thời gian kết thúc sự kiện
     * ",
     *     operationId="recreateEvent",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="interger", example=1),
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
     *                           @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                           @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                           @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                  @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     *                           @OA\Property(property="attendances_count", type="interger", example=3),
     * @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
     *                 )
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
    public function recreateEvent(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'id'=>'required',
                'start_time'=>'required',
                'end_time'=>'required|after:start_time',
                'user_id'=>'required'
            ],[
                'id.required'=>'Không được để trống id của sự kiện cần tạo lại',
                'start_time.required'=>'Không được để trống thời gian bắt đầu',
                'end_time.required'=>'Không được để trống thời gian kết thúc',
                'end_time.after'=>'Ngày kết thúc của dự án phải lớn hơn ngày bắt đầu',
                'user_id'=>'Không được để trống id của người tạo sự kiện'
            ]);
            if($validator->fails()) {
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $logUserRole = auth()->user()->role;
            if ($logUserRole == 1 || $logUserRole == 2) {
                //Tạo thêm ảnh mới
                $event = Event::findOrFail($request->id);
                $imageName = time().'.'.pathinfo($event->banner, PATHINFO_EXTENSION);
                $imageUrl = asset("Upload/{$imageName}");
                $sourcePath = public_path('Upload\\') . $event->banner; // Đường dẫn tệp tin hiện tại
                $destinationPath = public_path('Upload\\') . $imageName; // Đường dẫn tới tệp tin mới
                $success = File::copy($sourcePath,$destinationPath);

                $newEventData = $event->toArray();

                $newEventData['status'] = 1;
                $newEventData['user_id'] = $request->user_id;
                $newEventData['start_time'] = $request->start_time;
                $newEventData['end_time'] = $request->end_time;
                $newEventData['banner'] = $imageUrl;
                Event::create($newEventData);

                $eventRecreate = Event::orderBy('id','desc')->with('user')->first();
                return response()->json([
                    'metadata' => $eventRecreate,
                    'message' => 'Create Record Successfully',
                    'status' => 'success',
                    'statusCode' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }
            return response([
                "status" => "error",
                "message" => "Không phải nhân viên hoặc quản lí thì không có quyền tạo lại sự kiện",
                'statusCode' => Response::HTTP_FORBIDDEN
            ], Response::HTTP_FORBIDDEN);

        }catch(\Exception $e){
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
     *     summary="Thêm mới bản ghi với dữ liệu được cung cấp",
     *     description="
     * - Endpoint trả về bản ghi mới được thêm vào
     * -Role đước sử dụng là nhân viên, quản lí
     * -name là tên sự kiện
     * -location là nơi tổ chức sự kiện
     * -contact là liên lạc bằng số điện thoại
     * -banner là ảnh của sự kiện
     * -user_id là id của user tổ chức sự kiện này
     * -start_time là thời gian bắt đầu sự kiện
     * -end_time là thời gian kết thúc sự kiện
     * ",
     *     operationId="storeEvent",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Event Name"),
     *             @OA\Property(property="location", type="string", example="Hai Phong"),
     *             @OA\Property(property="contact", type="string", example="0983467584"),
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="banner", type="string", example="anh1.jpg"),
     *             @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *             @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                   @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
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
     *                           @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                           @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                           @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                   @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     *                           @OA\Property(property="attendances_count", type="interger", example=3),
     * @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
     *                 )
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
            'banner'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'description'=>'required',
            'content'=>'required'
        ], [
            'name.required' => 'Không để trống name của của sự kiện nhập',
            'location.required' => 'Không được để trống địa điểm của sự kiện',
            'contact.required' => 'Không được để trống phần liên lạc',
            'contact.regex' => 'Định dạng số điện thoại được nhập không đúng',
            'user_id.required' => 'User Id không được để trống',
            'banner.required' =>'Ảnh sự kiện bắt buộc phải có',
            'start_time.required' => 'Ngày khởi đầu của event không được để trống',
            'end_time.required' => 'Ngày kết thúc của event không được để trống',
            'end_time.after' => 'Ngày kết thúc của dự án phải lớn hơn ngày bắt đầu',
            'description.required' => 'Không được để trống trường mô tả',
            'content.required'=>'Không được để trống trường nội dung'
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
                $imageName = time().'.'.$request->banner->extension();
                $request->banner->move(public_path('Upload'), $imageName);
                $resourceData = $request->all();
                $resourceData['banner'] = $imageName;
                $event = event::create($resourceData);
                $returnData = event::withCount('attendances')->with('user')->findOrFail($event->id);
                $returnData->banner = asset("Upload/{$returnData->banner}");
                return response()->json([
                    'metadata' => $returnData,
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
            "message" => "Chỉ nhân viên và quán lí mới có thể tạo mới sự kiện",
            "statusCode" => Response::HTTP_CONFLICT
        ], Response::HTTP_CONFLICT);
    }

    /**
     * @OA\Get(
     *      path="/api/event/{id}",
     *      operationId="getEventsById",
     *      tags={"Event"},
     *      summary="Lấy dữ liệu sự kiện từ một id cho trước",
     *      description="
     * -Endpoint này lấy ra 1 bản ghi của sự kiện
     * -id là id của event mà mình cần tìm kiếm",
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
     *                           @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                           @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                           @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                  @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     *                           @OA\Property(property="attendances_count", type="interger", example=3),
     * @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
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
    public function show($id)
    {
        try {
            $event = event::withCount('attendances')->findOrFail($id);
            $event->banner = url("Upload/{$event->banner}");
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
/**
     * @OA\Post(
     *     path="/api/eventStatistics",
     *     tags={"Event"},
     *     summary="Thống kê sự kiện ",
     *     description="
     * - Endpoint trả về các bản ghi sự kiện
     * -Role đước sử dụng quản lí, nhân viên
     * -name là tên sự kiện
     * -location là nơi tổ chức sự kiện
     * -contact là liên lạc bằng số điện thoại
     * -user_id là id của user tổ chức sự kiện này
     * -start_time là thời gian bắt đầu sự kiện
     * -end_time là thời gian kết thúc sự kiện
     * -attendances_count là số sinh viên tham gia
     *  - attendances là thông tin của các sinh viên tham gia
     *  - feedback là thông tin của các feedback của sinh viên
     * - start_time có thể rỗng
     * - end_time có thể rỗng
     * - Nhưng nếu nhập start_time thì bắt buộc phải nhập end_time
     * - Nếu không nhập cả start_time và end_time thì sẽ là thống kê của tuần hiện tại
     * ",
     *     operationId="eventStatistics",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *             @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dữ liệ trả về thành công",
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
     * @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                           @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                           @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                   @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     *                           @OA\Property(property="attendances_count", type="integer", example=0),
     *                           @OA\Property(property="feedback", type="array", @OA\Items(
     *                              type="object",
     *                      @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="content", type="string", example="Phucla DepZai"),
     *                     @OA\Property(property="user_id", type="integer", example="1"),
     *                     @OA\Property(property="event_id", type="integer", example="2"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                          )),
     *                         @OA\Property(property="attendances", type="array", @OA\Items(
     *                              type="object",
     *                     @OA\Property(property="id", type="interger", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="event_id", type="integer", example=2),
     *                          )),
     * @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
     *                 )
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
    public function eventStatistics(Request $request)
    {
        $logUser = auth()->user()->role;
        if($logUser == 0){
            return response([
                "status" => "error",
                "message" => 'Sinh viên không thể xem thống kê',
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        //Nếu không có request thì là mặc định tuần hiện tại
        if ($request->start_time == "" && $request->end_time == "") {
            $today = Carbon::now();

            // Lấy thông tin về tuần và năm
            $weekNumber = $today->weekOfYear;
            $year = $today->year;
            $dayOfWeekNumber = $today->dayOfWeek==0?7:$today->dayOfWeek;

            //Lấy ngày đầu tiên, cuối cùng của tuần đó
            $firstDayOfWeekNumber = $today->copy()->addDays(-$dayOfWeekNumber+1);
            $lastDayOfWeekNumber = $today->copy()->addDays(7-$dayOfWeekNumber);
            $eventInWeek = event::where('end_time','>=',$firstDayOfWeekNumber)
                                                ->where('start_time','<=',$lastDayOfWeekNumber)
                                                ->with('feedback')
                                                ->withCount('attendances')
                                                ->with('attendances')
                                                ->with('user')
                                                ->get();
            $eventInWeek->map(function ($event) {
                $imageUrl = asset("Upload/{$event->banner}");
                $event->banner = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
                return $event;
            });
            return response()->json([
                'metadata' => $eventInWeek,
                'message' => 'Get One Record Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        $validator = Validator::make($request->all(),[
            'start_time'=>'required',
            'end_time'=>'required', 'after:start_time'
        ],[
            'start_time.required'=>'Ngày bắt đầu phải có',
            'end_time.required'=>'Ngày kết thúc phải có',
            'start_time.after'=>'Ngày kết thúc phải lớn hơn ngày bắt đầu'
        ]);
        if ($validator->fails()) {
            return response([
                "status" => "error",
                "message" => $validator->errors(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $eventInStatistic = event::where('end_time','>=',$request->start_time)
        ->where('start_time','<=',$request->end_time)
        ->with('feedback')
        ->withCount('attendances')
        ->with('attendances')
        ->with('user')
        ->get()->map(function ($event) {
            $imageUrl = asset("Upload/{$event->banner}");
            $event->banner = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
            return $event;
        });
        return response()->json([
            'metadata' => $eventInStatistic,
            'message' => 'Get One Record Successfully',
            'status' => 'success',
            'statusCode' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * /**
     * @OA\Put(
     *     path="/api/event/{id}",
     *     operationId="updateEvent",
     *     tags={"Event"},
     *     summary="Sửa dữ liệu của một sự kiện ",
     *     description="
     * -Endpoint trả về dữ liệu bản ghi mới được sửa đổi
     * -Role quy định là role nhân viên, quản lí
     * -name là tên sự kiện
     * -location là nơi tổ chức sự kiện
     * -contact là liên lạc bằng số điện thoại
     * -user_id là id của user tổ chức sự kiện này
     * -start_time là thời gian bắt đầu sự kiện
     * -end_time là thời gian kết thúc sự kiện
     * ",
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
     * @OA\Property(property="banner", type="string", example="anh1.jpg"),
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                                   @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
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
     * @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                 @OA\Property(property="start_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                 @OA\Property(property="end_time", type="string", format="date-time", example="2023-11-23 11:20:22"),
     *                                   @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     * @OA\Property(property="attendances_count", type="integer", example=3),
     * @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
     *                 )
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
            'banner'=>'required',
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'description' => ['required'],
            'content' => ['required'],
        ], [
            'name.required' => 'Không để trống name của của sự kiện nhập',
            'location.required' => 'Không được để trống địa điểm của sự kiện',
            'contact.required' => 'Không được để trống phần liên lạc',
            'contact.regex' => 'Định dạng số điện thoại được nhập không đúng',
            'status.required' => 'Trạng thái của sự kiện không được để trống',
            'user_id.required' => 'User Id không được để trống',
            'start_time.required' => 'Ngày khởi đầu của event không được để trống',
            'end_time.required' => 'Ngày kết thúc của event không được để trống',
            'banner.required' => 'Không được để trống ảnh',
            'end_time.after' => 'Ngày kết thúc của dự án phải lớn hơn ngày bắt đầu',
            'description.required' => 'Không được để trống trường mô tả',
            'content.required'=>'Không được để trống trường nội dung'
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
            $event = event::findOrFail($id)->with('user');
            try {
                //Xóa ảnh
                $imagePath = public_path('Upload/'.$event->banner);
                File::delete($imagePath);

                //Thêm ảnh mới
                $imageName = time().'.'.$request->banner->extension();
                $request->banner->move(public_path('Upload'), $imageName);

                $resourceData = $request->all();
                $resourceData['banner'] = $imageName;
                $event->update($resourceData);
                $event->banner = url("Upload/{$event->banner}");

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
            "message" => "Chỉ nhân viên và quản lí mới có quyền sửa bản ghi",
            "statusCode" => Response::HTTP_CONFLICT
        ], Response::HTTP_CONFLICT);
    }

    /**
     * @OA\Delete(
     *     path="/api/event/{id}",
     *     summary="Xóa một bản ghi",
     *     tags={"Event"},
     * description="
     *          - Endpoint này sẽ xóa 1 sự kiện
     *          - Role được sử dụng là role Quản lí
     *          - Xóa thành công sẽ trả lại data là của các sự kiện còn lại
     *          - id là id của event cần xóa
     *          ",
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
     *             @OA\Property(property="statusCode", type="integer", example=200),
     * @OA\Property(property="metadata",type="array",
     *                  @OA\Items(
     *                      type="object",
     *                       @OA\Property(property="name", type="string", example="Event Name"),
     *                       @OA\Property(property="location", type="string", example="Ha Noi"),
     *                       @OA\Property(property="contact", type="string", example="0986567467"),
     *                       @OA\Property(property="user_id", type="integer", example=2),
     *                       @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/1702785355.jpg"),
     *                       @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                       @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     *                                   @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                       @OA\Property(property="content", type="string", example="Chào mừng tổng thống"),
     * @OA\Property(property="attendances_count", type="interger", example=3),
     * @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="name", type="string", example="Kurtis Legros IV"),
     *                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                     @OA\Property(property="phone", type="string", example="+1 (564) 267-3494"),
     *                     @OA\Property(property="role", type="integer", example="1"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-02T08:55:45.000000Z")
     *                 )
     *                  )
     *              )
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
            $logUserRole = auth()->user()->role;
            if($logUserRole != 2){
                return response()->json([
                    'message' => 'Không phải quản lí thì sẽ không có quyền xóa',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_CONFLICT
                ], Response::HTTP_CONFLICT);
            }
            //Xóa ảnh
            $imagePath = public_path('Upload/'.$event->banner);
            File::delete($imagePath);

            $event->delete();
            $restOfEvents = event::with('user')->get();
            $restOfEvents->map(function ($event) {
                $imageUrl = asset("Upload/{$event->banner}");
                $event->banner = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
                return $event;
            });
            return response()->json([
                'metadata'=> $restOfEvents,
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
