<?php

namespace App\Http\Controllers;

use App\Models\GuestListEntry;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class GuestListCheckInController extends Controller
{
    public function show(Request $request, string $token)
    {
        $entry = GuestListEntry::with(['event', 'customer', 'dj', 'rp', 'inviteLink'])
            ->where('invite_token', $token)
            ->firstOrFail();

        return view('guest-list.check-in', [
            'entry' => $entry,
            'event' => $entry->event,
            'customer' => $entry->customer,
            'checkInConfirmUrl' => $entry->getCheckInConfirmUrl(),
            'checkInQrUrl' => $entry->getCheckInQrUrl(),
            'checkInCode' => $entry->getCheckInCode(),
        ]);
    }

    public function confirm(Request $request, string $token)
    {
        $entry = GuestListEntry::with(['event', 'customer', 'dj', 'rp', 'inviteLink'])
            ->where('invite_token', $token)
            ->firstOrFail();

        if (! $entry->canCheckIn()) {
            return redirect($entry->getCheckInUrl())
                ->with('info', 'Este acceso ya agotó sus consumos permitidos.');
        }

        $entry->checkIn();

        return redirect($entry->getCheckInUrl())
            ->with('success', 'Check-in confirmado correctamente.');
    }

    public function qr(string $token)
    {
        $entry = GuestListEntry::where('invite_token', $token)->firstOrFail();

        $entry->ensureInviteToken();
        $payload = $entry->invite_token;

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
