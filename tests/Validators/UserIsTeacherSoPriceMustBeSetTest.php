<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\UserIsTeacherSoPriceMustBeSet;
use PHPUnit\Framework\TestCase;

final class UserIsTeacherSoPriceMustBeSetTest extends TestCase
{
    public function testCheckIfUserIsTeacherSoPriceIsSetDoesNotThrowException(): void
    {
        try {
            $validator = new UserIsTeacherSoPriceMustBeSet();
            $validator->checkIfUserIsTeacherSoPriceIsSet(
                [
                    'isTeacher' => true,
                    'price' => 25000,
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserIsTeacherSoPriceIsSetThrowsException(): void
    {
        try {
            $validator = new UserIsTeacherSoPriceMustBeSet();
            $validator->checkIfUserIsTeacherSoPriceIsSet(
                [
                    'isTeacher' => true,
                    'price' => null,
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(24, $data['error']['code']);
            $this->assertSame('User is teacher, so price must be set.', $data['error']['message']);
            $this->assertSame('User is teacher, so price must be set.', $e->getMessage());
        }
    }
}
