<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="AppObras API",
 *   description="API v1 de autenticação e conta",
 * )
 *
 * @OA\Server(
 *   url="/api",
 *   description="API base"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
class SwaggerController extends Controller {}
