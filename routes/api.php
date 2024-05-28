<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\ModificationReceivedController;
use App\Http\Controllers\API\NeedMoreInfoController;
use App\Http\Controllers\API\PatientCaseController;
use App\Http\Controllers\API\PendingApprovalController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\StepFileReadyController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TeamsController;
use App\Http\Controllers\API\CasePlansController;
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
        Route::get('users/{guid}', 'detail');
        Route::post('users/update/{guid}', 'update');
        Route::delete('users/{guid}', 'destroy');
        Route::get('user/treatment-planners', 'treatmentPlanners');
        Route::get('user/treatment-planners-quality-check', 'treatmentPlannersQualityCheck');
        


        
    });

    Route::controller(RoleController::class)->group(function () {
        Route::get('roles', 'index');
        Route::post('roles/store', 'store');
        Route::get('roles/{guid}', 'detail');
        Route::post('roles/update/{guid}', 'update');
        Route::delete('roles/{guid}', 'destroy');
    });
    Route::controller(PermissionController::class)->group(function () {
        Route::get('permissions', 'index');
        Route::post('permissions/store', 'store');
        Route::get('permissions/{guid}', 'detail');
        Route::post('permissions/update/{guid}', 'update');
        Route::delete('permissions/{guid}', 'destroy');
    });

    Route::controller(PatientCaseController::class)->group(function () {
        Route::get('patient_cases', 'index');
        Route::post('patient_cases/store', 'store');
        Route::get('patient_cases/{guid}', 'detail');
        Route::post('patient_cases/update/{guid}', 'update');
        Route::delete('patient_cases/{guid}', 'destroy');
        Route::post('patient_cases/case_assign_to', 'case_assign_to');
    });

    Route::controller(PendingApprovalController::class)->group(function () {
        Route::get('pending_approvals', 'index');
        Route::post('pending_approvals/store', 'store');
        Route::get('pending_approvals/{guid}', 'detail');
        Route::post('pending_approvals/update/{guid}', 'update');
        Route::delete('pending_approvals/{guid}', 'destroy');
    });

    Route::controller(ModificationReceivedController::class)->group(function () {
        Route::get('modification_receiveds', 'index');
        Route::post('modification_receiveds/store', 'store');
        Route::get('modification_receiveds/{guid}', 'detail');
        Route::post('modification_receiveds/update/{guid}', 'update');
        Route::delete('modification_receiveds/{guid}', 'destroy');
    });

    Route::controller(NeedMoreInfoController::class)->group(function () {
        Route::get('need_more_infos', 'index');
        Route::post('need_more_infos/store', 'store');
        Route::get('need_more_infos/{guid}', 'detail');
        Route::post('need_more_infos/update/{guid}', 'update');
        Route::delete('need_more_infos/{guid}', 'destroy');
    });

    Route::controller(StepFileReadyController::class)->group(function () {
        Route::get('step_file_readys', 'index');
        Route::post('step_file_readys/store', 'store');
        Route::get('step_file_readys/{guid}', 'detail');
        Route::post('step_file_readys/update/{guid}', 'update');
        Route::delete('step_file_readys/{guid}', 'destroy');
    });

    Route::controller(TeamsController::class)->group(function () {
        Route::get('teams', 'index');
        Route::post('teams/store', 'store');
        Route::get('teams/{guid}', 'detail');
        Route::post('teams/update/{guid}', 'update');
        Route::delete('teams/{guid}', 'destroy');
        Route::post('team/assign-teams', 'assignUserToTeams');

    });

    Route::controller(CasePlansController::class)->group(function () {
        Route::get('case_plans', 'index');
        Route::post('case_plans/store', 'store');
        Route::get('case_plans/{guid}', 'detail');
        Route::post('case_plans/update/{guid}', 'update');
        Route::delete('case_plans/{guid}', 'destroy');
    });

    


    
    

});

