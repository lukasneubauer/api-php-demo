<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Course;
use App\Exceptions\ValidationException;
use App\Repositories\CourseRepository;
use App\Validators\NoDataFoundForCourseIdUrlParameter;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class NoDataFoundForCourseIdUrlParameterTest extends TestCase
{
    public function testCheckIfAnyDataForUrlParameterCourseIdWereFoundDoesNotThrowException(): void
    {
        try {
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(m::mock(Course::class))
                ->getMock();
            $validator = new NoDataFoundForCourseIdUrlParameter($courseRepository);
            $validator->checkIfAnyDataForUrlParameterCourseIdWereFound(new ParameterBag(['id' => 'cc3488de-ccdb-40c3-b964-a4e04a51314a']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfAnyDataForUrlParameterCourseIdWereFoundThrowsException(): void
    {
        try {
            $courseRepository = m::mock(CourseRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new NoDataFoundForCourseIdUrlParameter($courseRepository);
            $validator->checkIfAnyDataForUrlParameterCourseIdWereFound(new ParameterBag(['id' => 'cc3488de-ccdb-40c3-b964-a4e04a51314a']));
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(7, $data['error']['code']);
            $this->assertSame("No data found for 'id' url parameter.", $data['error']['message']);
            $this->assertSame("No data found for 'id' url parameter.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
