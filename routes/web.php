<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BibliotecasController;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/bibliotecas", [BibliotecasController::class, 'index'])->name("bibliotecas.index");
Route::get("/bibliotecas/new", [BibliotecasController::class, 'create'])->name("bibliotecas.create");
Route::post("/bibliotecas/create", [BibliotecasController::class, 'store'])->name("bibliotecas.store");
Route::get("/bibliotecas/edit/{id}", [BibliotecasController::class, 'edit'])->name("bibliotecas.edit");
Route::put("/bibliotecas/update/{id}", [BibliotecasController::class, 'update'])->name("bibliotecas.update");
Route::delete("/bibliotecas/delete/{id}", [BibliotecasController::class, 'destroy'])->name("bibliotecas.destroy");

