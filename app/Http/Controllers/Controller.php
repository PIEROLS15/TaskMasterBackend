<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(
 *     title="API Tasks",
 *     version="1.0",
 *     description="Listado de URI´S de la API Tasks",
 * )
 *
 * @OA\Server(url="http://taskmasterbackend.test")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}