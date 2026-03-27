<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\NoCache;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Unit\PpmpController;
use App\Http\Controllers\Admin\PpmpController as AdminPpmpController;
use App\Http\Controllers\Bac\ProcurementFlowController as BacProcurementFlowController;
use App\Http\Controllers\Bac\PurchaseRequestController as BacPurchaseRequestController;
use App\Http\Controllers\Admin\PurchaseRequestController as AdminPurchaseRequestController;

/*
|--------------------------------------------------------------------------
| Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showRoleSelect'])
    ->middleware(NoCache::class)
    ->name('landing');

/*
|--------------------------------------------------------------------------
| Admin Auth
|--------------------------------------------------------------------------
*/

Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])
    ->middleware(NoCache::class)
    ->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);

Route::get('/admin/register', [AuthController::class, 'showAdminRegister'])
    ->middleware(NoCache::class)
    ->name('admin.register');
Route::post('/admin/register', [AuthController::class, 'registerAdmin']);

Route::get('/login', function () {
    return redirect('/');
});
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
    ->middleware(NoCache::class)
    ->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'processForgotPassword'])
    ->name('password.email');
Route::get('/password/reset-choice', [AuthController::class, 'showResetPasswordChoice'])
    ->middleware([NoCache::class, 'signed'])
    ->name('password.reset.choice');
Route::post('/password/reset-choice', [AuthController::class, 'processResetPasswordChoice'])
    ->middleware([NoCache::class, 'signed'])
    ->name('password.reset.choice.submit');

/*
|--------------------------------------------------------------------------
| BAC Auth
|--------------------------------------------------------------------------
*/

Route::get('/bac/login', [AuthController::class, 'showBacLogin'])
    ->middleware(NoCache::class)
    ->name('bac.login');
Route::post('/bac/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Unit Auth
|--------------------------------------------------------------------------
*/

Route::get('/unit/login', [AuthController::class, 'showUnitLogin'])
    ->middleware(NoCache::class)
    ->name('unit.login');
Route::post('/unit/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', NoCache::class])->group(function () {
    Route::get('/change-password', function () {
        return view('auth.change-password');
    })->name('password.change');

    Route::post('/change-password', [AuthController::class, 'updatePassword'])
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Dashboards
|--------------------------------------------------------------------------
*/

Route::middleware([NoCache::class, 'admin'])->get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

Route::middleware([NoCache::class, 'bac'])->get('/bac/dashboard', function () {
    return view('bac.dashboard');
});

Route::middleware([NoCache::class, 'unit'])->get('/dashboard', [UnitController::class, 'dashboard'])
    ->name('unit.dashboard');

/*
|--------------------------------------------------------------------------
| Admin - Users / PR / Reports / Profile
|--------------------------------------------------------------------------
*/

Route::middleware([NoCache::class, 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/create', [UserController::class, 'create']);
    Route::post('/users/store', [UserController::class, 'store']);

    Route::get('/users/{id}/edit', [UserController::class, 'edit']);
    Route::post('/users/{id}/update', [UserController::class, 'update']);
    Route::post('/users/{id}/disable', [UserController::class, 'disable']);

    Route::get('/fund-sources/{departmentId}', [UserController::class, 'getFundSources']);

    Route::get('/validate-request', [AdminPurchaseRequestController::class, 'index'])
        ->name('admin.validate-request.index');
    Route::post('/validate-request/{id}/approve', [AdminPurchaseRequestController::class, 'approve'])
        ->name('admin.validate-request.approve');
    Route::post('/validate-request/{id}/disapprove', [AdminPurchaseRequestController::class, 'disapprove'])
        ->name('admin.validate-request.disapprove');

    Route::get('/consolidated-request', [AdminPurchaseRequestController::class, 'consolidated'])
        ->name('admin.consolidated-request.index');
    Route::get('/consolidated-request/print', [AdminPurchaseRequestController::class, 'printConsolidated'])
        ->name('admin.consolidated-request.print');

    Route::get('/reports', [AdminPurchaseRequestController::class, 'reports'])
        ->name('admin.reports.index');

    Route::get('/profile', function () {
        return view('admin.profile');
    })->name('admin.profile');
});

/*
|--------------------------------------------------------------------------
| Admin - PPMP
|--------------------------------------------------------------------------
*/

Route::middleware([NoCache::class, 'admin'])->prefix('admin')->group(function () {
    Route::get('/ppmp/validate', [AdminPpmpController::class, 'index'])
        ->name('admin.ppmp.validate');
    Route::get('/ppmp/{id}/details', [AdminPpmpController::class, 'show'])
        ->name('admin.ppmp.show');
    Route::get('/ppmp/{id}/review', [AdminPpmpController::class, 'review'])
        ->name('admin.ppmp.review');
    Route::post('/ppmp/{id}/approve', [AdminPpmpController::class, 'approve'])
        ->name('admin.ppmp.approve');
    Route::post('/ppmp/{id}/disapprove', [AdminPpmpController::class, 'disapprove'])
        ->name('admin.ppmp.disapprove');

    Route::get('/ppmp/consolidated', [AdminPpmpController::class, 'consolidated'])
        ->name('admin.ppmp.consolidated');
    Route::get('/ppmp/consolidated/print', [AdminPpmpController::class, 'printConsolidated'])
        ->name('admin.ppmp.consolidated.print');

    Route::get('/ppmp/unit', [AdminPpmpController::class, 'unit'])
        ->name('admin.ppmp.unit');
});

/*
|--------------------------------------------------------------------------
| Unit - PPMP / PR / Notifications / Profile
|--------------------------------------------------------------------------
*/

Route::middleware([NoCache::class, 'unit'])->prefix('unit')->group(function () {
    Route::post('/fund-source', [UnitController::class, 'setActiveFundSource'])
        ->name('unit.fund-source.set');
    Route::get('/ppmp', [PpmpController::class, 'index'])->name('unit.ppmp.index');
    Route::get('/ppmp/create', [PpmpController::class, 'create'])->name('unit.ppmp.create');
    Route::post('/ppmp/store', [PpmpController::class, 'store'])->name('unit.ppmp.store');
    Route::get('/ppmp/upload', [PpmpController::class, 'showUploadForm'])->name('unit.ppmp.uploadForm');
    Route::post('/ppmp/upload', [PpmpController::class, 'upload'])->name('unit.ppmp.upload');
    Route::get('/ppmp/{id}/edit', [PpmpController::class, 'edit'])->name('unit.ppmp.edit');
    Route::post('/ppmp/{id}/add-item', [PpmpController::class, 'addItem'])->name('unit.ppmp.addItem');

    Route::get('/ppmp-remaining', [UnitController::class, 'viewRemainingItems'])
        ->name('unit.ppmp.remaining');

    Route::get('/purchase-requests', [UnitController::class, 'index'])
        ->name('unit.pr.index');
    Route::get('/purchase-requests/create', [UnitController::class, 'create'])
        ->name('unit.pr.create');
    Route::post('/purchase-requests', [UnitController::class, 'store'])
        ->name('unit.pr.store');
    Route::get('/purchase-requests/{id}', [UnitController::class, 'show'])
        ->name('unit.pr.show');
    Route::get('/purchase-requests/{id}/print', [UnitController::class, 'print'])
        ->name('unit.pr.print');
    Route::get('/purchase-requests/{id}/edit', [UnitController::class, 'edit'])
        ->name('unit.pr.edit');
    Route::post('/purchase-requests/{id}', [UnitController::class, 'update'])
        ->name('unit.pr.update');
    Route::post('/purchase-requests/{id}/submit', [UnitController::class, 'submit'])
        ->name('unit.pr.submit');
    Route::post('/purchase-requests/{id}/request-correction', [UnitController::class, 'requestCorrection'])
        ->name('unit.pr.requestCorrection');
    Route::post('/purchase-requests/{id}/confirm', [UnitController::class, 'confirm'])
        ->name('unit.pr.confirm');
    Route::get('/procurement-history', [UnitController::class, 'history'])
        ->name('unit.procurement-history');

    Route::get('/profile', [UnitController::class, 'profile'])
        ->name('unit.profile');

    Route::get('/notifications', [NotificationController::class, 'indexForCurrentUser'])
        ->name('unit.notifications');
});

/*
|--------------------------------------------------------------------------
| BAC - PR / Notices / Notifications / Profile
|--------------------------------------------------------------------------
*/

Route::middleware([NoCache::class, 'bac'])->prefix('bac')->group(function () {
    Route::get('/purchase-requests', [BacPurchaseRequestController::class, 'index'])
        ->name('bac.pr.index');
    Route::get('/upload-notice', [BacPurchaseRequestController::class, 'uploadNoticeIndex'])
        ->name('bac.uploadNotice.index');
    Route::get('/purchase-requests/{id}', [BacPurchaseRequestController::class, 'show'])
        ->name('bac.pr.show');
    Route::get('/purchase-requests/{id}/print', [BacPurchaseRequestController::class, 'print'])
        ->name('bac.pr.print');
    Route::post('/purchase-requests/{id}/upload-notice', [BacPurchaseRequestController::class, 'uploadNotice'])
        ->name('bac.pr.uploadNotice');

    Route::get('/consolidation', [BacProcurementFlowController::class, 'consolidationIndex'])
        ->name('bac.consolidation.index');
    Route::post('/consolidation/generate', [BacProcurementFlowController::class, 'generateConsolidation'])
        ->name('bac.consolidation.generate');

    Route::get('/biddings', [BacProcurementFlowController::class, 'biddingIndex'])
        ->name('bac.biddings.index');
    Route::post('/biddings', [BacProcurementFlowController::class, 'storeBid'])
        ->name('bac.biddings.store');
    Route::post('/biddings/{id}/award', [BacProcurementFlowController::class, 'award'])
        ->name('bac.biddings.award');

    Route::get('/deliveries', [BacProcurementFlowController::class, 'deliveriesIndex'])
        ->name('bac.deliveries.index');
    Route::post('/deliveries', [BacProcurementFlowController::class, 'storeDelivery'])
        ->name('bac.deliveries.store');
    Route::get('/inventory', [BacProcurementFlowController::class, 'inventoryIndex'])
        ->name('bac.inventory.index');

    Route::get('/profile', [BacPurchaseRequestController::class, 'profile'])
        ->name('bac.profile');
    Route::get('/notifications', [NotificationController::class, 'indexForCurrentUser'])
        ->name('bac.notifications');
});
