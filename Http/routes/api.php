<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Agency\Http\Controllers\AgencyController;

/*
|--------------------------------------------------------------------------
| ROUTE MODUL Agency (konvensi core: api/v1 + auth:sanctum)
|--------------------------------------------------------------------------
|   /api/v1/agencies
|     GET    /                              agency:view
|     POST   /                              agency:create
|     GET    /{id}                          agency:view
|     PUT    /{id}                          agency:edit
|     DELETE /{id}                          agency:delete
|     GET    /{id}/units                    unit:view
|     GET    /{id}/activity-logs            agency:view
|*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('agencies')->group(function () {
        Route::get('/', [AgencyController::class, 'index'])->middleware('permission:agency:view');
        Route::post('/', [AgencyController::class, 'store'])->middleware('permission:agency:create');
        Route::get('/{id}', [AgencyController::class, 'show'])->whereNumber('id')->middleware('permission:agency:view');
        Route::put('/{id}', [AgencyController::class, 'update'])->whereNumber('id')->middleware('permission:agency:edit');
        Route::delete('/{id}', [AgencyController::class, 'destroy'])->whereNumber('id')->middleware('permission:agency:delete');
        Route::get('/{id}/activity-logs', [AgencyController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:agency:view');
        Route::get('/{id}/units', [AgencyController::class, 'units'])->whereNumber('id')->middleware('permission:unit:view');

        // Jurisdictions (wilayah kerja unit)
        Route::post('/jurisdictions/move', [AgencyController::class, 'moveJurisdiction'])->middleware('permission:jurisdiction:edit');
        Route::get('/{id}/jurisdictions', [AgencyController::class, 'jurisdictions'])->whereNumber('id')->middleware('permission:jurisdiction:view');
        Route::post('/{id}/jurisdictions', [AgencyController::class, 'storeJurisdictions'])->whereNumber('id')->middleware('permission:jurisdiction:create');
        Route::delete('/{id}/jurisdictions/{regencyId}', [AgencyController::class, 'destroyJurisdiction'])->whereNumber('id')->whereNumber('regencyId')->middleware('permission:jurisdiction:delete');
    });
});
