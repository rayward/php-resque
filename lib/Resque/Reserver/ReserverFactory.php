<?php

namespace Resque\Reserver;

use Psr\Container\ContainerInterface;

class ReserverFactory
{
    /** @var string */
    const DEFAULT_RESERVER = QueueOrderReserver::class;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Creates a reserver given its name in snake case format.
     *
     * @param string $name
     * @return ReserverInterface
     * @throws UnknownReserverException
     */
    public function createFromName($name)
    {
        $className = ucwords($name, '_');
        $className = str_replace('_', '', $className);
        $className = __NAMESPACE__ . '\\' . $className . 'Reserver';

        if (!$this->container->has($className)) {
            throw new UnknownReserverException($className);
        }

        return $this->container->get($className);
    }

    /**
     * Creates the default reserver.
     *
     * @return ReserverInterface
     */
    public function createDefault()
    {
        if ($this->container->get('env.reserver.use_blocking')) {
            return $this->container->get(BlockingListPopReserver::class);
        }

        return $this->container->get(self::DEFAULT_RESERVER);
    }
}
