<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\TransactionController;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::apiResource("/clients", ClientController::class);
Route::apiResource("/comptes", CompteController::class);
Route::apiResource("/transaction", TransactionController::class);
Route::post("/depot",[TransactionController::class, "allTransact"]);
Route::get("/name/{numero}",[TransactionController::class, "name"]);
Route::get("/transact/{numero}",[TransactionController::class, "transact"]);
Route::post("/transfert",[TransactionController::class, "transfert"]);