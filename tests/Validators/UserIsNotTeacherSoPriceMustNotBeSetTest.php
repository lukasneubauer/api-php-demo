<?php

declare(strict_types=1);

namespace Tests\App\Validators;

use App\Exceptions\ValidationException;
use App\Validators\UserIsNotTeacherSoPriceMustNotBeSet;
use PHPUnit\Framework\TestCase;

final class UserIsNotTeacherSoPriceMustNotBeSetTest extends TestCase
{
    public function testCheckIfUserIsNotTeacherSoPriceIsNotSetDoesNotThrowException(): void
    {
        try {
            $validator = new UserIsNotTeacherSoPriceMustNotBeSet();
            $validator->checkIfUserIsNotTeacherSoPriceIsNotSet(
                [
                    'isTeacher' => false,
                    'price' => null,
                ]
            );
            $this->assertTrue(true);
        } catch (ValidationException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCheckIfUserIsNotTeacherSoPriceIsNotSetThrowsException(): void
    {
        try {
            $validator = new UserIsNotTeacherSoPriceMustNotBeSet();
            $validator->checkIfUserIsNotTeacherSoPriceIsNotSet(
                [
                    'isTeacher' => false,
                    'price' => 25000,
                ]
            );
            $this->fail('Failed to throw exception.');
        } catch (ValidationException $e) {
            $data = $e->getData();
            $this->assertSame(23, $data['error']['code']);
            $this->assertSame('User is not teacher, so price must not be set.', $data['error']['message']);
            $this->assertSame('User is not teacher, so price must not be set.', $e->getMessage());
        }
    }
}
