<?php

use App\Http\Controllers\DjController;
use App\Http\Controllers\EmailTrackingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestListCheckInController;
use App\Http\Controllers\GuestListController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadCaptureController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/djs', [DjController::class, 'index'])->name('djs.index');
Route::get('/djs/{dj:slug}', [DjController::class, 'show'])->name('djs.show');

Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
Route::get('/eventos/{event:slug}', [EventController::class, 'show'])->name('events.show');

Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video:slug}', [VideoController::class, 'show'])->name('videos.show');

Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/blog/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::post('/guest-list', [GuestListController::class, 'store'])->name('guestlist.store');

// Guest List Invites (por DJ) - Legacy (comentado temporalmente)
// Route::get('/invite/{token}', [GuestListInviteController::class, 'show'])->name('guestlist.invite.show');
// Route::post('/invite/{token}/confirm', [GuestListInviteController::class, 'confirm'])->name('guestlist.invite.confirm');

// Guest List Registration (público desde links)
Route::get('/register/{token}', [App\Http\Controllers\GuestListRegisterController::class, 'show'])->name('guestlist.register');
Route::post('/register/{token}', [App\Http\Controllers\GuestListRegisterController::class, 'store'])->name('guestlist.register.store');
Route::get('/register/{token}/success', [App\Http\Controllers\GuestListRegisterController::class, 'success'])->name('guestlist.register.success');
Route::get('/register/{token}/thank-you', [App\Http\Controllers\GuestListRegisterController::class, 'thankyou'])->name('guestlist.register.thankyou');

Route::get('/check-in/{token}', [GuestListCheckInController::class, 'show'])
    ->middleware('signed')
    ->name('guestlist.checkin.show');
Route::post('/check-in/{token}', [GuestListCheckInController::class, 'confirm'])
    ->middleware('signed')
    ->name('guestlist.checkin.confirm');
Route::get('/check-in/{token}/qr', [GuestListCheckInController::class, 'qr'])
    ->name('guestlist.checkin.qr');
Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::get('/mi-portal', [CustomerController::class, 'portal'])->name('customers.portal');

// Lead Capture (Popup)
Route::post('/api/leads', [LeadCaptureController::class, 'capture'])->name('leads.capture');

// Email Tracking
Route::get('/track/email/{token}/open', [EmailTrackingController::class, 'trackOpen'])->name('email.track.open');
Route::get('/track/email/{token}/click', [EmailTrackingController::class, 'trackClick'])->name('email.track.click');

// Webhooks
Route::post('/webhooks/mailtrap/events', [EmailTrackingController::class, 'mailtrapWebhook'])->name('webhooks.mailtrap');
Route::post('/webhooks/twilio/sms/status', function () { /* TODO */ })->name('webhooks.twilio.sms');
Route::post('/webhooks/twilio/whatsapp/status', function () { /* TODO */ })->name('webhooks.twilio.whatsapp');
Route::post('/webhooks/twilio/voice/status', function () { /* TODO */ })->name('webhooks.twilio.voice');

// Unsubscribe
Route::get('/unsubscribe', [LeadCaptureController::class, 'unsubscribe'])->name('customer.unsubscribe');
Route::post('/unsubscribe', [LeadCaptureController::class, 'unsubscribe']);

Route::get('/locale/{locale}', function (string $locale) {
    if (! in_array($locale, ['es', 'en'])) {
        abort(404);
    }

    session(['locale' => $locale]);

    return redirect()->back();
})->name('locale.switch');

Route::post('/theme', function () {
    $theme = request()->input('theme', 'dark');
    
    if (! in_array($theme, ['dark', 'light'])) {
        return response()->json(['success' => false, 'message' => 'Invalid theme'], 400);
    }

    if (auth()->check()) {
        auth()->user()->update(['theme' => $theme]);
    }

    return response()->json(['success' => true, 'theme' => $theme]);
})->name('theme.update');
