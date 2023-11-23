<?php

namespace App\Http\Controllers;

use App\Models\atendance;
use App\Http\Resources\AtendanceResources;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Http\Request;

class atendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $atendance = atendance::all();
            return response([
                "status" => "success",
                "payload" => AtendanceResources::collection($atendance)
            ], 200);
        }catch(\Exception $e) {
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'event_id' => [
                    'required'
                ],
                'user_id' => [
                    'required',
                    Rule::exists('users', 'id')->where(function ($query) {
                        $query->whereIn('role', [1, 2]);
                    })
//                    Rule::unique('atendance')->where(function ($query) use ($request) {
//                        return $query->where('event_id', $request->event_id)
//                            ->where('user_id', $request->user_id);
//                    }),
                ],
            ], [
                'event_id.required' => 'Không để trống ID sự kiện',
                'user_id.required' => 'Không để trống ID người dùng',
                'user_id.exists' => 'Chức vụ không hợp lệ.'
            ]);

            if($validator->fails()){
                return response(['status' => 'error', 'message' => $validator->errors()], 500);
            }

            atendance::create($request->all());
            return response([   "status" => "success",'message' =>'Tạo mới thành công!!'], 200);
        } catch (\Exception $e){
            return response([
                "status" => "error",
                "message" => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(atendance $atendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, atendance $atendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(atendance $atendance)
    {
        //
    }
}
