<?php

namespace App\Http\Controllers;

use App\Models\resource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     *     summary="Get all resource records",
     *     tags={"Resource"},
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
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                      @OA\Property(property="name", type="string", example="Anh su kien"),
     *                      @OA\Property(property="url", type="string", format="binary"),
     *                      @OA\Property(property="event_id", type="interger", example=1),
     *                 )
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
            $resource = resource::all()->map(function ($resource) {
                $imageUrl = asset("Upload/{$resource->url}");
                $resource->url = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
                return $resource;
            });
            return response()->json([
                'metadata' => $resource,
                'message' => 'Get All Records Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ],Response::HTTP_OK);
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
     *     summary="Store a new resources record",
     *     description="Store a new resource record with the provided data.",
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
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"user_id": {"User ID is required"}}),
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
                    "message" => $validate->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            if(auth()->user()->role == 1 || auth()->user()->role == 2){
                $imageName = time().'.'.$request->url->extension();  
                $request->url->move(public_path('Upload'), $imageName);
                $resourceData = $request->all();
                $resourceData['url'] = $imageName;
                $resource = resource::create($resourceData);

                $resource->url = url("Upload/{$resource->url}");
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
     *      summary="Get resources by ID",
     *      description="Get a specific resource by its ID.",
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
            $resource->url = url("Upload/{$resource->url}");
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
     *      summary="Get resources by Event ID",
     *      description="Get a specific resource by Event ID.",
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
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                      @OA\Property(property="name", type="string", example="Anh su kien"),
     *                      @OA\Property(property="url", type="string", format="binary"),
     *                      @OA\Property(property="event_id", type="interger", example=1),
     *                 )
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
    public function GetRecordByEventId($event_id){
        try{
            $resourceById = DB::table('resources')->where('event_id',$event_id)->get()
            ->map(function ($resourceById) {
                $imageUrl = asset("Upload/{$resourceById->url}");
                $resourceById->url = $imageUrl; // Thay đổi giá trị trường `url` của mỗi đối tượng
                return $resourceById;
            });;
            return response()->json([
                'metadata' => $resourceById,
                'message' => 'Get All Record By Event ID Successfully',
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
     * @OA\Put(
     *      path="/api/resource/{id}",
     *      operationId="updateResource",
     *      tags={"Resource"},
     *      summary="Update Resource",
     *      description="Update a specific resources.",
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
     *             @OA\Property(property="event_id", type="integer", example="1"),
     *             @OA\Property(property="user_id", type="integer", example="2")
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
    public function update(Request $request, $id)
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
                    "message" => $validate->errors(),
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
                $imagePath = public_path('Upload/'.$resource->url);
                File::delete($imagePath);
    
                //Thêm ảnh mới 
                $imageName = time().'.'.$request->url->extension();  
                $request->url->move(public_path('Upload'), $imageName);

                $resourceData = $request->all();
                $resourceData['url'] = $imageName;
                $resource->update($resourceData);

                $resource->url = url("Upload/{$resource->url}");
                return response()->json([
                    'metadata' => $resource,
                    'message' => 'Update Record Successfully',
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
     * @OA\Delete(
     *     path="/api/resource/{id}",
     *     summary="Delete an resource record",
     *     tags={"Resource"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of resource model",
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
            $imagePath = public_path('Upload/'.$resource->url);
            File::delete($imagePath);
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
