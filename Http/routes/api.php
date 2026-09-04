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
        Route::get('/', [AgencyController::class, 'index'])->middleware('permission:agency:view|agency:surveyor-register');
        Route::post('/', [AgencyController::class, 'store'])->middleware('permission:agency:create');
        Route::get('/{id}', [AgencyController::class, 'show'])->whereNumber('id')->middleware('permission:agency:view|agency:surveyor-register');
        Route::put('/{id}', [AgencyController::class, 'update'])->whereNumber('id')->middleware('permission:agency:edit');
        Route::delete('/{id}', [AgencyController::class, 'destroy'])->whereNumber('id')->middleware('permission:agency:delete');
        Route::get('/{id}/activity-logs', [AgencyController::class, 'activityLogs'])->whereNumber('id')->middleware('permission:agency:view');
        Route::get('/{id}/units', [AgencyController::class, 'units'])->whereNumber('id')->middleware('permission:unit:view');
        Route::get('/{id}/companies', [AgencyController::class, 'companies'])->whereNumber('id')->middleware('permission:agency:view');

        // Jurisdictions (wilayah kerja unit)
        Route::post('/jurisdictions/move', [AgencyController::class, 'moveJurisdiction'])->middleware('permission:jurisdiction:edit');
        Route::get('/{id}/jurisdictions', [AgencyController::class, 'jurisdictions'])->whereNumber('id')->middleware('permission:jurisdiction:view');
        Route::post('/{id}/jurisdictions', [AgencyController::class, 'storeJurisdictions'])->whereNumber('id')->middleware('permission:jurisdiction:create');
        Route::delete('/{id}/jurisdictions/{regencyId}', [AgencyController::class, 'destroyJurisdiction'])->whereNumber('id')->whereNumber('regencyId')->middleware('permission:jurisdiction:delete');

        // Registrasi surveyor lintas dinas (1 baris per HO).
        Route::post('/{id}/surveyor-registration', [AgencyController::class, 'register'])->whereNumber('id')->middleware('permission:agency:surveyor-register');

        // Opsi pengawas utk form assign (semua user role pengawas/pengawas-spesialis)
        Route::get('/pengawas/options', [AgencyController::class, 'pengawasOptions'])->middleware('permission:pengawas:view');
        // Surveyor-registrasi: agency-admin lihat semua; surveyor (register lintas
        // dinas) lihat baris miliknya sendiri (difilter di controller).
        Route::get('/{id}/surveyor-registrations', [AgencyController::class, 'registrations'])->whereNumber('id')->middleware('permission:agency:approve-surveyor-registration|agency:surveyor-register');
        Route::post('/{id}/surveyor-registrations/{regId}/decide', [AgencyController::class, 'decide'])->whereNumber('id')->whereNumber('regId')->middleware('permission:agency:approve-surveyor-registration');
    });
});
