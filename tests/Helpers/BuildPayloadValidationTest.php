<?php


declare(strict_types=1);

namespace FppTest\Helpers;

use Fpp\Argument;
use Fpp\Constructor;
use Fpp\Definition;
use Fpp\DefinitionCollection;
use Fpp\Deriving;
use PHPUnit\Framework\TestCase;
use function Fpp\buildPayloadValidation;

class BuildPayloadValidationTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_payload_validation_without_first_argument(): void
    {
        $userId = new Definition(
            'My',
            'UserId',
            [
                new Constructor('UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $email = new Definition(
            'Some',
            'Email',
            [
                new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $constructor = new Constructor('UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
if (isset(\$payload['name']) && ! is_string(\$payload['name'])) {
                throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
            }

            if (! isset(\$payload['email']) || ! is_string(\$payload['email'])) {
                throw new \InvalidArgumentException("Key 'email' is missing in payload or is not a string");
            }

            if (! isset(\$payload['something'])) {
                throw new \InvalidArgumentException("Key 'something' is missing in payload");
            }

CODE;

        $this->assertSame($expected, buildPayloadValidation($constructor, $collection, false));
    }

    /**
     * @test
     */
    public function it_builds_payload_validation_with_first_argument(): void
    {
        $userId = new Definition(
            'My',
            'UserId',
            [
                new Constructor('UserId'),
            ],
            [
                new Deriving\Uuid(),
            ]
        );

        $email = new Definition(
            'Some',
            'Email',
            [
                new Constructor('String'),
            ],
            [
                new Deriving\FromString(),
                new Deriving\ToString(),
            ]
        );

        $constructor = new Constructor('UserRegistered', [
            new Argument('id', 'My\UserId'),
            new Argument('name', 'string', true),
            new Argument('email', 'Some\Email'),
            new Argument('something', 'Something\Unknown'),
        ]);

        $definition = new Definition(
            'My',
            'UserRegistered',
            [$constructor]
        );

        $collection = new DefinitionCollection($userId, $email, $definition);

        $expected = <<<CODE
if (! isset(\$payload['id']) || ! is_string(\$payload['id'])) {
                throw new \InvalidArgumentException("Key 'id' is missing in payload or is not a string");
            }

            if (isset(\$payload['name']) && ! is_string(\$payload['name'])) {
                throw new \InvalidArgumentException("Value for 'name' is not a string in payload");
            }

            if (! isset(\$payload['email']) || ! is_string(\$payload['email'])) {
                throw new \InvalidArgumentException("Key 'email' is missing in payload or is not a string");
            }

            if (! isset(\$payload['something'])) {
                throw new \InvalidArgumentException("Key 'something' is missing in payload");
            }

CODE;

        $this->assertSame($expected, buildPayloadValidation($constructor, $collection, true));
    }
}
