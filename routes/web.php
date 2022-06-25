<?php

use App\Http\Controllers\CrawlerJobController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/queue-crawler', [CrawlerJobController::class, 'initCrawler']);
Route::get('/results/{id}', [CrawlerJobController::class, 'showResult'])->name('results');
