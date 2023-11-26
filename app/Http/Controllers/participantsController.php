<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParticipantsResources;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;

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
            $users = User::all();
            return response([
                "status" => "success",
                "payload" => ParticipantsResources::collection($users)
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
                return response(['status' => 'error', 'message' => $validator->errors()], 500);
            }

            User::create($request->all());
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
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         example="1"
     *     ),
     *     @OA\Property(
     *         property="email",
     *         type="string",
     *         example="2"
     *     ),
     *     @OA\Property(
     *         property="phone",
     *         type="string",
     *         example="2"
     *     ),   
     *     @OA\Property(
     *         property="role",
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
            return response([
                "status" => "success",
                "payload" => new ParticipantsResources($users),
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
            return response(['status' => 'error', 'message' => 'Chưa đăng nhập nên không thể vào chỉnh sửa'], 500);
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
            return response(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        //Check role của từng người 
        if($logUserRole == 2){
            $canUpdate = true;
        }else if($logUserRole == 1){
            if($roleUpdate == 2){
                return response([
                    "status" => "Conflict",
                    "message" => "Nhân viên không thể chỉnh sửa quản lí được",
                ], 422);
            }else{
                //Đây là 2 trường hợp còn lại là 0,1 : nhân viên, sinh viên
                $canUpdate = true;
            }
        }else{
            //Trường hợp còn lại là sinh viên thì không cho chỉnh sửa bất cứ cải gì
            return response([
                "status" => "Conflict",
                "message" => "Sinh viên không có quyền chỉnh sửa bất cứ cái gì",
            ], 422);
        }
        if($canUpdate == true){
            $user->update($request->all());
        }
        return response([
            "status" => "success",
            "payload" => new ParticipantsResources($user)
        ], 200);
    }
}
