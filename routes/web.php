<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LandInsightController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::redirect('/home', '/');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->middleware('permission:view_dashboard')
        ->name('dashboard');

    Route::get('land-insights', [LandInsightController::class, 'index'])
        ->middleware('permission:view_dashboard')
        ->name('land-insights.index');

    Route::view('gis', 'gis.index')
        ->middleware('permission:view_gis')
        ->name('gis.index');

    Route::get('tools/calculator', [ToolsController::class, 'calculator'])
        ->middleware('permission:view_dashboard')
        ->name('tools.calculator');



    Route::get('surveys', [SurveyController::class, 'index'])
        ->middleware('permission:collect_survey_data')
        ->name('surveys.index');

    Route::get('surveys/create', [SurveyController::class, 'create'])
        ->middleware('permission:manage_surveys')
        ->name('surveys.create');

    Route::post('surveys', [SurveyController::class, 'store'])
        ->middleware('permission:manage_surveys')
        ->name('surveys.store');

    Route::get('surveys/{survey}/collect', [SurveyController::class, 'collect'])
        ->middleware('permission:collect_survey_data')
        ->name('surveys.collect');

    Route::post('surveys/{survey}/collect', [SurveyController::class, 'submit'])
        ->middleware('permission:collect_survey_data')
        ->name('surveys.submit');

    Route::get('surveys/{survey}/responses', [SurveyController::class, 'responses'])
        ->middleware('permission:collect_survey_data')
        ->name('surveys.responses');

    Route::delete('surveys/{survey}', [SurveyController::class, 'destroy'])
        ->middleware('permission:manage_surveys')
        ->name('surveys.destroy');

    Route::delete('survey-responses/{response}', [SurveyController::class, 'destroyResponse'])
        ->middleware('permission:manage_surveys')
        ->name('surveys.destroyResponse');

    Route::get('admin', [AdminController::class, 'index'])
        ->middleware('permission:manage_users')
        ->name('admin.index');

    Route::view('profile', 'profile')
        ->name('profile');
});

require __DIR__.'/auth.php';
