<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PaymentTrustCopyTest extends TestCase
{
    public function test_payment_trust_copy_module_defines_stripe_and_dual_variants(): void
    {
        $path = dirname(__DIR__, 2).'/resources/js/lib/paymentTrustCopy.ts';

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertIsString($content);
        $this->assertStringContainsString("'stripe'", $content);
        $this->assertStringContainsString("'dual'", $content);
        $this->assertStringContainsString('Compra protegida', $content);
        $this->assertStringContainsString('reembolso del 100%', $content);
    }
}
