<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Inventory Management API',
    version: '1.0.0',
    description: 'REST API for PT Telkomsel Inventory Management System. Authenticate via POST /api/v1/auth/login to receive a Bearer token.',
    contact: new OA\Contact(email: 'admin@telkom.com')
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Enter the token returned by POST /api/v1/auth/login'
)]
#[OA\Schema(
    schema: 'ApiResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Data retrieved successfully'),
        new OA\Property(property: 'data', type: 'object'),
    ]
)]
abstract class Controller
{
    use AuthorizesRequests;
}
