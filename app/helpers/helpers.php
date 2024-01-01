<?php

use Illuminate\Http\Response;
function handleData($status = false,$data){
    return ($status) ? [
        'metadata' => $data,
        'message' => 'Get All Records Successfully',
        'status' => 'success',
        'statusCode' => Response::HTTP_OK
    ] : [
        'metadata' => [
            'docs' => $data->items(),
            'totalDocs' => $data->total(),
            'limit' => $data->perPage(),
            'totalPages' => $data->lastPage(),
            'page' => $data->currentPage(),
            'pagingCounter' => $data->currentPage(), // Bạn có thể sử dụng currentPage hoặc số khác nếu cần
            'hasPrevPage' => $data->previousPageUrl() != null,
            'hasNextPage' => $data->nextPageUrl() != null
//                    'prevPage' => $atendance->previousPageUrl(),
//                    'nextPage' =>$atendance->nextPageUrl(),
        ],
        'message' => 'Lấy thành công tất cả các bản ghi',
        'status' => 'success',
        'statusCode' => Response::HTTP_OK
    ];
}
