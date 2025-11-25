<?php

use App\Http\Controllers\FizzBuzzController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FizzBuzzController::class, 'fizzbuzz'])->name('fizzbuzz');
Route::get('/fizzbuzz', [FizzBuzzController::class, 'fizzbuzz'])->name('fizzbuzz');
Route::get('/fibonacci', [FizzBuzzController::class, 'fibonacci'])->name('fibonacci');
Route::get('/combine', [FizzBuzzController::class, 'combine'])->name('combine');
