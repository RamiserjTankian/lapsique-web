<?php

namespace App\Http\Controllers;

use App\Models\TicketAttendee;
use App\Models\TicketScan;
use App\Services\TicketPassPdfService;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class TicketCheckInController extends Controller
{
    public function show(Request $request, string $token)
    {
        $attendee = TicketAttendee::with(['event', 'customer', 'product', 'order'])
            ->where('invite_token', $token)
            ->firstOrFail();

        return view('tickets.check-in', [
            'attendee' => $attendee,
            'event' => $attendee->event,
            'checkInConfirmUrl' => $attendee->getCheckInConfirmUrl(),
            'checkInQrUrl' => $attendee->getCheckInQrUrl(),
            'checkInCode' => $attendee->getCheckInCode(),
        ]);
    }

    public function confirm(Request $request, string $token)
    {
        $attendee = TicketAttendee::with(['event', 'customer', 'product', 'order'])
            ->where('invite_token', $token)
            ->firstOrFail();

        if (! $attendee->canCheckIn()) {
            return redirect($attendee->getCheckInUrl())
                ->with('info', 'Este pase ya agotó sus consumos permitidos.');
        }

        $attendee->checkIn();

        TicketScan::create([
            'ticket_attendee_id' => $attendee->id,
            'user_id' => auth()->id(),
            'scan_status' => 'checked_in',
            'scanned_at' => now(),
        ]);

        return redirect($attendee->getCheckInUrl())
            ->with('success', 'Check-in confirmado correctamente.');
    }

    public function pdf(string $token)
    {
        $attendee = TicketAttendee::with([
            'event.djs',
            'event.location',
            'product',
            'order.items.attendees',
        ])->where('invite_token', $token)->firstOrFail();

        $pdfService = app(TicketPassPdfService::class);
        $pdf = $pdfService->buildForAttendee($attendee);

        return $pdf->download($pdfService->filenameForEvent($attendee->event));
    }

    public function qr(string $token)
    {
        $attendee = TicketAttendee::where('invite_token', $token)->firstOrFail();

        $attendee->ensureInviteToken();
        $payload = $attendee->invite_token;

        $qrCode = QrCode::create($payload)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(320)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(18, 18, 18))
            ->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
