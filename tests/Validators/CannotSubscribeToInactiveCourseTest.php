<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use App\Validators\CannotSubscribeToInactiveCourse;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class CannotSubscribeToInactiveCourseTest extends TestCase
{
    public function testCheckIfCannotSubscribeToInactiveCourseDoesNotThrowException(): void
    {
        try {
            $course = m::mock(Course::class)
                ->shouldReceive('isActive')
                ->times(1)
                ->andReturn(true)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $validator = new CannotSubscribeToInactiveCourse($courseRepository);
            $validator->checkIfCannotSubscribeToInactiveCourse(['courseId' => '6fd21fb4-5787-4113-9e48-44ded2492608']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfCannotSubscribeToInactiveCourseThrowsException(): void
    {
        try {
            $course = m::mock(Course::class)
                ->shouldReceive('isActive')
                ->times(1)
                ->andReturn(false)
                ->getMock();
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn($course)
                ->getMock();
            $validator = new CannotSubscribeToInactiveCourse($courseRepository);
            $validator->checkIfCannotSubscribeToInactiveCourse(['courseId' => '6fd21fb4-5787-4113-9e48-44ded2492608']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(26, $data['error']['code']);
            $this->assertSame('Cannot subscribe to inactive course.', $data['error']['message']);
            $this->assertSame('Cannot subscribe to inactive course.', $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
