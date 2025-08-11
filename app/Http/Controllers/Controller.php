<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="SUNU Santé API Documentation",
 *      description="API pour le projet Zéro Papier de SUNU Santé",
 *      @OA\Contact(
 *          email="support@sunusante.com"
 *      )
 * )
 */
abstract class Controller extends BaseController
{
    //
}
