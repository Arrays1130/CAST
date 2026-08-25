<?php

return [
    'teacher_invite_code' => env('TEACHER_INVITE_CODE', ''),
    'seed_demo' => env('SEED_DEMO', false),
    'on_render' => filled(env('RENDER')) || filled(env('RENDER_EXTERNAL_URL')),
];
