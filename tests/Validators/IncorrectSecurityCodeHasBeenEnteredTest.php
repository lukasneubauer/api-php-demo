<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\SecurityCode;
use App\Entities\User;
use App\Exceptions\SecurityCodeConfirmationFailureException;
use App\Repositories\UserRepository;
use App\Validators\IncorrectSecurityCodeHasBeenEntered;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class IncorrectSecurityCodeHasBeenEnteredTest extends TestCase
{
    public function testCheckIfIncorrectSecurityCodeHasBeenEnteredDoesNotThrowException(): void
    {
        try {
            $securityCode = m::mock(SecurityCode::class)
                ->shouldReceive('getCode')
                ->times(1)
                ->andReturn('123456789')
                ->getMock();
            $user = m::mock(User::class)
                ->shouldReceive('getSecurityCode')
                ->times(1)
                ->andReturn($securityCode)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new IncorrectSecurityCodeHasBeenEntered($userRepository);
            $validator->checkIfIncorrectSecurityCodeHasBeenEntered(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '123456789',
                ]
            );
            $this->assertTrue(true);
        } catch (SecurityCodeConfirmationFailureException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getParameters
     */
    public function testCheckIfIncorrectSecurityCodeHasBeenEnteredThrowsException(int $inputFailures, int $remainingAttempts): void
    {
        try {
            $securityCode = m::mock(SecurityCode::class);
            $securityCode->shouldReceive('getCode')
                ->times(1)
                ->andReturn('123456789');
            $securityCode->shouldReceive('getInputFailures')
                ->times(1)
                ->andReturn($inputFailures);
            $user = m::mock(User::class)
                ->shouldReceive('getSecurityCode')
                ->times(1)
                ->andReturn($securityCode)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new IncorrectSecurityCodeHasBeenEntered($userRepository);
            $validator->checkIfIncorrectSecurityCodeHasBeenEntered(
                [
                    'email' => 'john.doe@example.com',
                    'securityCode' => '000000000',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (SecurityCodeConfirmationFailureException $e) {
            $data = $e->getData();
            $this->assertSame(43, $data['error']['code']);
            $this->assertSame(\sprintf('Incorrect security code has been entered. %s attempt(s) left.', $remainingAttempts), $data['error']['message']);
            $this->assertSame(\sprintf('Incorrect security code has been entered. %s attempt(s) left.', $remainingAttempts), $e->getMessage());
        }
    }

    public static function getParameters(): array
    {
        return [
            [
                0,
                2,
            ],
            [
                1,
                1,
            ],
        ];
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
