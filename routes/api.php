<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\ModificationReceivedController;
use App\Http\Controllers\API\NeedMoreInfoController;
use App\Http\Controllers\API\PatienCaseController;
use App\Http\Controllers\API\PendingApprovalController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\StepFileReadyController;
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

    Route::controller(ModificationReceivedController::class)->group(function () {
        Route::get('modification_receiveds', 'index');
        Route::post('modification_receiveds/store', 'store');
        Route::get('modification_receiveds/{modification_received_id}', 'detail');
        Route::post('modification_receiveds/update/{modification_received_id}', 'update');
        Route::delete('modification_receiveds/{modification_received_id}', 'delete');
    });

    Route::controller(NeedMoreInfoController::class)->group(function () {
        Route::get('need_more_infos', 'index');
        Route::post('need_more_infos/store', 'store');
        Route::get('need_more_infos/{need_more_info_id}', 'detail');
        Route::post('need_more_infos/update/{need_more_info_id}', 'update');
        Route::delete('need_more_infos/{need_more_info_id}', 'delete');
    });

    Route::controller(StepFileReadyController::class)->group(function () {
        Route::get('step_file_readys', 'index');
        Route::post('step_file_readys/store', 'store');
        Route::get('step_file_readys/{step_file_ready_id}', 'detail');
        Route::post('step_file_readys/update/{step_file_ready_id}', 'update');
        Route::delete('step_file_readys/{step_file_ready_id}', 'delete');
    });


    
    

});