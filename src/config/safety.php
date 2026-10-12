<?php

return [
    'allow_destructive_commands' => (bool) env('ALLOW_DESTRUCTIVE_COMMANDS', false),

    'destructive_commands' => [
        'app:fresh-db',
        'db:wipe',
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'schema:wipe',
    ],
];
