<?php
return [
    'title' => 'API логи',
    'subtitle' => 'Подробный лог каждого API запроса',
    'filter_model' => 'Все модели',
    'filter_status' => 'Все статусы',
    'status_success' => 'Успешные',
    'status_error' => 'Ошибки',
    'cols' => [
        'time' => 'Время',
        'model' => 'Модель',
        'tokens' => 'Токены (вход→выход)',
        'cost' => 'Стоимость',
        'latency' => 'Скорость',
        'status' => 'Статус',
        'ip' => 'IP',
    ],
    'empty' => 'Пока нет логов',
    'empty_desc' => 'Сделайте первый API запрос',
];