<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
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
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object", example={"id": 1, "name": "John Doe", "email": "john.doe@example.com", "phone": "123456789", "role": "user"}),
     *             @OA\Property(property="message", type="string", example="Register users Successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="int", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example={"name": {"name cannot be empty"}, "email": {"email cannot be empty"}}),
     *             @OA\Property(property="statusCode", type="int", example=500),
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validator   = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required',
            'password' => 'required',
            'phone' => 'required',
            'role' => 'required'
        ], [
            'name.required' => 'name cannot be empty',
            'name.max' =>  'Maximum 255 characters allowed',
            'email.required' => 'email cannot be empty',
            'password.required' => 'password cannot be empty',
            'phone.required' => 'phone cannot be empty',
            'role.required' => 'role cannot be empty',
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
            'message' => 'Register users Successfully',
            'status' => 'success',
            'statusCode' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="metadata", type="object", example={"id": 1, "name": "John Doe", "email": "john.doe@example.com", "phone": "123456789", "role": "user", "token": "api-token"}),
     *             @OA\Property(property="message", type="string", example="Login users Successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="statusCode", type="int", example=200),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Incorrect Details. Please try again"),
     *             @OA\Property(property="statusCode", type="int", example=500),
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
                "message" => 'Incorrect Details.
            Please try again',
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        auth()->user()->token = auth()->user()->createToken('API Token')->accessToken;

        return response()->json([
            'metadata' => auth()->user(),
            'message' => 'Login users Successfully',
            'status' => 'success',
            'statusCode' => Response::HTTP_OK
        ], Response::HTTP_OK);

    }
}
