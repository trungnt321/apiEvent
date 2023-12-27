<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
//    /**
//     * @OA\Post(
//     *     path="/api/get-google-sign-in-url",
//     *     summary="Get Google Sign-In URL",
//     *     tags={"Authentication"},
//     *     operationId="getGoogleSignInUrl",
//     *     @OA\Response(
//     *         response=200,
//     *         description="Successful operation",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="metadata", type="object", @OA\Property(property="url", type="string")),
//     *             @OA\Property(property="message", type="string"),
//     *             @OA\Property(property="status", type="string"),
//     *             @OA\Property(property="statusCode", type="integer")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Internal Server Error",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="status", type="string"),
//     *             @OA\Property(property="message", type="string"),
//     *             @OA\Property(property="statusCode", type="integer")
//     *         )
//     *     )
//     * )
//     */
    public function getGoogleSignInUrl()
    {
        try {
            $url = Socialite::driver('google')->stateless()
                ->redirect()->getTargetUrl();
//            return response()->json([
//                'url' => $url,
//            ])->setStatusCode(Response::HTTP_OK);
            return response()->json([
                'metadata' => ['url' => $url],
                'message' => 'Lấy thành công url login',
                'status' => 'success',
                'statusCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return $exception;
        }
    }

//    /**
//     * @OA\Get(
//     *     path="/api/callback",
//     *     summary="Google Login Callback",
//     *     tags={"Authentication"},
//     *     operationId="loginCallback",
//     *     @OA\Response(
//     *         response=200,
//     *         description="Successful operation",
//     *              @OA\JsonContent(
//         *             @OA\Property(property="metadata", type="object",
//         *                 @OA\Property(property="id", type="integer", example=1),
//         *                 @OA\Property(property="name", type="string", example="John Doe"),
//         *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
//         *                 @OA\Property(property="phone", type="string", example="123456789"),
//         *                 @OA\Property(property="role", type="integer", example=1),
//         *                 @OA\Property(property="token", type="string", example="api-token")
//         *             ),
//         *             @OA\Property(property="message", type="string", example="Đăng nhập người dùng thành công"),
//         *             @OA\Property(property="status", type="string", example="success"),
//         *             @OA\Property(property="statusCode", type="integer", example=200),
//         *         )
//     *     ),
//     *     @OA\Response(
//     *         response=400,
//     *         description="Bad Request",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="status", type="string"),
//     *             @OA\Property(property="message", type="string"),
//     *             @OA\Property(property="statusCode", type="integer")
//     *         )
//     *     ),
//     *     @OA\Response(
//     *         response=500,
//     *         description="Internal Server Error",
//     *         @OA\JsonContent(
//     *             @OA\Property(property="status", type="string"),
//     *             @OA\Property(property="message", type="string"),
//     *             @OA\Property(property="statusCode", type="integer")
//     *         )
//     *     )
//     * )
//     */
    public function loginCallback(Request $request)
    {
        try {

            $state = $request->input('state');

            parse_str($state, $result);
            $googleUser = Socialite::driver('google')->stateless()->user();
            $finduser = User::where('google_id', $googleUser->id)->first();

            if($finduser){
                Auth::login($finduser);
                $finduser->token = auth()->user()->createToken("API Token")->accessToken;
                return response()->json([
                    'metadata' => $finduser,
                    'message' => 'Đăng nhập thành công',
                    'status' => 'success',
                    'statusCode' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }else{
                $newUser = User::create([
                    'email' => $googleUser->email,
                    'name' => $googleUser->name,
                    'google_id'=> $googleUser->id,
                    'password'=> bcrypt('123'),
                    'avatar' => $googleUser->avatar
                ]);
//                $newUser->token = $newUser->createToken('API Token')->accessToken;
                $newUser->token = $newUser->createToken('API Token')->accessToken;

                return response()->json([
                    'metadata' => $newUser,
                    'message' => 'Đăng nhập thành công',
                    'status' => 'success',
                    'statusCode' => Response::HTTP_OK
                ], Response::HTTP_OK);

            }
//            $user = User::firstOrCreate(
//                [
//                    'email' => $googleUser->email,
//                    'name' => $googleUser->name,
//                    'google_id'=> $googleUser->id,
//                    'password'=> bcrypt('123'),
//                    'avatar' => $googleUser->avatar
//                ]
//            );
//            return response()->json([
//                'status' => __('google sign in successful'),
//                'data' => $user,
//            ], Response::HTTP_CREATED);

        } catch (\Exception $exception) {
            return response([
                "status" => "error",
                "message" =>$exception->getMessage(),
                'statusCode' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
