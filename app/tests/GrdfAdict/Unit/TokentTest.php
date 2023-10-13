<?php

declare(strict_types=1);

namespace App\GrdfAdict\Tests\Unit;

use App\GrdfAdict\Model\Token;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    public function testHydratation()
    {
        $data = <<<JSON
        {
            "access_token": "WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5",
            "token_type": "Bearer",
            "expires_in": 12600,
            "scope": "/adict/v1",
            "id_token": "12546852467895"
        }
        JSON;

        $token = Token::fromJson($data);

        self::assertInstanceOf(Token::class, $token);
        self::assertSame("WeOAFUQA7KjyvWRujg6pqCNshq6pxJaC497Ubz3bku12lF4SW5Dws5", $token->accessToken);
        self::assertTrue($token->isAccessTokenStillValid());
        self::assertEquals("Bearer", $token->tokenType);
        self::assertEquals("/adict/v1", $token->scope);
    }
}
