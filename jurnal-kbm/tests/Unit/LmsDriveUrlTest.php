<?php

namespace Tests\Unit;

use App\Support\LmsDriveUrl;
use PHPUnit\Framework\TestCase;

class LmsDriveUrlTest extends TestCase
{
    public function test_accepts_https_google_drive(): void
    {
        $this->assertTrue(LmsDriveUrl::isValid('https://drive.google.com/file/d/abc/view'));
    }

    public function test_rejects_http_and_unknown_host(): void
    {
        $this->assertFalse(LmsDriveUrl::isValid('http://drive.google.com/x'));
        $this->assertFalse(LmsDriveUrl::isValid('https://evil.example.com/x'));
    }
}
