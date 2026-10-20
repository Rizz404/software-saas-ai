<?php

use App\Http\Controllers\Admin\ConsultantController;
use App\Http\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\KnowledgeItemController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\SolutionConsultantChatController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CompanyPageController::class, 'index'])->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');
        Route::resource('consultants', ConsultantController::class)->except('show');
        Route::resource('knowledge-items', KnowledgeItemController::class)->except('show');

        Route::get('consultations', [AdminConsultationController::class, 'index'])->name('consultations.index');
        Route::patch('consultations/{consultation}/status', [AdminConsultationController::class, 'updateStatus'])->name('consultations.status');

        Route::get('handovers', [HandoverController::class, 'index'])->name('handovers.index');
        Route::get('handovers/{handover}', [HandoverController::class, 'show'])->name('handovers.show');
        Route::post('handovers/{handover}/reply', [HandoverController::class, 'reply'])->name('handovers.reply');
        Route::post('handovers/{handover}/resolve', [HandoverController::class, 'resolve'])->name('handovers.resolve');
    });
});

Route::prefix('{companySlug}')->middleware('company')->group(function () {
    Route::get('/', [CompanyPageController::class, 'show'])->name('company.show');
    Route::get('products', [CompanyPageController::class, 'products'])->name('company.products');
    Route::get('products/{itemSlug}', [CompanyPageController::class, 'product'])->name('company.product');
    Route::get('consultants', [CompanyPageController::class, 'consultants'])->name('company.consultants');
    Route::get('consultants/{areaSlug}', [CompanyPageController::class, 'consultant'])->name('company.consultant');
    Route::get('facilities', [CompanyPageController::class, 'facilities'])->name('company.facilities');
    Route::get('facilities/{facilityId}', [CompanyPageController::class, 'facility'])->whereNumber('facilityId')->name('company.facility');
    Route::get('info', [CompanyPageController::class, 'info'])->name('company.info');
    Route::get('staff', [CompanyPageController::class, 'staff'])->name('company.staff');
    Route::get('consultation', [CompanyPageController::class, 'consultationScene'])->name('company.consultation');

    Route::prefix('consultation')->name('consultation.')->middleware('throttle:20,1')->group(function () {
        Route::post('quote', [ConsultationController::class, 'quote'])->name('quote');
        Route::post('/', [ConsultationController::class, 'store'])->name('store');
    });

    Route::prefix('solution_consultant')->name('solution_consultant.')->group(function () {
        Route::post('start', [SolutionConsultantChatController::class, 'start'])->middleware('throttle:solution_consultant-start')->name('start');
        Route::post('message', [SolutionConsultantChatController::class, 'message'])->middleware('throttle:solution_consultant-message')->name('message');
        Route::get('history', [SolutionConsultantChatController::class, 'history'])->middleware('throttle:solution_consultant-history')->name('history');
        Route::post('speech', [SolutionConsultantChatController::class, 'speech'])->middleware('throttle:solution_consultant-speech')->name('speech');
    });
});
