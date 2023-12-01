<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParticipantsResources;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class participantsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/participants",
     *     summary="Get all participants records",
     *     tags={"Participants"},
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
     *          @OA\Property(property="name",type="string",example="Phuc La"),
     *     @OA\Property(property="email",type="string",example="phucminhbeos@gmail.com"),
     *     @OA\Property(property="phone",type="string",example="0866058725"),   
     *     @OA\Property(property="role",type="integer",example=1),  
     *     @OA\Property(property="created_at",type="string",format="date-time",example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at",type="string",format="date-time",example="2023-11-23 11:20:22"),
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
            $users = User::all();
            return response()->json([
                'metadata' => $users,
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
     *     path="/api/participants",
     *     tags={"Participants"},
     *     summary="Store a new participants record",
     *     description="Store a new participants record with the provided data.",
     *     operationId="storeParticipants",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="1"),
     *             @OA\Property(property="email", type="string", example="2"),
     *              @OA\Property(property="password", type="string", example="2"),
     *          @OA\Property(property="phone", type="string", example="2"),
     *          @OA\Property(property="role", type="integer", example=1),
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
     *             @OA\Property(property="message", type="object", example={"user_id": {"Không đúng định dạng"}}),
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required'
                ],
                'email' => [
                    'required'
                ],
                'password' => [
                    'required',
                    'min:6'
                ],
                'phone' => [
                    'required',
                    'regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/'
                ],
                'role' =>[
                    'required',
                    Rule::in([0, 1])
                ]
            ], [
                'name.required' => 'Không để trống name của người dùng',
                'email.required' => 'Không để trống email của người dùng',
                'password.required' => 'Password không dược để trống',
                'phone.required'=> 'Số điện thoại không được để trống',
                'phone.regex'=> 'Số điện thoại không đúng định dạng',
                'role.required' => 'Role không được để trống',
                'role.in' => 'Role phải là 0 hoặc 1'
            ]);

            if($validator->fails()){
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $user = User::create($request->all());
            return response()->json([
                'metadata' => $user,
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
     *      path="/api/participants/{id}",
     *      operationId="getParticipantsById",
     *      tags={"Participants"},
     *      summary="Get participants by ID",
     *      description="Get a specific participants by its ID.",
     *      @OA\Parameter(
     *          name="id",
     *          description="Participants ID",
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
     *     @OA\Property(property="name",type="string",example="1"),
     *     @OA\Property(property="email",type="string",example="2"),
     *     @OA\Property(property="phone",type="string",example="2"),   
     *     @OA\Property(property="role",type="integer",example=2),  
     *     @OA\Property(property="created_at",type="string",format="date-time",example="2023-11-23 11:20:22"),
     *     @OA\Property(property="updated_at",type="string",format="date-time",example="2023-11-23 11:20:22"),
     * )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Participants not found",
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
            $users = User::findOrFail($id);
            return response()->json([
                'metadata' => $users,
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
     *      path="/api/participants/{id}",
     *      operationId="updateParticipants",
     *      tags={"Participants"},
     *      summary="Update Participants",
     *      description="Update a specific participants.",
     *      @OA\Parameter(
     *          name="participants",
     *          description="participants model",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *             @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="2"),
     *  @OA\Property(property="email", type="string", example="2"),
     * @OA\Property(property="phone", type="string", example="2"),
     * @OA\Property(property="role", type="integer", example=2),
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
    public function update(Request $request,$id)
    {
        $user = User::findOrFail($id);
        if(auth()->check()){
            $logUserRole = auth()->user()->role;
        }else{
            return response([
                'status' => 'error',
                'message' => 'Not logged in yet',
                'statusCode' => Response::HTTP_UNAUTHORIZED
            ],Response::HTTP_UNAUTHORIZED);
        }
        
        $roleUpdate = $request->input('role');
        $canUpdate = false;

        //Validate cho request
        $validator = Validator::make($request->all(), [
            'name' => [
                'required'
            ],
            'email' => [
                'required',
                'regex:~^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$~'
            ],
            'phone' => [
                'required',
                'regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/'
            ],
            'role' =>[
                'required'
            ]
        ], [
            'name.required' => 'Không để trống name của người dùng',
            'email.required' => 'Không để trống email của người dùng',
            'email.regex' => 'Email được nhập vào không đúng định dạng',
            'password.required' => 'Password không dược để trống',
            'phone.required'=> 'Số điện thoại không được để trống',
            'phone.regex'=> 'Số điện thoại không đúng định dạng',
            'role.required' => 'Role không được để trống'
        ]);

        //Nếu nó sai từ validate request thì nó dừng luôn
        if($validator->fails()){
            return response([
                "status" => "error",
                "message" => $validator->errors(),
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //Check role của từng người 
        if($logUserRole == 2){
            $canUpdate = true;
        }else if($logUserRole == 1){
            if($roleUpdate == 2){
                return response([
                    "status" => "error",
                    "message" => "Employees cannot edit manager information",
                    "statusCode" => Response::HTTP_CONFLICT
                ], Response::HTTP_CONFLICT);
            }else{
                //Đây là 2 trường hợp còn lại là 0,1 : nhân viên, sinh viên
                $canUpdate = true;
            }
        }else{
            //Trường hợp còn lại là sinh viên thì không cho chỉnh sửa bất cứ cải gì
            return response([
                "status" => "error",
                "message" => "Students cannot edit anything",
                "statusCode" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }
        if($canUpdate == true){
            $user->update($request->all());
        }
        return response()->json([
            'metadata' => $user,
            'message' => 'Update One Record Successfully',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/participants/{id}",
     *     summary="Delete an participants record",
     *     tags={"Participants"},
     *     @OA\Parameter(
     *         name="participants",
     *         in="path",
     *         required=true,
     *         description="Participants record model",
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
    public function destroy($id){
        try{
            $user = User::findOrFail($id);
            if(!$user){
                return response()->json([
                    'message' => 'Record not exists',
                    'status' => 'error',
                    'statusCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }
            $user->delete();
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
