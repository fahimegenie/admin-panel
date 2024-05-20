<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\PatienCaseController;
use App\Http\Controllers\API\PendingApprovalController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::controller(UserController::class)->group(function () {
        Route::get('users', 'index');
        Route::post('users/store', 'store');
        Route::get('users/{user_id}', 'detail');
        Route::post('users/update/{user_id}', 'update');
        Route::delete('users/{user_id}', 'delete');
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('roles', 'index');
        Route::post('roles/store', 'store');
        Route::get('roles/{role_id}', 'detail');
        Route::post('roles/update/{role_id}', 'update');
        Route::delete('roles/{role_id}', 'delete');
    });
    Route::controller(PermissionController::class)->group(function () {
        Route::get('permissions', 'index');
        Route::post('permissions/store', 'store');
        Route::get('permissions/{permission_id}', 'detail');
        Route::post('permissions/update/{permission_id}', 'update');
        Route::delete('permissions/{permission_id}', 'delete');
    });

    Route::controller(PatienCaseController::class)->group(function () {
        Route::get('patient_cases', 'index');
        Route::post('patient_cases/store', 'store');
        Route::get('patient_cases/{p_case_id}', 'detail');
        Route::post('patient_cases/update/{p_case_id}', 'update');
        Route::delete('patient_cases/{p_case_id}', 'delete');
    });

    Route::controller(PendingApprovalController::class)->group(function () {
        Route::get('pending_approvals', 'index');
        Route::post('pending_approvals/store', 'store');
        Route::get('pending_approvals/{pending_approval_id}', 'detail');
        Route::post('pending_approvals/update/{pending_approval_id}', 'update');
        Route::delete('pending_approvals/{pending_approval_id}', 'delete');
    });


});