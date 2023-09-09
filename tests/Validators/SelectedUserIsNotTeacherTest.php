<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;
use App\Validators\SelectedUserIsNotTeacher;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class SelectedUserIsNotTeacherTest extends TestCase
{
    public function testCheckIfSelectedUserIsTeacherDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new SelectedUserIsNotTeacher($userRepository);
            $validator->checkIfSelectedUserIsTeacher(['teacherId' => 'b17f7098-d1a0-494d-a5dc-bba9cf418d2b']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfSelectedUserIsTeacherThrowsException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $userRepository = m::mock(UserRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $validator = new SelectedUserIsNotTeacher($userRepository);
            $validator->checkIfSelectedUserIsTeacher(['teacherId' => 'b17f7098-d1a0-494d-a5dc-bba9cf418d2b']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(22, $data['error']['code']);
            $this->assertSame('Selected user is not teacher.', $data['error']['message']);
            $this->assertSame('Selected user is not teacher.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
