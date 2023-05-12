<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Dropbox PHP",
 *         version="1.0.0",
 *     )
 * )
 *
 * Class Controller
 * @package App\Http\Controllers
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
