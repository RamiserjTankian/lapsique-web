<?php

namespace App\Support;

use Illuminate\Http\Request;

class BookingMode
{
    public static function shouldSkipPayment(Request $request): bool
    {
        $host = strtolower($request->getHost());
        $allowedHosts = array_map('strtolower', config('booking.skip_payment_hosts', []));

        if (in_array($host, $allowedHosts, true)) {
            return true;
        }

        foreach (config('booking.skip_payment_host_suffixes', []) as $suffix) {
            $suffix = strtolower((string) $suffix);

            if ($suffix !== '' && str_ends_with($host, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
