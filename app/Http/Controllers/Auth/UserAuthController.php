<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserAuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"authentication"},
     *     summary="Register a new user",
     *     description="Register a new user with the provided information.",
     *     operationId="registerUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+8412331212"),
     *             @OA\Property(property="role", type="int", example="1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="role", type="string"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object"),
     *         )
     *     ),
     * )
     */
    public function register(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required',
            'password' => 'required',
            'phone' => 'required',
            'role' => 'required'
        ]);

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);
        return response([ 'user' => $user]);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"authentication"},
     *     summary="User login",
     *     description="Authenticate a user with email and password.",
     *     operationId="loginUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="role", type="int"),
     *                 @OA\Property(property="created_at", type="datetime"),
     *                 @OA\Property(property="updated_at", type="datetime"),
     *                 @OA\Property(property="token", type="string",example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZjIyMTE5OGE4Mzc3ZDhkYWJhNmE0ZjRlYzFlM2Y0ZWQwZGE3ZmZjMDhkNTg5Mjg1MDhjMTZlYzczNWYxNjgyMjI0NjkxNGM3NGUwNGJlNDQiLCJpYXQiOjE3MDExNjkwNjIuMTI4MDIsIm5iZiI6MTcwMTE2OTA2Mi4xMjgwMjMsImV4cCI6MTczMjc5MTQ2Mi4wMDc4NjYsInN1YiI6IjMzIiwic2NvcGVzIjpbXX0.0T5247AY4CFiI-avwoMOvbDrPIZdziUfUALcjyKJ4GMRhc5eFdChDvG2l8TrAkl9WGq2pzUqhu-IJUcNLbW6XJ0BIQHdrvPZSTLvYDlk7myUel01g_ZGbp9Kn_vRCemruq6T9q8mmd624ryuP_rOQbFViEpfa2UQUrisfooiGNDXhfWR0B5HUrsoIjC5AaFDgekMgg5OyOsRzu-uW7rUj-Ylp8GRLPi5q3o1aanqwwwrWQV7x0xYj3OyEYfcjYitVNTehhlzcEb6QvObemrMox8SfT-s9pA2nlqTfNg_ZTtpDQJ-pWQLGAgw2pI8lXvWJ6SI_uc2S2FDEgY9GDwyXsY-dhCL__3bQc4vtNG-Fl29ByYlA9bZovwCfT7gRJ1E82Mb9qNRUVeavW-ja1KVngXRC6LmOKVcC0SiETDdGo5yloXwfuzMjKWA9CGn6Lye7NyxopS7eajElnmHz9Ytomxxtt-BBJS0fqjxhLrsqHK-SRh4GfO6bXyxZrCAtsac35w06SmFn9ohZQ5L0vMQtd_mtIHi0SVQpCLyVsUhOfNIQti5d-JIWvPOrmf_D8mnts3d_mMX45HhyVo_PLwXFHauXFua0d8FH4m9CjG2QkLZd0d2TD2bZ6KI_XhkYbV0ba4D1uQuMgB3jh06DXvoMxqA57LhbvC43-pmVfyAAoQ"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error_message", type="string", example="Incorrect Details. Please try again."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object"),
     *         )
     *     ),
     * )
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response(['error_message' => 'Incorrect Details.
            Please try again']);
        }

        auth()->user()->token = auth()->user()->createToken('API Token')->accessToken;

        return response(['user' => auth()->user()]);

    }
}
