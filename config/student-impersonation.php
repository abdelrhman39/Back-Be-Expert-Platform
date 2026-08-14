<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed admin emails for student impersonation
    |--------------------------------------------------------------------------
    |
    | Requires the students.impersonate permission AND a matching email here.
    | Add emails when granting impersonation to specific staff members.
    |
    */

    'allowed_emails' => array_filter(array_map(
        'trim',
        explode(',', (string) env('STUDENT_IMPERSONATION_ALLOWED_EMAILS', 'admin@local.invalid'))
    )),

];
