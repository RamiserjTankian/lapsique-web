<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEmailOpenJob;
use App\Jobs\ProcessEmailClickJob;
use App\Services\MailtrapEventsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class EmailTrackingController extends Controller
{
    /**
     * Track email opens
     */
    public function trackOpen(string $token)
    {
        // Dispatch job en background para no bloquear la respuesta
        ProcessEmailOpenJob::dispatch(
            $token,
            request()->ip(),
            request()->userAgent()
        );

        // Devolver un pixel transparente 1x1
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel)
            ->header('Content-Type', 'image/gif')
            ->header('Content-Length', strlen($pixel))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Track email clicks
     */
    public function trackClick(string $token, Request $request)
    {
        $url = $request->query('url');
        
        if (!$url) {
            abort(400, 'URL parameter is required');
        }

        // Dispatch job en background
        ProcessEmailClickJob::dispatch(
            $token,
            $url,
            $request->ip(),
            $request->userAgent()
        );

        // Redirigir al URL original
        return redirect($url);
    }

    /**
     * Webhook para eventos de Mailtrap
     */
    public function mailtrapWebhook(Request $request)
    {
        Log::info('Mailtrap webhook received', $request->all());

        $events = $request->input('events', []);

        app(MailtrapEventsService::class)->processEvents($events);

        return response()->json(['success' => true]);
    }
}
