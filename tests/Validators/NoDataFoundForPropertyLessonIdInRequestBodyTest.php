<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Lesson;
use App\Exceptions\ValidationException;
use App\Repositories\LessonRepository;
use App\Validators\NoDataFoundForPropertyLessonIdInRequestBody;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class NoDataFoundForPropertyLessonIdInRequestBodyTest extends TestCase
{
    public function testCheckIfAnyDataForPropertyLessonIdWereFoundDoesNotThrowException(): void
    {
        try {
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(m::mock(Lesson::class))
                ->getMock();
            $validator = new NoDataFoundForPropertyLessonIdInRequestBody($lessonRepository);
            $validator->checkIfAnyDataForPropertyLessonIdWereFound(['id' => 'b17f7098-d1a0-494d-a5dc-bba9cf418d2b']);
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfAnyDataForPropertyLessonIdWereFoundThrowsException(): void
    {
        try {
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new NoDataFoundForPropertyLessonIdInRequestBody($lessonRepository);
            $validator->checkIfAnyDataForPropertyLessonIdWereFound(['id' => 'b17f7098-d1a0-494d-a5dc-bba9cf418d2b']);
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(13, $data['error']['code']);
            $this->assertSame("No data found for 'id' in request body.", $data['error']['message']);
            $this->assertSame("No data found for 'id' in request body.", $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
