<?php
use App\Http\Controllers\WalletController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/deposit',[WalletController::class,'deposit']);
    Route::post('/withdraw',[WalletController::class,'withdraw']);
});