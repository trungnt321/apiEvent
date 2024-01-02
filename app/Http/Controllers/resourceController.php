<?php

namespace App\Http\Controllers;

use App\Models\resource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class resourceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/resource",
     *     summary="Lấy tất cả bản ghi",
     *     tags={"Resource"},
     * description="
     *      - Endpoint trả về hình ảnh của tất cả sự kiện.
     *      - Role được sử dụng là tất cả các role ",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Get One Record Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="docs", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                      @OA\Property(property="name", type="string", example="Anh su kien"),
     *                      @OA\Property(property="url", type="string", format="binary"),
     *                      @OA\Property(property="event_id", type="interger", example=1),
     *                 ))
     *             ),
     *             @OA\Property(property="totalDocs", type="integer", example=16),
     *                 @OA\Property(property="limit", type="integer", example=10),
     *                 @OA\Property(property="totalPages", type="integer", example=2),
     *                 @OA\Property(property="page", type="integer", example=2),
     *                 @OA\Property(property="pagingCounter", type="integer", example=2),
     *                 @OA\Property(property="hasPrevPage", type="boolean", example=true),
     *                 @OA\Property(property="hasNextPage", type="boolean", example=false)
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
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $status = $request->query('pagination', false);
            $resource = ($status) ? resource::all() :  resource::paginate($limit, ['*'], 'page', $page);
            if ($page > $resource->lastPage()) {
                $page = 1;
                $resource = resource::paginate($limit, ['*'], 'page', $page);
            }
//            $resource->map(function ($resource) {
//                $imageUrl = asset("Upload/{$resource->url}");
//                $resource->url = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
//                return $resource;
//            });
            return response()->json(handleData($status,$resource),Response::HTTP_OK);
        }catch(\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status'=>'error',
                'statusCode'=>$e instanceof HttpException
                    ? $e->getStatusCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR
            ],  $e instanceof HttpException
                ? $e->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

/**
     * @OA\Post(
     *     path="/api/resource",
     *     tags={"Resource"},
     *     summary="Lưu trữ một bản ghi tài nguyên mới với dữ liệu được cung cấp.",
     *     description="
     * -Dữ liệu được thêm vào là post
     * -name là tên ảnh
     * -url là file ảnh, sẽ được lưu trong CSDL là thời gian hienj tại, để tránh bị trùng lạp
     * event_id là id của sự kiện
     * -Role được sử dụng là tất cả các role",
     *     operationId="storeResource",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="1"),
     *             @OA\Property(property="url", type="string", format="binary"),
     *              @OA\Property(property="event_id", type="interger", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Create Record Successfully"),
     *             @OA\Property(property="statusCode", type="int", example=200),
     *              @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="error"),
     *                     @OA\Property(property="message", type="string", example="Server error"),
     *                     @OA\Property(property="statusCode", type="integer", example=500)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi xác thực hoặc lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"user_id": {"ID người dùng là bắt buộc"}}),
     *             @OA\Property(property="statusCode", type="int", example=500),

     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {

        try{
            $validate = Validator::make($request->all(),[
                'name'=>'required',
                'url'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'event_id'=>[
                    'required',
                    Rule::exists('events', 'id'),
                ]
            ],[
                'name.required' => 'Name must not be empty',
                'url.required' => 'Url must not be empty',
                'event_id.required' => 'Event id must not be empty',
                'event_id.exists' => 'Event id does not exist',
            ]);
            if($validate->fails()){
                return response([
                    "status" => "error",
                    "message" => $validate->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if(auth()->user()->role == 1 || auth()->user()->role == 2){
                $imageName = time().'.'.$request->url->extension();
                $request->url->storeAs('Upload', $imageName, 'public');
//                $request->url->move(public_path('Upload'), $imageName);
                $resourceData = $request->all();
                $resourceData['url'] = $imageName;
                $resource = resource::create($resourceData);

//                $resource->url = url("Upload/{$resource->url}");
                return response()->json([
                    'metadata' => $resource,
                    'message' => 'Create Record Successfully',
                    'status' => 'success',
                    'statusCode' => Response::HTTP_OK
                        ], Response::HTTP_OK);
            }
            return response([
                "status" => "error",
                "message" => "Students cannot edit anything",
                "statusCode" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }catch(\Exception $e){
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
     *      path="/api/resource/{id}",
     *      operationId="getResourceById",
     *      tags={"Resource"},
     *      summary="Lấy model resource theo ID",
     *      description="
     * -Lấy một bản ghi hình ảnh theo id cho trước
     * - Endpoint trả về hình ảnh của tất cả sự kiện.
     * -name là tên ảnh
     * -url là url của ảnh đó
     * event_id là id của event ",
     *      @OA\Parameter(
     *          name="id",
     *          description="Resource ID",
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
     *                  @OA\Property(property="name", type="string", example="1"),
     *                  @OA\Property(property="url", type="string", format="binary"),
     *                  @OA\Property(property="event_id", type="interger", example=1),
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
        try{

            $resource = resource::findOrFail($id);
            if(!$resource){
                return response()->json([
                    'message' => 'Record not exist',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
//            $resource->url = url("Upload/{$resource->url}");
            return response()->json([
                'metadata' => $resource,
                'message' => 'Get One Record Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }catch(\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


       /**
     * @OA\Get(
     *      path="/api/resourceByEventID/{event_id}",
     *      operationId="getResourceByEventId",
     *      tags={"Resource"},
     *      summary="Nhận tài nguyên cụ thể bằng ID sự kiện.",
     *      description="
     * - Endpoint trả về tất cả ảnh của một sự kiện
     * -event_id là id của sự kiện
     * -Role được sử dụng là tất cả các role
        *     - Sẽ có 1 số option param sau
        *     - page=<số trang> chuyển sang trang cần
        *     - limit=<số record> số record muốn lấy trong 1 trang
        *     - pagination=true|false sẽ là trạng thái phân trang hoặc không phân trang <mặc định là false phân trang>
     * ",
     *      @OA\Parameter(
     *          name="event_id",
     *          description="Event ID",
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
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                 @OA\Property(property="docs", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                      @OA\Property(property="name", type="string", example="Anh su kien"),
     *                      @OA\Property(property="url", type="string", format="binary"),
     *                      @OA\Property(property="event_id", type="interger", example=1),
     *                 ))
     *             ),
     *                 @OA\Property(property="totalDocs", type="integer", example=16),
     *                 @OA\Property(property="limit", type="integer", example=10),
     *                 @OA\Property(property="totalPages", type="integer", example=2),
     *                 @OA\Property(property="page", type="integer", example=2),
     *                 @OA\Property(property="pagingCounter", type="integer", example=2),
     *                 @OA\Property(property="hasPrevPage", type="boolean", example=true),
     *                 @OA\Property(property="hasNextPage", type="boolean", example=false)
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
    public function GetRecordByEventId($event_id,Request $request){
        try{
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $status = $request->query('pagination', false);
            $query = resource::where('event_id',$event_id);
            $resourceById = ($status) ? $query->get() : $query->paginate($limit, ['*'], 'page', $page);
            if ($page > $resourceById->lastPage()) {
                $page = 1;
                $resourceById = resource::where('event_id',$event_id)->paginate($limit, ['*'], 'page', $page);
            }
//            $resourceById->map(function ($resourceById) {
//                $imageUrl = asset("Upload/{$resourceById->url}");
//                $resourceById->url = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
//                return $resourceById;
//            });;
            return response()->json(handleData($status,$resourceById), Response::HTTP_OK);
        }catch(\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }
       /**
     * @OA\Put(
     *      path="/api/resource/{id}",
     *      operationId="updateResource",
     *      tags={"Resource"},
     *      summary="Sửa dữ liệu bản ghi hình ảnh",
     *      description="
     * -Endpoint trả về hình ảnh mà mình đã sửa
     * -Role được sử dụng là role nhân viên và quản lí
     * -Ảnh mới sẽ được thêm vào đồng thời xóa đi ảnh cũ
        * -Ảnh mới sẽ là ảnh được chuyển đổi sang mã base 64
        *     ",
    *       @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the resource record",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="integer", example="1"),
     *             @OA\Property(property="url", type="string", example="anh1.jpg")
     *             @OA\Property(property="event_id", type="integer", example="2")
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
     *                 @OA\Property(property="name", type="string", example="Anh su kien"),
     *                 @OA\Property(property="url", type="string", format="binary"),
     *                 @OA\Property(property="event_id", type="interger", example=1),
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
    public function update(Request $request, $id)
    {
        try{
            $validate = Validator::make($request->all(),[
                'name'=>'required',
                'url'=>'required',
                'event_id'=>[
                    'required',
                    Rule::exists('events', 'id'),
                ]
            ],[
                'name.required' => 'Tên ảnh phải có',
                'url.required' => 'Url không được để trống',
                'event_id.required' => 'ID event không được để trống',
                'event_id.exists' => 'ID event không tồn tại',
            ]);
            if($validate->fails()){
                return response([
                    "status" => "error",
                    "message" => $validate->errors()->all(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $resource = resource::findOrFail($id);
            if(!$resource){
                return response()->json([
                    'message' => 'Record not exists',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }


            if(auth()->user()->role != 0){
                //Xóa ảnh
//                $imagePath = public_path('Upload/'.$resource->url);
//                File::delete($imagePath);
                Storage::disk('public')->delete('Upload/' .$resource->getRawOriginal('url'));
                //Thêm ảnh mới
                $image_64 = $request->url; //your base64 encoded data

                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];   // .jpg .png .pdf

                $replace = substr($image_64, 0, strpos($image_64, ',')+1);

// find substring fro replace here eg: data:image/png;base64,

                $image = str_replace($replace, '', $image_64);

                $image = str_replace(' ', '+', $image);

                $imageName = Str::random(10).'.'.$extension;
//                $request->url->move(public_path('Upload'), $imageName);
                Storage::disk('public')->put('Upload/' . $imageName, base64_decode($image));
                $resourceData = $request->all();
                $resourceData['url'] = $imageName;
                $resource->update($resourceData);

//                $resource->url = url("Upload/{$resource->url}");
                return response()->json([
                    'metadata' => $resource,
                    'message' => 'Update Record Successfully',
                    'status' => 'success',
                    'statusCode' => Response::HTTP_OK
                        ], Response::HTTP_OK);
            }
            return response([
                "status" => "error",
                "message" => "Học sinh không thể sửa ảnh",
                "statusCode" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);

        }catch(\Exception $e){
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
     * @OA\Delete(
     *     path="/api/resource/{id}",
     *     summary="Delete an resource record",
     * description="
     * -Xóa đi một ảnh theo một id cho trước
     * -Ảnh sẽ xóa trong SQL, đồng thời xóa trong thư mục Backend
     * -Role được sử dụng là tất cả các role
     * ",
     *     tags={"Resource"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của mô hình tài nguyên",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Delete One Record Successfully"),
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
        try{
            $resource = resource::findOrFail($id);
            if(!$resource){
                return response()->json([
                    'message' => 'Record not exists',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
            //Xóa ảnh
//            $imagePath = public_path('Upload/'.$resource->url);
//            File::delete($imagePath);
            Storage::disk('public')->delete('Upload/' .$resource->getRawOriginal('url'));
            $resource->delete();
            return response()->json([
                'message' => 'Delete One Record Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);

        }catch(\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
