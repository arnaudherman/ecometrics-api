<?php

namespace Tests\Unit;

use App\Models\CarbonCertificate;
use Tests\TestCase;

class CarbonCertificateTest extends TestCase
{
    /**
     * Test platinum badge determination.
     */
    public function test_determines_platinum_badge(): void
    {
        $badge = CarbonCertificate::determineBadgeLevel(5);
        $this->assertEquals('platinum', $badge);

        $badge = CarbonCertificate::determineBadgeLevel(10);
        $this->assertEquals('platinum', $badge);
    }

    /**
     * Test gold badge determination.
     */
    public function test_determines_gold_badge(): void
    {
        $badge = CarbonCertificate::determineBadgeLevel(15);
        $this->assertEquals('gold', $badge);

        $badge = CarbonCertificate::determineBadgeLevel(20);
        $this->assertEquals('gold', $badge);
    }

    /**
     * Test silver badge determination.
     */
    public function test_determines_silver_badge(): void
    {
        $badge = CarbonCertificate::determineBadgeLevel(30);
        $this->assertEquals('silver', $badge);

        $badge = CarbonCertificate::determineBadgeLevel(50);
        $this->assertEquals('silver', $badge);
    }

    /**
     * Test bronze badge determination.
     */
    public function test_determines_bronze_badge(): void
    {
        $badge = CarbonCertificate::determineBadgeLevel(75);
        $this->assertEquals('bronze', $badge);

        $badge = CarbonCertificate::determineBadgeLevel(100);
        $this->assertEquals('bronze', $badge);

        $badge = CarbonCertificate::determineBadgeLevel(200);
        $this->assertEquals('bronze', $badge);
    }
}