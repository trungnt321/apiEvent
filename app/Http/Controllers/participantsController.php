<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParticipantsResources;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\DB;

class participantsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/participants",
     *     summary="Get all participants records",
     *     tags={"Participants"},
     *     @OA\Response(
     *         response=200,
     *         description="Dữ liệu trả về thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Get All Record Successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Phuc La"),
     *                     @OA\Property(property="email", type="string", example="phuclaf@gmail.com"),
     *                     @OA\Property(property="password", type="string", example="123456"),
     *                     @OA\Property(property="phone", type="string", example="0983118272"),
     *                     @OA\Property(property="role", type="integer", example=1),
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
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại "),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi server"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
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
 *     path="/api/searchUser",
 *     summary="Get user information by email and phone",
 *     tags={"Participants"},
 *     description="Tìm kiếm người dùng theo email và số điện thoại",
 *     operationId="getUserByEmailAndPhone",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="email", type="string", example="phuclaf@gmail.com"),
 *             @OA\Property(property="phone", type="string", example="0983118272")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Dữ liệu người dùng được trả về thành công"),
 *             @OA\Property(property="statusCode", type="integer", example=200),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="name", type="string", example="Phuc La"),
 *                 @OA\Property(property="email", type="string", example="phuclaf@gmail.com"),
 *                 @OA\Property(property="password", type="string", example="123456"),
 *                 @OA\Property(property="phone", type="string", example="0983118272"),
 *                 @OA\Property(property="role", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Không tìm thấy người dùng mong muốn",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Không tìm thấy người dùng"),
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
    public function getUserByEmailAndPhone(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'email'=>'required',
                'phone'=> 'required'
                
            ],[
                'email.required' => 'Email không được để trống',
                'phone.required' => 'Số điện thoại không được để trống'
            ]);
            if($validator->fails()){
                return response([
                    "status" => "error",
                    "message" => $validator->errors(),
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $data = $request->all();
            $email = $data['email'];
            $phone = $data['phone'];
            $users = DB::table('users')
            ->where(function($query) use ($email, $phone) {
                $query->where('email', 'like', "%{$email}%")
                    ->orWhere('phone', 'like', "%{$phone}%");
            })
            ->get();
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
     *              @OA\Property(property="name", type="string", example="Phuc La"),
     *                     @OA\Property(property="email", type="string", example="phuclaf@gmail.com"),
     *                     @OA\Property(property="password", type="string", example="123456"),
     *                     @OA\Property(property="phone", type="string", example="0983118272"),
     *                     @OA\Property(property="role", type="integer", example=1),
     *         )
     *     ),
     *    @OA\Response(
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
     *                     @OA\Property(property="name", type="string", example="Phuc La"),
     *                     @OA\Property(property="email", type="string", example="phuclaf@gmail.com"),
     *                     @OA\Property(property="password", type="string", example="123456"),
     *                     @OA\Property(property="phone", type="string", example="0983118272"),
     *                     @OA\Property(property="role", type="integer", example=1),
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
            $data = $validator->validated();
            $data['password'] = bcrypt($request->password);
            $user = User::create($data);
            return response()->json([
                'metadata' => $user,
                'message' => 'Tạo mới bản ghi thành công',
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
 *      description="Lấy dữ liệu người dùng theo id cho trước",
 *      @OA\Parameter(
 *          name="id",
 *          description="Participant ID",
 *          required=true,
 *          in="path",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Lấy bản ghi thành công"),
 *             @OA\Property(
 *                 property="metadata",
 *                 type="object",
 *                         @OA\Property(property="name", type="string", example="Phuc La"),
     *                     @OA\Property(property="email", type="string", example="phuclaf@gmail.com"),
     *                     @OA\Property(property="password", type="string", example="123456"),
     *                     @OA\Property(property="phone", type="string", example="0983118272"),
     *                     @OA\Property(property="role", type="integer", example=1),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Không tìm thấy bản ghi nào như thế",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Không tìm thấy bản ghi nào như thế"),
 *             @OA\Property(property="statusCode", type="integer", example=404)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Lỗi server",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Lỗi server"),
 *             @OA\Property(property="statusCode", type="integer", example=500)
 *         )
 *     )
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
     *      description="Sửa dữ liệu bản ghi theo id cho trước",
     *      @OA\Parameter(
     *          name="id",
     *          description="Mẫu người dùng",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *             @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Phuc La"),
     *             @OA\Property(property="email", type="string", example="phucla@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="0982221151"),
     *             @OA\Property(property="role", type="integer", example=1),
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="Dữ liệu trả về thành công",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Sửa dữ liệu thành công"),
     *             @OA\Property(
     *                 property="metadata",
     *                 type="object",
     *                      @OA\Property(property="id", type="string", example="1"),
     *                      @OA\Property(property="name", type="string", example="Phuc La"),
     *                      @OA\Property(property="email", type="string", example="phucla@gmail.com"),
     *                      @OA\Property(property="phone", type="string", example="0982221151"),
     *                      @OA\Property(property="role", type="integer", example=1),
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
     *         description="Mô hình dữ liệu người dùng",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Xóa bản ghi thành công"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Record not exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bản ghi không tồn tại"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Lỗi server"),
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
