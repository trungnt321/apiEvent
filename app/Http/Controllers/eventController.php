<?php

namespace App\Http\Controllers;

use App\Models\event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\EventResources;

class eventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/event",
     *     tags={"Event"},
     *     summary="Store a new event record",
     *     description="Store a new event record with the provided data.",
     *     operationId="storeEvent",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="2"),
     *  @OA\Property(property="location", type="string", example="2"),
     * @OA\Property(property="contact", type="string", example="2"),
     *      @OA\Property(property="user_id", type="integer", example=2),
     * @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     * @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
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
     *             @OA\Property(property="message", type="object", example={"user_id": {"Không để trống ID người dùng"}}),
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        //Check valiadate
        $validate = Validator::make($request->all(),[
            'name'=> ['required'],
            'location'=> ['required'],
            'contact'=>[
                'required',
                'regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/'
            ],
            'user_id'=> [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', [1, 2]);
                })
            ],
            'start_time'=> ['required'],
            'end_time'=> ['required','after:start_time']
        ],[
            'name.required' => 'Không để trống name của của sự kiện nhập',
            'location.required' =>'Không được để trống địa điểm của sự kiện',
            'contact.required' =>'Không được để trống phần liên lạc',
            'contact.regex'=>'Định dạng số điện thoại được nhập không đúng',
            'user_id.required'=>'User Id không được để trống',
            'start_time.required'=>'Ngày khởi đầu của event không được để trống',
            'end_time.required'=>'Ngày kết thúc của event không được để trống',
            'end_time.after'=>'Ngày kết thúc của dự án phải lớn hơn ngày bắt đầu'
        ]);
        if($validate->fails()){
            return response(['status' => 'error', 'message' => $validate->errors()], 422);
        }
        $logUserRole = auth()->user()->role;
        if($logUserRole == 1 || $logUserRole == 2){
            //Only staff and admin can make event
            try{
                event::create($request->all());
                return response([
                    "status" => "success",
                    "message" => 'Thêm mới bản ghi thành công'
                ], 200);
            }catch(\Exception $e){
                return response([
                    "status" => "error",
                    "message" => $e->getMessage()
                ], 500);
            }           
        }
        return response([
            'status' => 'error', 
            'message' => 'Chỉ nhân viên và quản lí mới có thể thêm mơi sự kiện']
        ,500);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //Get details of the resource
        
    }

   /**
     * @OA\Put(
     *      path="/api/event/{id}",
     *      operationId="updateEvent",
     *      tags={"Event"},
     *      summary="Update Event",
     *      description="Update a specific event.",
     *      @OA\Parameter(
     *          name="event",
     *          description="event model",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *             @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="2"),
     *  @OA\Property(property="location", type="string", example="2"),
     * @OA\Property(property="contact", type="string", example="2"),
     * @OA\Property(property="status", type="integer", example=2),
     *      @OA\Property(property="user_id", type="integer", example=2),
     * @OA\Property(property="start_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
     * @OA\Property(property="end_time", type="string",format="date-time", example="2023-11-23 11:20:22"),
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
    public function update(Request $request, $id)
    {
        //Check validate
        $validate = Validator::make($request->all(),[
            'name'=> ['required'],
            'location'=> ['required'],
            'contact'=>[
                'required',
                'regex:/^(\+?\d{1,3}[- ]?)?\d{10}$/'
            ],
            'status'=>[
                'required',
                Rule::in([0,1])
            ],
            'user_id'=> [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role', [1, 2]);
                })
            ],
            'start_time'=> ['required'],
            'end_time'=> ['required','after:start_time']
        ],[
            'name.required' => 'Không để trống name của của sự kiện nhập',
            'location.required' =>'Không được để trống địa điểm của sự kiện',
            'contact.required' =>'Không được để trống phần liên lạc',
            'contact.regex'=>'Định dạng số điện thoại được nhập không đúng',
            'status.required' =>'Trạng thái của sự kiện không được để trống',
            'user_id.required'=>'User Id không được để trống',
            'start_time.required'=>'Ngày khởi đầu của event không được để trống',
            'end_time.required'=>'Ngày kết thúc của event không được để trống',
            'end_time.after'=>'Ngày kết thúc của dự án phải lớn hơn ngày bắt đầu'
        ]);
        if($validate->fails()){
            return response(['status' => 'error', 'message' => $validate->errors()], 422);
        }

        $logUserRole = auth()->user()->role;
        if($logUserRole == 1 || $logUserRole == 2){
            //Check role
            $event = event::findOrFail($id);
            try{
                $event->update($request->all());
                return response([
                    'status' => 'success', 
                    'message' => 'Chỉnh sửa thành công'
                    ]
                ,500);
            }catch(\Exception $e){
                return response([
                    'status' => 'error', 
                    'message' => $e->getMessage()
                    ]
                ,500);
            }
        }
        return response([
            'status' => 'error', 
            'message' => 'Chỉ nhân viên và quản lí mới có thể sửa  sự kiện']
        ,500);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(event $event)
    {
        //
    }
}
