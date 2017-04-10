<?php

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Resque\Reserver;

return [
    ContainerInterface::class => function (ContainerInterface $c) {
        return $c;
    },

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
        $verbose = false;
        if (getenv('LOGGING') || getenv('VERBOSE') || getenv('VVERBOSE')) {
            $verbose = true;
        }

        return new Resque_Log($verbose);
    },

    Reserver\QueueOrderReserver::class => DI\object()
        ->constructor(LoggerInterface::class, DI\get('queues')),

    Reserver\BlockingListPopReserver::class => DI\object()
        ->constructor(LoggerInterface::class, DI\get('queues'), DI\get('env.blpop.timeout')),

    Reserver\RandomOrderReserver::class => DI\object()
        ->constructor(LoggerInterface::class, DI\get('queues')),

    'reserver.factory' => DI\object(Reserver\ReserverFactory::class),
];
