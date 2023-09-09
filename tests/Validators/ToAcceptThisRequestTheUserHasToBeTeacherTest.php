<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Session;
use App\Entities\User;
use App\Exceptions\ValidationException;
use App\Repositories\SessionRepository;
use App\Validators\ToAcceptThisRequestTheUserHasToBeTeacher;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;

final class ToAcceptThisRequestTheUserHasToBeTeacherTest extends TestCase
{
    public function testCheckIfTheUserIsTeacherToAcceptThisRequestDoesNotThrowException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $session = m::mock(Session::class)
                ->shouldReceive('getUser')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new ToAcceptThisRequestTheUserHasToBeTeacher($sessionRepository);
            $validator->checkIfTheUserIsTeacherToAcceptThisRequest(new HeaderBag(['Api-Token' => '1234567890']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfTheUserIsTeacherToAcceptThisRequestThrowsException(): void
    {
        try {
            $user = m::mock(User::class)
                ->shouldReceive('isTeacher')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $session = m::mock(Session::class)
                ->shouldReceive('getUser')
                ->times(1)
                ->andReturn($user)
                ->getMock();
            $sessionRepository = m::mock(SessionRepository::class)
                ->shouldReceive('getByApiToken')
                ->times(1)
                ->andReturn($session)
                ->getMock();
            $validator = new ToAcceptThisRequestTheUserHasToBeTeacher($sessionRepository);
            $validator->checkIfTheUserIsTeacherToAcceptThisRequest(new HeaderBag(['Api-Token' => '1234567890']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(29, $data['error']['code']);
            $this->assertSame('To accept this request the user has to be teacher.', $data['error']['message']);
            $this->assertSame('To accept this request the user has to be teacher.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
