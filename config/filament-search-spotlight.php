<?php

return [
    'key_binding' => 'mod+k',

    'result_limit_per_category' => 8,

    'excluded_resources' => [],

    'disable_default_global_search' => false,

    'categories' => [
        'records' => true,
        'resources' => true,
        'pages' => true,
        'actions' => true,
    ],

    'disable_create_actions' => false,

    // Leave null to use the translated string from the package's language files.
    'placeholder' => null,

    // Any valid CSS width, e.g. '42rem', '640px', '90vw'.
    'max_width' => '42rem',

    // Global registry action names that should be hidden when the plugin provides
    // an action with the same name.
    'override_actions' => [],
];
