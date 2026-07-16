<?php

use App\Http\Controllers\Api\JournalTemplateApiController;
use Illuminate\Support\Facades\Route;

// API untuk aplikasi eksternal — autentikasi via header X-API-Key
Route::middleware('api.key')->group(function () {
    Route::get('journal-templates', [JournalTemplateApiController::class, 'index']);
    Route::get('journal-templates/{journalTemplate}', [JournalTemplateApiController::class, 'show']);
});
