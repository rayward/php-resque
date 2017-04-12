<?php

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Resque\Reserver;
use Psr\Log\LogLevel;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    'env.redis.host'            => DI\env('REDIS_BACKEND', null),
    'env.redis.db'              => DI\env('REDIS_BACKEND_DB', null),
    'env.redis.prefix'          => DI\env('PREFIX', ''),
    'env.app_include'           => DI\env('APP_INCLUDE', null),
    'env.worker.count'          => DI\env('COUNT', 1),
    'env.worker.check_interval' => DI\env('INTERVAL', 5),
    'env.worker.pidfile'        => DI\env('PIDFILE', null),
    'env.blpop.timeout'         => DI\env('BLPOP_TIMEOUT', DI\get('env.worker.check_interval')),
    'env.reserver'              => DI\env('RESERVER', null),
    'env.reserver.use_blocking' => DI\env('BLOCKING', null),
    'env.logging.level'         => DI\env('LOG_LEVEL', LogLevel::INFO),
    'env.queue'                 => DI\env('QUEUE', null),

    'queues' => function (ContainerInterface $c) {
        $queues = $c->get('env.queue');
        if (empty($queues)) {
            return false;
        }

        if ($queues === '*') {
            return null;
        }

        return explode(',', $queues);
    },

    LoggerInterface::class => function (ContainerInterface $c) {
        // $dateFormat = "H:i:s Y-m-d";
        // $format     = "[%level_name%] %message%\n";
        // if (getenv('LOGGING') || getenv('VERBOSE') || getenv('VVERBOSE')) {
        //     $format = "[%level_name%] [%datetime%] %message%\n";
        // }

        $stream = new StreamHandler(STDOUT, $c->get('env.logging.level'));
        //$stream->setFormatter(new LineFormatter($format, $dateFormat));

        $logger = new Logger('php-resque');
        $logger->pushHandler($stream);
        $logger->pushProcessor(new PsrLogMessageProcessor());

        return $logger;
    },

    Reserver\QueueOrderReserver::class => DI\object()
        ->constructor(DI\get(LoggerInterface::class), DI\get('queues')),

    Reserver\BlockingListPopReserver::class => DI\object()
        ->constructor(DI\get(LoggerInterface::class), DI\get('queues'), DI\get('env.blpop.timeout')),

    Reserver\RandomOrderReserver::class => DI\object()
        ->constructor(DI\get(LoggerInterface::class), DI\get('queues')),

    'reserver.factory' => DI\object(Reserver\ReserverFactory::class),
];
