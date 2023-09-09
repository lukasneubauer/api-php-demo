<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\NoDataFoundForEmailUrlParameter;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class NoDataFoundForEmailUrlParameterTest extends TestCase
{
    public function testCheckIfAnyDataForUrlParameterEmailWereFoundDoesNotThrowException(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn(m::mock(User::class))
                ->getMock();
            $validator = new NoDataFoundForEmailUrlParameter($userRepository);
            $validator->checkIfAnyDataForUrlParameterEmailWereFound(new ParameterBag(['email' => 'john.doe@example.com']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfAnyDataForUrlParameterEmailWereFoundThrowsException(): void
    {
        try {
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getByEmail')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new NoDataFoundForEmailUrlParameter($userRepository);
            $validator->checkIfAnyDataForUrlParameterEmailWereFound(new ParameterBag(['email' => 'john.doe@example.com']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(7, $data['error']['code']);
            $this->assertSame("No data found for 'email' url parameter.", $data['error']['message']);
            $this->assertSame("No data found for 'email' url parameter.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
