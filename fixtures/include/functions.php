<?php

declare(strict_types=1);

function get_random_students(array $students, int $count, string $excludeTeacherKey): array
{
    $studentKeys = array_keys($students);
    shuffle($studentKeys);
    $studentsArray = [];
    $i = 0;
    foreach ($studentKeys as $studentKey) {
        if ($studentKey !== $excludeTeacherKey) {
            $studentsArray[] = $studentKey;
            $i++;
        }
        if ($i === $count) {
            break;
        }
    }

    $result = [];
    foreach ($studentsArray as $student) {
        $result[] = '@' . $student;
    }

    return $result;
}

function get_day_of_first_lesson(int $aPointTimestamp, int $bPointTimestamp): DateTime
{
    $firstDayTs = mt_rand($aPointTimestamp, $bPointTimestamp);
    $firstDay = date('Y-m-d', $firstDayTs);

    return date_time_utc($firstDay);
}

function get_random_date_of_first_lesson(): DateTime
{
    $y = date('Y');
    $m = date('m');

    $interval = new DateInterval('P3M');

    $aPoint = date_time_utc("$y-$m-01")->sub($interval);
    $bPoint = date_time_utc("$y-$m-01")->add($interval);

    $aPointTimestamp = $aPoint->getTimestamp();
    $bPointTimestamp = $bPoint->getTimestamp();

    return get_day_of_first_lesson($aPointTimestamp, $bPointTimestamp);
}

function get_lesson_times_as_array(int $lessonStartHour, DateTime $date): array
{
    $oneDayInterval = new DateInterval('P1D');

    if ($date->format('l') === 'Saturday') {
        $date->add($oneDayInterval);
    }
    if ($date->format('l') === 'Sunday') {
        $date->add($oneDayInterval);
    }

    $dateString = $date->format('Y-m-d');
    $from = date_time_utc($dateString . ' ' . $lessonStartHour . ':00:00');
    $to = date_time_utc($dateString . ' ' . ($lessonStartHour + 1) . ':00:00');
    $date->add($oneDayInterval);

    return ['from' => $from, 'to' => $to];
}

function get_created_at(): string
{
    return '<dateTimeBetween("2000-01-01 00:00:01", "2000-06-30 23:59:59")>';
}

function get_updated_at(): string
{
    return '<dateTimeBetween("2000-07-01 00:00:01", "2000-12-31 23:59:59")>';
}

function get_random_price(): int
{
    return mt_rand(100, 999) * 100;
}

function get_password(): App\Entities\Password
{
    return new App\Entities\Password('$2y$13$Y9rdI88aSRnmbjZCwDJqSui/RGvzJYFGezxXVgI/tsaGJCk8GYmaG', App\Passwords\PasswordAlgorithms::BCRYPT); // secret
}

function get_avatar(): string
{
    return (
        new App\Base64\Base64Encoder()
    )->encode(
        (
            new App\Avatars\AvatarCreator(
                new App\Images\ImageFactory()
            )
        )->create(
            \file_get_contents(__DIR__ . '/../../resources/avatars/256x256.png')
        )
    );
}
