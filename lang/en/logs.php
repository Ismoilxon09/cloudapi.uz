<?php
return [
    'title' => 'API Logs',
    'subtitle' => 'Detailed log of every API request',
    'filter_model' => 'All models',
    'filter_status' => 'All statuses',
    'status_success' => 'Success',
    'status_error' => 'Errors',
    'cols' => [
        'time' => 'Time',
        'model' => 'Model',
        'tokens' => 'Tokens (in→out)',
        'cost' => 'Cost',
        'latency' => 'Latency',
        'status' => 'Status',
        'ip' => 'IP',
    ],
    'empty' => 'No logs yet',
    'empty_desc' => 'Make your first API request to see logs here',
];