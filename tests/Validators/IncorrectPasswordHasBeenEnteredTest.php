<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Password;
use App\Entities\User;
use App\Exceptions\AuthenticationFailureException;
use App\Passwords\PasswordAlgorithms;
use App\Passwords\PasswordCheck;
use App\Repositories\UserRepository;
use App\Validators\IncorrectPasswordHasBeenEntered;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class IncorrectPasswordHasBeenEnteredTest extends TestCase
{
    public function testCheckIfIncorrectPasswordHasBeenEnteredDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('$2y$13$/6/rFTIqOaeMV3wD4.gRN.p9aPUNY6RUdZUjTCX5WftlfSEFzJqTi', PasswordAlgorithms::BCRYPT))
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $passwordCheck = m::mock(PasswordCheck::class)
                ->shouldReceive('isPasswordCorrect')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $validator = new IncorrectPasswordHasBeenEntered($userRepository, $passwordCheck);
            $validator->checkIfIncorrectPasswordHasBeenEntered(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->assertTrue(true);
        } catch (AuthenticationFailureException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getParameters
     */
    public function testCheckIfIncorrectPasswordHasBeenEnteredThrowsException(int $authenticationFailures, int $remainingAttempts): void
    {
        try {
            $user = m::mock(User::class);
            $user->shouldReceive('getPassword')
                ->times(1)
                ->andReturn(new Password('incorrect', PasswordAlgorithms::BCRYPT));
            $user->shouldReceive('getAuthenticationFailures')
                ->times(1)
                ->andReturn($authenticationFailures);
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $passwordCheck = m::mock(PasswordCheck::class)
                ->shouldReceive('isPasswordCorrect')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $validator = new IncorrectPasswordHasBeenEntered($userRepository, $passwordCheck);
            $validator->checkIfIncorrectPasswordHasBeenEntered(
                [
                    'email' => 'john.doe@example.com',
                    'password' => 'secret',
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (AuthenticationFailureException $e) {
            $data = $e->getData();
            $this->assertSame(40, $data['error']['code']);
            $this->assertSame(\sprintf('Incorrect password has been entered. %s attempt(s) left.', $remainingAttempts), $data['error']['message']);
            $this->assertSame(\sprintf('Incorrect password has been entered. %s attempt(s) left.', $remainingAttempts), $e->getMessage());
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
