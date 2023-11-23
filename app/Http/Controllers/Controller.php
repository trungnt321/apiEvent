<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(
 *    version = "1.0.0",
 *    title="L5 OpenAPI",
 *     description="L5 Swagger OpenAPI description",
 *    @OA\Contact(
 *      email = "darius@matulion.lt"
 *     ),
 *     @OA\License(
 *      name = "Apache 2.0",
 *      url = "http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * ),
 *   @OA\Get(
 *       path = "/",
 *       description = "Home page",
 *       @OA\Response(response = "default", description = "Welcome Page")
 *    ),
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
