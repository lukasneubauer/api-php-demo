<?php

declare(strict_types=1);

include __DIR__ . '/../vendor/autoload.php';

// USERS ///////////////////////////////////////////////////////////////////////

$userTeachers = [];
for ($i = 2; $i <= 10; $i++) {
    $userTeachers['user_teacher_' . $i] = [
        '__construct' => [
            generate_uuid(),
            '50%? <firstNameMale()> : <firstNameFemale()>',
            '<lastName()>',
            null,
            '<safeEmail()>',
            get_password(),
            'Europe/Prague',
            new App\Entities\Token(generate_token(), date_time_utc()),
            true,
            get_created_at(),
            get_updated_at(),
        ],
        '__calls' => [
            0 => ['setIsTeacher' => [true]],
            1 => ['setIsStudent' => [false]],
            2 => ['unsetToken' => []],
        ],
    ];
}

$userStudents = [];
for ($i = 2; $i <= 10; $i++) {
    $userStudents['user_student_' . $i] = [
        '__construct' => [
            generate_uuid(),
            '50%? <firstNameMale()> : <firstNameFemale()>',
            '<lastName()>',
            null,
            '<safeEmail()>',
            get_password(),
            'Europe/Prague',
            new App\Entities\Token(generate_token(), date_time_utc()),
            true,
            get_created_at(),
            get_updated_at(),
        ],
        '__calls' => [
            0 => ['setIsTeacher' => [false]],
            1 => ['setIsStudent' => [true]],
            2 => ['unsetToken' => []],
        ],
    ];
}

$userBoth = [];
for ($i = 2; $i <= 10; $i++) {
    $userBoth['user_both_' . $i] = [
        '__construct' => [
            generate_uuid(),
            '50%? <firstNameMale()> : <firstNameFemale()>',
            '<lastName()>',
            null,
            '<safeEmail()>',
            get_password(),
            'Europe/Prague',
            new App\Entities\Token(generate_token(), date_time_utc()),
            true,
            get_created_at(),
            get_updated_at(),
        ],
        '__calls' => [
            0 => ['setIsTeacher' => [true]],
            1 => ['setIsStudent' => [true]],
            2 => ['unsetToken' => []],
        ],
    ];
}

$userTeachers['user_teacher_1'] = [
    '__construct' => [
        '8a06562a-c59a-4477-9e0a-ab8b9aba947b',
        'John',
        'Doe',
        get_avatar(),
        'john.doe@example.com',
        get_password(),
        'Europe/Prague',
        new App\Entities\Token(generate_token(), date_time_utc()),
        true,
        get_created_at(),
        get_updated_at(),
    ],
    '__calls' => [
        0 => ['setIsTeacher' => [true]],
        1 => ['setIsStudent' => [false]],
        2 => ['unsetToken' => []],
    ],
];

$userStudents['user_student_1'] = [
    '__construct' => [
        '912ff62e-fef5-442a-9953-b7c18dca9dae',
        'Jane',
        'Doe',
        get_avatar(),
        'jane.doe@example.com',
        get_password(),
        'Europe/Prague',
        new App\Entities\Token(generate_token(), date_time_utc()),
        true,
        get_created_at(),
        get_updated_at(),
    ],
    '__calls' => [
        0 => ['setIsTeacher' => [false]],
        1 => ['setIsStudent' => [true]],
        2 => ['unsetToken' => []],
    ],
];

$userBoth['user_both_1'] = [
    '__construct' => [
        '2406c756-15a0-411e-ad47-cdc6badc70b7',
        'Jake',
        'Doe',
        get_avatar(),
        'jake.doe@example.com',
        get_password(),
        'Europe/Prague',
        new App\Entities\Token(generate_token(), date_time_utc()),
        true,
        get_created_at(),
        get_updated_at(),
    ],
    '__calls' => [
        0 => ['setIsTeacher' => [true]],
        1 => ['setIsStudent' => [true]],
        2 => ['unsetToken' => []],
    ],
];

$teachers = array_merge($userTeachers, $userBoth);
$students = array_merge($userStudents, $userBoth);

$FIXTURE_USERS = [App\Entities\User::class => array_merge($userTeachers, $userStudents, $userBoth)];

// SUBJECTS ////////////////////////////////////////////////////////////////////

$subjectList = include __DIR__ . '/include/subjects.php';

$subjects = [];
$i = 0;
foreach ($subjectList as $subject) {
    $subjects['subject_' . $i] = [
        '__construct' => [
            generate_uuid(),
            null,
            $subject,
            get_created_at(),
            get_updated_at(),
        ],
    ];
    $i++;
}

$FIXTURE_SUBJECTS = [App\Entities\Subject::class => $subjects];

// COURSES /////////////////////////////////////////////////////////////////////

$courses = [];
for ($i = 0; $i < 100; $i++) {
    $teacherKey = array_rand($teachers);
    $studentsArray = get_random_students($students, 5, $teacherKey);
    $courses['course_' . $i] = [
        '__construct' => [
            $i === 99 ? '6fd21fb4-5787-4113-9e48-44ded2492608' : generate_uuid(),
            '@subject_*',
            '@' . $teacherKey,
            'Letní doučování angličtiny',
            25000,
            true,
            get_created_at(),
            get_updated_at(),
        ],
        'students' => $studentsArray,
    ];
}

$FIXTURE_COURSES = [App\Entities\Course::class => $courses];

// LESSONS /////////////////////////////////////////////////////////////////////

$lessons = [];
$lessonCounts = [2, 3, 5, 7, 8, 10];
$j = 0;
foreach ($courses as $key => $course) {
    $lessonCount = $lessonCounts[mt_rand(0, count($lessonCounts) - 1)];
    $lessonStartHour = mt_rand(8, 19);
    $date = get_random_date_of_first_lesson();
    for ($k = 0; $k < $lessonCount; $k++) {
        $times = get_lesson_times_as_array($lessonStartHour, $date);
        $lessons['lesson_' . $j . '_' . $k] = [
            '__construct' => [
                $j === (count($courses) - 1) && $k === ($lessonCount - 1) ? '77c76af4-cc32-4695-99cf-41f60b8b7ad3' : generate_uuid(),
                '@' . $key,
                $times['from'],
                $times['to'],
                'Minulý, přítomný a budoucí čas',
                get_created_at(),
                get_updated_at(),
            ],
        ];
    }
    $j++;
}

$FIXTURE_LESSONS = [App\Entities\Lesson::class => $lessons];

// DONE ////////////////////////////////////////////////////////////////////////

return array_merge(
    $FIXTURE_USERS,
    $FIXTURE_SUBJECTS,
    $FIXTURE_COURSES,
    $FIXTURE_LESSONS,
);
