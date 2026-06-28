<?php
return [
    'title' => 'Activity',
    'subtitle' => 'Monitor your API usage, costs, and performance',
    'ranges' => ['24h' => 'Last 24 hours', '7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days'],
    'stats' => [
        'total_requests' => 'Total requests',
        'total_tokens' => 'Tokens used',
        'total_spent' => 'Total spent',
        'avg_latency' => 'Avg latency',
        'success_rate' => 'Success rate',
        'error_rate' => 'Errors',
    ],
    'chart_title' => 'Usage trends',
    'top_models' => 'Top models',
    'no_data' => 'No activity in this period',
];