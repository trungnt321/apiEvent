<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\keywords;
class keywordsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/keywords",
     *     summary="Lấy ra tất cả các keywords",
     *     tags={"keywords"},
     *     description="
     *      - Endpoint này cho phép lấy ra các keywords.
     *      - Trả về thông tin keywords,
     *      - Tất cả role đều được sử dụng
     *     - Sẽ có 1 số option param sau
     *     - page=<số trang> chuyển sang trang cần
     *     - limit=<số record> số record muốn lấy trong 1 trang
     *     - pagination=true|false sẽ là trạng thái phân trang hoặc không phân trang <mặc định là false phân trang>
     *     ",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with keywords data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully retrieved all keywords"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="docs", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example="1"),
     *                         @OA\Property(property="name", type="string", example="sự kiện"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                     )
     *                 ),
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
    public function index(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $page = $request->query('search', "");
            $status = $request->query('pagination', false);
            $keywords = ($status) ? keywords::all(): keywords::paginate($limit, ['*'], 'page', $page);
            if (!$status && $page > $keywords->lastPage()) {
                $page = 1;
                $keywords = keywords::paginate($limit, ['*'], 'page', $page);
            }
            return response()->json(handleData($status,$keywords), Response::HTTP_OK);
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
     * @OA\Get(
     *     path="/api/searchKeyword?search=tukhoa1",
     *     summary="Lấy ra 10 keywords nổi bật",
     *     tags={"keywords"},
     *     description="Endpoint này cho phép lấy ra các keywords nổi bật. Trả về thông tin keywords và sự kiện của keywords đó. Tất cả role đều được sử dụng
     *     - Sẽ có 1 số option param sau
     *     - page=<số trang> chuyển sang trang cần
     *     - limit=<số record> số record muốn lấy trong 1 trang
     *     - pagination=true|false sẽ là trạng thái phân trang hoặc không phân trang <mặc định là false phân trang>
     *     - search=<text search> nội dung cần search mặc định thì sẽ là tất cả
     *     ",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=true,
     *         description="Từ khóa tìm kiếm",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with keywords data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully retrieved all keywords"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="docs", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example="1"),
     *                         @OA\Property(property="name", type="string", example="sự kiện"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-11-28 17:02:29"),
     *                         @OA\Property(property="events_count", type="integer", example=4),
     *                         @OA\Property(property="events", type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example="1"),
     *                                 @OA\Property(property="name", type="string", example="Event Name"),
     *                                 @OA\Property(property="location", type="string", example="Hai Phong"),
     *                                 @OA\Property(property="contact", type="string", example="0983467584"),
     *                                 @OA\Property(property="status", type="integer", example=2),
     *                                 @OA\Property(property="description", type="string", example="Sự kiện rất hoành tráng"),
     *                                 @OA\Property(property="content", type="string", example="Sự kiện rất hoành tráng"),
     *                                 @OA\Property(property="banner", type="string", example="http://127.0.0.1:8000/Upload/anh1.jpg"),
     *                                 @OA\Property(property="start_time", type="string", example="2024-01-01 14:19:35"),
     *                                 @OA\Property(property="end_time", type="string", example="2024-01-02 14:19:35"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time", example=null),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time", example=null),
     *                                 @OA\Property(property="attendances_count", type="integer", example=1),
     *                                 @OA\Property(property="laravel_through_key", type="integer", example=2),
     *                                 @OA\Property(property="user", type="object",
     *                                     @OA\Property(property="id", type="integer", example="1"),
     *                                     @OA\Property(property="name", type="string", example="HẬU ĐẶNG"),
     *                                     @OA\Property(property="email", type="string", example="haudvph20519@fpt.edu.vn"),
     *                                     @OA\Property(property="avatar", type="string", example="https://lh3.googleusercontent.com/a/ACg8ocL2nrwZ_mNIBGYaLd8tnzAJLMR0g_UXSVhY_BN67ZWA=s96-c"),
     *                                     @OA\Property(property="role", type="integer", example=2),
     *                                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:54:12.000000Z"),
     *                                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:54:12.000000Z")
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="totalDocs", type="integer", example=16),
     *                 @OA\Property(property="limit", type="integer", example=10),
     *                 @OA\Property(property="totalPages", type="integer", example=2),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="pagingCounter", type="integer", example=1),
     *                 @OA\Property(property="hasPrevPage", type="boolean", example=false),
     *                 @OA\Property(property="hasNextPage", type="boolean", example=false)
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
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */

    public function searchEvent(Request $request){
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $search = $request->query('search', "");
            $status = $request->query('pagination', false);
            //return $page;

            $query = keywords::with([
                'events' => function ($query) {
                    $query->withCount('attendances')->with('user');
                }
            ]);
            $query->withCount('events');
            $query->orderByDesc('events_count'); // Sắp xếp giảm dần theo số lượng sự kiện
            $query->take(10);
// Thêm điều kiện tìm kiếm theo trường 'name' nếu có dữ liệu search
            $query->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });

// Nếu status là true, sử dụng get thay vì paginate
            $keywords = ($status) ? $query->get() : $query->paginate($limit, ['*'], 'page', $page);

// Kiểm tra nếu page vượt quá lastPage, reset về page 1
            if (!$status && $page > $keywords->lastPage()) {
                $page = 1;
                $keywords = keywords::with([
                    'events' => function ($query) {
                        $query->withCount('attendances')->with('user');
                    }
                ])->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                })->paginate($limit, ['*'], 'page', $page);
            }

// Xử lý sự kiện và trả về JSON response
            $keywords->each(function ($keyword) {
                $keyword->events->each(function ($event) {
                    $imageUrl = asset("Upload/{$event->banner}");
                    $event->banner = $imageUrl;
                });
            });
            return response()->json(handleData($status,$keywords), Response::HTTP_OK);
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
     *     path="/api/keywords",
     *     tags={"keywords"},
     *     summary="Tạo 1 keywords mới",
     *     description="
     *      - Endpoint này cho phép tạo thêm 1 keywords mới.
     *      - Trả về thông tin các phản hồi của sự kiện đó.
     *      - Role được sử dụng quản quản lí, nhân viên.
     *      - `name` là tên từ khóa",
     *     operationId="storeKeywords",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Từ khóa 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Thêm phản hồi thành công"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example="3"),
     *                 @OA\Property(property="name", type="string", example="Từ khóa 1"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-05T12:36:46.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-05T12:36:46.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User does not exist"),
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
                'name' => 'required|unique:keywords,name|max:40'
            ], [
                'name.required' => 'Không để trống tên từ khóa',
                'name.unique' => 'Tên từ khóa đã tồn tại',
                'name.max' => 'Tên từ khóa tối đa 40 kí tự',
            ]);

            if($validator->fails()){
                return response([
                    "status" => "error",
                    "message" => $validator->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $user =  Auth::user();
            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người dùng không hợp lệ",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $keywords = keywords::create($request->all());
            return response()->json([
                'metadata' => $keywords,
                'message' => 'Thêm từ khóa thành công',
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
     *     path="/api/keywords/{id}",
     *     summary="Lấy ra chi tiết 1 keywords",
     *     description="
     *      - Endpoint này cho phép lấy ra chi tiết 1 keywords.
     *      - Trả về thông tin các phản hồi của sự kiện đó.
     *      - Role được sử dụng quản quản lí, nhân viên
     *      - id là của keywords đó",
     *     tags={"keywords"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của keywords",
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
     *                 @OA\Property(property="id", type="integer", example="2"),
     *                 @OA\Property(property="name", type="string", example="từ khóa 1"),
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
     *             @OA\Property(property="message", type="string", example="Keywords không tồn tại"),
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
    public function show(string $id)
    {
        try {
            $keywords = keywords::with([
                'events' => function ($query) {
                    $query->withCount('attendances')->with('user');
                }
            ])->find($id);
            $user =  Auth::user();
            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người dùng không hợp lệ",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if(!$keywords){
                return response([
                    "status" => "error",
                    "message" => "Keywords không tồn tại",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return response()->json([
                'metadata' => $keywords,
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
     * @OA\Patch(
     *     path="/api/keywords/{id}",
     *     tags={"keywords"},
     *     summary="Cập nhật lại keywords",
     *     description="
     *      - Endpoint này cho phép cập nhật lại nội dung 1 keywords.
     *      - Trả về thông tin các keywords đó.
     *      - Role được sử dụng quản quản lí,nhân viên
     *      - id là của keywords đó",
     *     operationId="updateKeywords",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the keywords record to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Từ khóa update")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cập nhật thành công keywords"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example="3"),
     *                 @OA\Property(property="name", type="string", example="keywords content update"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-12-05T12:36:46.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-12-05T12:39:16.000000Z"),
     *             )
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
    public function update(Request $request, $id)
    {
        try {
            $keywords = keywords::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'unique:keywords,name|max:40'
            ], [
                'name.required' => 'Không để trống tên từ khóa',
                'name.unique' => 'Tên từ khóa đã tồn tại',
                'name.max' => 'Tên từ khóa tối đa 40 kí tự',
            ]);

            if ($validator->fails()) {
                return response([
                    "status" => "error",
                    "message" => $validator->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $user =  Auth::user();
            if($user->role == 0){
                return response([
                    "status" => "error",
                    "message" => "Role người dùng không hợp lệ",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $keywords->update($request->only(['name']));
            return response()->json([
                'metadata' => $keywords,
                'message' => 'Cập nhật keywords thành công',
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
     *     path="/api/keywords/{id}",
     *     tags={"keywords"},
     *     summary="Xóa keywords",
     *     description="
     *      - Xóa 1 phản hồi bằng ID
     *      - Trả về thông báo xóa thành công.
     *      - Role được sử dụng quản quản lí
     *      - id là của keywords đó",
     *     operationId="deleteKeywords",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the keywords record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Xóa keywords thành công"),
     *             @OA\Property(property="statusCode", type="int", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bản ghi không tồn tại",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
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
    public function destroy(string $id)
    {
        try {
            $keywords = keywords::findOrFail($id);
            if (!$keywords) {
                return response()->json([
                    'message' => 'Keywords không tồn tại',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            $user =  Auth::user();
            if($user->role == 0 || $user->role == 1){
                return response([
                    "status" => "error",
                    "message" => "Role người dùng không hợp lệ",
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $keywords->delete();

            return response()->json([
                'message' => 'Xóa keywords thành công',
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
