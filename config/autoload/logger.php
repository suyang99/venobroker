<?php declare(strict_types=1);

use App\Common\Logger\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

$formater = [
    'class' => LineFormatter::class,
    'constructor' => [
        'format'                     => "[%datetime%][%level_name%] %message% %context% %extra%\n",
        'dateFormat'                 => 'Y-m-d H:i:s',
        'allowInlineLineBreaks'      => true,
        'ignoreEmptyContextAndExtra' => true
    ],
];

$handler  = [
    'info'    => [
        'class' => RotatingFileHandler::class,
        'constructor' => [
            'filename' => BASE_PATH . '/runtime/logs/error/hyperf.log',
            'level' => Logger::INFO,
        ],
        'formatter' => [
            'class' => LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
    'debug'   => [],
    'warning' => [],
    'error'   => [
        'class' => RotatingFileHandler::class,
        'constructor' => [
            'filename' => BASE_PATH . '/runtime/logs/error/hyperf.log',
            'level' => Logger::ERROR,
        ],
        'formatter' => [
            'class' => LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];

$config = [];


return [
    'default' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/error/Error.log',
                    'level' => Logger::ERROR,
                ],
                'formatter' => $formater,
            ],
            [
                'class' => Monolog\Handler\StreamHandler::class,
                'constructor' => [
//                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'stream' => 'php://stdout',
                    'level' => Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ]
        ]
    ],
];
