<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\JWT;

use HeyFrame\Core\Framework\JWT\JWTDecoder;
use HeyFrame\Core\Framework\JWT\JWTException;
use HeyFrame\Core\Framework\Log\Package;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
#[CoversClass(JWTDecoder::class)]
class JWTDecoderTest extends TestCase
{
    private JWTDecoder $decoder;

    protected function setUp(): void
    {
        $this->decoder = new JWTDecoder();
    }

    public function testDecodeWithValidToken(): void
    {
        $claims = $this->decoder->decode($this->getJwt());
        static::assertSame([
            ['identifier' => 'Purchase1', 'nextBookingDate' => '2099-12-13 11:44:31', 'quantity' => 1, 'sub' => 'example.com'],
            ['identifier' => 'Purchase2', 'nextBookingDate' => '2099-12-13 11:44:31', 'quantity' => 1, 'sub' => 'example.com'],
        ], $claims);
    }

    public function testDecodeWithInvalidTokenThrowsException(): void
    {
        $this->expectException(JWTException::class);
        $this->expectExceptionMessage('Invalid JWT: Error while decoding from Base64Url, invalid base64 characters detected');
        $this->decoder->decode('invalid.jwt.token');
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function provideInvalidJwts(): array
    {
        $jwts = \file_get_contents(__DIR__ . '/_fixtures/invalid-jwts.json');
        static::assertIsString($jwts);

        return \json_decode($jwts, true, 512, \JSON_THROW_ON_ERROR);
    }

    private function getJwt(): string
    {
        $jwt = \file_get_contents(__DIR__ . '/_fixtures/valid-jwt.txt');
        static::assertIsString($jwt);
        $jwt = \trim($jwt);

        return $jwt;
    }
}
