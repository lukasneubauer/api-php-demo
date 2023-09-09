<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Entities\Lesson;
use App\Exceptions\ValidationException;
use App\Repositories\LessonRepository;
use App\Validators\NoDataFoundForLessonIdUrlParameter;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class NoDataFoundForLessonIdUrlParameterTest extends TestCase
{
    public function testCheckIfAnyDataForUrlParameterLessonIdWereFoundDoesNotThrowException(): void
    {
        try {
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(m::mock(Lesson::class))
                ->getMock();
            $validator = new NoDataFoundForLessonIdUrlParameter($lessonRepository);
            $validator->checkIfAnyDataForUrlParameterLessonIdWereFound(new ParameterBag(['id' => 'cc3488de-ccdb-40c3-b964-a4e04a51314a']));
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfAnyDataForUrlParameterLessonIdWereFoundThrowsException(): void
    {
        try {
            $lessonRepository = m::mock(LessonRepository::class)
                ->shouldReceive('getById')
                ->times(1)
                ->andReturn(null)
                ->getMock();
            $validator = new NoDataFoundForLessonIdUrlParameter($lessonRepository);
            $validator->checkIfAnyDataForUrlParameterLessonIdWereFound(new ParameterBag(['id' => 'cc3488de-ccdb-40c3-b964-a4e04a51314a']));
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
