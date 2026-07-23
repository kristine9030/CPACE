<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mock Exam Access Code
    |--------------------------------------------------------------------------
    |
    | Mock exams are scheduled and administered by faculty. Students must enter
    | this code (given by the proctor at exam time) to unlock the mock exam.
    | Override it in .env with MOCK_EXAM_ACCESS_CODE without touching code.
    |
    */

    'access_code' => env('MOCK_EXAM_ACCESS_CODE', 'CPALE2026'),

];
