<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="EcoLibrary API",
 *      description="API documentation for the EcoLibrary project",
 *      @OA\Contact(
 *          email="admin@ecolibrary.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Demo API Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="apiKey",
 *      description="Enter token in format (Bearer <token>)",
 *      name="Authorization",
 *      in="header",
 * )
 */
class SwaggerController extends Controller
{
}
