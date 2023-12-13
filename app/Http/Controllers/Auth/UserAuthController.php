<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserAuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Đăng ký người dùng mới",
     *     description="
     *      - Endpoint này cho phép đăng ký người dùng mới vào hệ thống.
     *      - Trả về thông tin của người dùng đã đăng ký.
     *      - Role được sử dụng là nhân viên và sinh viên",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="123456789"),
     *             @OA\Property(property="role", type="int", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object", example={"id": 1, "name": "John Doe", "email": "john.doe@example.com", "phone": "123456789", "role": "user"}),
     *             @OA\Property(property="message", type="string", example="Đăng ký người dùng thành công"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="int", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"name": {"tên không thể để trống"}, "email": {"email không thể để trống"}}),
     *             @OA\Property(property="statusCode", type="int", example=500),
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        $validator   = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'phone' => 'required',
            'role' => ['required',
                Rule::in([0, 1])]
        ], [
            'name.required' => 'tên không thể để trống',
            'name.max' => 'Tối đa 255 ký tự được phép',
            'email.required' => 'email không thể để trống',
            'email.unique' => 'email đã tồn tại',
            'password.required' => 'mật khẩu không thể để trống',
            'phone.required' => 'số điện thoại không thể để trống',
            'role.required' => 'vai trò không thể để trống',
            'role.in' => 'Role phải là Nhân viên hoặc sinh viên'
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
            'message' => 'Tạo tài khoản thành công',
            'status' => 'success',
            'statusCode' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Đăng nhập người dùng",
     *     description="
     *      - Endpoint này cho phép người dùng đăng nhập vào hệ thống.
     *      - Trả về thông tin của người dùng đã đăng nhập, bao gồm cả token đăng nhập.
     *      - Role được sử dụng là cả ba role nhân viên ,quản lí ,sinh viên",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="phone", type="string", example="123456789"),
     *                 @OA\Property(property="role", type="integer", example=1),
     *                 @OA\Property(property="token", type="string", example="api-token")
     *             ),
     *             @OA\Property(property="message", type="string", example="Đăng nhập người dùng thành công"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi máy chủ nội bộ",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Thông tin đăng nhập không chính xác. Vui lòng thử lại"),
     *             @OA\Property(property="statusCode", type="integer", example=500),
     *         )
     *     )
     * )
     */



    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response([
                "status" => "error",
                "message" => 'Tài khoản mật khẩu không chính xác.
            Vui lòng thử lại',
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        auth()->user()->token = auth()->user()->createToken('API Token')->accessToken;

        return response()->json([
            'metadata' => auth()->user(),
            'message' => 'Đăng nhập thành công',
            'status' => 'success',
            'statusCode' => Response::HTTP_OK
        ], Response::HTTP_OK);

    }
}
