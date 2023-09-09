<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\NoDataFoundInUsersForPropertyUserIdInRequestBody;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class NoDataFoundInUsersForPropertyUserIdInRequestBodyTest extends TestCase
{
    public function testCheckIfAnyDataWereFoundInUsersForPropertyUserIdDoesNotThrowException(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(m::mock(User::class))
                ->getMock();
            $validator = new NoDataFoundInUsersForPropertyUserIdInRequestBody($userRepository);
            $validator->checkIfAnyDataWereFoundInUsersForPropertyUserId(['userId' => 'b17f7098-d1a0-494d-a5dc-bba9cf418d2b']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfAnyDataWereFoundInUsersForPropertyUserIdThrowsException(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new NoDataFoundInUsersForPropertyUserIdInRequestBody($userRepository);
            $validator->checkIfAnyDataWereFoundInUsersForPropertyUserId(['userId' => 'b17f7098-d1a0-494d-a5dc-bba9cf418d2b']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(13, $data['error']['code']);
            $this->assertSame("No data found for 'userId' in request body.", $data['error']['message']);
            $this->assertSame("No data found for 'userId' in request body.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
