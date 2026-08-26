<?php

return [
    'teacher_invite_code' => env('TEACHER_INVITE_CODE', ''),
    'seed_demo' => env('SEED_DEMO', false),
    'on_render' => filled(env('RENDER')) || filled(env('RENDER_EXTERNAL_URL')),

    'student_temp_password' => env('CAST_STUDENT_TEMP_PASSWORD', 'iloveyouILINK'),

    'provisioned_students' => [
        ['name' => 'Joshua', 'email' => 'joshua@ilinkcst.edu.ph'],
        ['name' => 'Zuharto Mangagel', 'email' => 'zuharto_mangagel201309206@ilinkcst.edu.ph'],
        ['name' => 'Norhaida', 'email' => 'norhaida@ilinkcst.edu.ph'],
        ['name' => 'Eleonor', 'email' => 'eleonor@ilinkcst.edu.ph'],
    ],
];
