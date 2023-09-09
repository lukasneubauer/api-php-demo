<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\NoDataFoundForPropertyEmailInRequestBody;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class NoDataFoundForPropertyEmailInRequestBodyTest extends TestCase
{
    public function testCheckIfAnyDataForPropertyEmailWereFoundDoesNotThrowException(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn(m::mock(User::class))
                ->getMock();
            $validator = new NoDataFoundForPropertyEmailInRequestBody($userRepository);
            $validator->checkIfAnyDataForPropertyEmailWereFound(['email' => 'john.doe@example.com']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfAnyDataForPropertyEmailWereFoundThrowsException(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new NoDataFoundForPropertyEmailInRequestBody($userRepository);
            $validator->checkIfAnyDataForPropertyEmailWereFound(['email' => 'john.doe@example.com']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(13, $data['error']['code']);
            $this->assertSame("No data found for 'email' in request body.", $data['error']['message']);
            $this->assertSame("No data found for 'email' in request body.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
