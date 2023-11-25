<?php

namespace App\Http\Controllers;

use App\Models\notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailApi;
use App\Jobs\SendEmailJob;
use Carbon\Carbon;

class notificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentDateTime = \Illuminate\Support\Carbon::now()->toDateTimeString();
        $emails = notification::where('time_send', '<=', $currentDateTime)
            ->with('user_receiver',function ($query){
                $query->select('id','name','email');
            })
            ->whereNull('sent_at')
            ->get();

        dd($emails);

        return response([
            "status" => "success",
            "message" => $emails
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/notification",
     *     tags={"notification"},
     *     summary="Send Email",
     *     description="Send Email to Users",
     *     operationId="notification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Thư nhắc nhở sự kiện"),
     *             @OA\Property(property="message", type="string", example="<h1 style='color:red;'>This is a Heading</h1>"),
     *             @OA\Property(property="email", type="string", example="example@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Email sent successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Validation error or internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="object", example="not found")
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = [
                'title' => $request->title,
                'message' => $request->message,
            ];


            Mail::to($request->email)->send(new EmailApi($data));
            return response([
                "status" => "success",
                "message" => 'Email sent successfully'
            ], 200);
        }catch (\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(notification $notification)
    {
        //
    }
}
