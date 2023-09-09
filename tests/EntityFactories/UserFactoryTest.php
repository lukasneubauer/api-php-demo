<?php

declare(strict_types=1);

namespace Tests\App\EntityFactories;

use App\DateTime\DateTimeUTC;
use App\Entities\Password;
use App\Entities\Token;
use App\EntityFactories\TokenFactory;
use App\EntityFactories\UserFactory;
use App\Generators\TokenGenerator;
use App\Generators\UuidGenerator;
use App\Passwords\PasswordAlgorithms;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class UserFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = m::mock(UuidGenerator::class)
            ->shouldReceive('generateUuid')
            ->times(1)
            ->andReturn('24887dd6-df68-4938-a4a1-49c6401a0389')
            ->getMock();

        /** @var TokenGenerator $tokenGenerator */
        $tokenGenerator = m::mock(TokenGenerator::class)
            ->shouldReceive('generateToken')
            ->times(1)
            ->andReturn('014boffm55rr2goahorn')
            ->getMock();

        $userFactory = new UserFactory($uuidGenerator, new TokenFactory($tokenGenerator));

        $now = (new DateTimeUTC())->createDateTimeInstance();

        $user = $userFactory->create(
            'John',
            'Doe',
            'BASE64-STRING',
            'john.doe@example.com',
            new Password('$2y$13$Y9rdI88aSRnmbjZCwDJqSui/RGvzJYFGezxXVgI/tsaGJCk8GYmaG', PasswordAlgorithms::BCRYPT),
            'Europe/Prague',
            $now
        );

        $this->assertSame('24887dd6-df68-4938-a4a1-49c6401a0389', $user->getId());
        $this->assertSame('John', $user->getFirstName());
        $this->assertSame('Doe', $user->getLastName());
        $this->assertSame('BASE64-STRING', $user->getAvatar());
        $this->assertSame('john.doe@example.com', $user->getEmail());
        $this->assertInstanceOf(Password::class, $user->getPassword());
        $this->assertSame('$2y$13$Y9rdI88aSRnmbjZCwDJqSui/RGvzJYFGezxXVgI/tsaGJCk8GYmaG', $user->getPassword()->getHash());
        $this->assertSame(PasswordAlgorithms::BCRYPT, $user->getPassword()->getAlgorithm());
        $this->assertFalse($user->isTeacher());
        $this->assertFalse($user->isStudent());
        $this->assertSame('Europe/Prague', $user->getTimezone());
        $this->assertInstanceOf(Token::class, $user->getToken());
        $this->assertSame('014boffm55rr2goahorn', $user->getToken()->getCode());
        $this->assertInstanceOf(DateTime::class, $user->getToken()->getCreatedAt());
        $this->assertNull($user->getSecurityCode());
        $this->assertSame(0, $user->getAuthenticationFailures());
        $this->assertFalse($user->isLocked());
        $this->assertFalse($user->isActive());
        $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $user->getUpdatedAt());
        $this->assertSame($user->getCreatedAt()->getTimestamp(), $user->getToken()->getCreatedAt()->getTimestamp());
        $this->assertSame($user->getCreatedAt()->getTimestamp(), $user->getUpdatedAt()->getTimestamp());
        $this->assertInstanceOf(Collection::class, $user->getTeacherCourses());
        $this->assertCount(0, $user->getTeacherCourses());
        $this->assertInstanceOf(Collection::class, $user->getStudentCourses());
        $this->assertCount(0, $user->getStudentCourses());
        $this->assertInstanceOf(Collection::class, $user->getSessions());
        $this->assertCount(0, $user->getSessions());
        $this->assertInstanceOf(Collection::class, $user->getPayments());
        $this->assertCount(0, $user->getPayments());
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
