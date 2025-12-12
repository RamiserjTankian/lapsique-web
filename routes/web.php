<?php

use App\Http\Controllers\DjController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestListController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/djs', [DjController::class, 'index'])->name('djs.index');
Route::get('/djs/{dj:slug}', [DjController::class, 'show'])->name('djs.show');

Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
Route::get('/eventos/{event:slug}', [EventController::class, 'show'])->name('events.show');

Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video:slug}', [VideoController::class, 'show'])->name('videos.show');

Route::post('/guest-list', [GuestListController::class, 'store'])->name('guestlist.store');

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['es', 'en'])) {
        abort(404);
    }

    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');
