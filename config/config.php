<?php

use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Resque\Reserver;

return [
    'env.redis.host'                  => DI\env('REDIS_BACKEND'),
    'env.redis.db'                    => DI\env('REDIS_BACKEND_DB', null),
    'env.redis.prefix'                => DI\env('PREFIX'),
    'env.app_include'                 => DI\env('APP_INCLUDE'),
    'env.worker.count'                => DI\env('COUNT', 1),
    'env.worker.check_interval'       => DI\env('INTERVAL', 5),
    'env.worker.pidfile'              => DI(\env('PIDFILE')),
    'env.blpop.timeout'               => DI\env('BLPOP_TIMEOUT', DI\get('env.worker.check_interval')),
    'env.reserver'                    => DI\env('RESERVER'),
    'env.reserver.use_blocking'       => DI\env('BLOCKING'),
    'env.queue'                       => DI\env('QUEUE'),

    'queues'                      => function (ContainerInterface $c) {
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

    'reserver.factory' => Reserver\ReserverFactory::class,
];
