<?php

namespace Resque\Reserver;

use Resque_Job;

/**
 * QueueOrderReserver reserves jobs in the order that the queues is given. This is the default reserver.
 * For example: given queues A, B and C, all the jobs from queue A will be processed before moving onto queue B and
 * then after that queue C.
 */
class QueueOrderReserver extends AbstractReserver implements ReserverInterface
{
    /**
     * {@inheritDoc}
     */
    public function reserve(array $queues)
    {
        foreach ($this->getQueues() as $queue) {
            $this->logger->info('Checking {queue} for jobs', array('queue' => $queue));
            $job = Resque_Job::reserve($queue);
            if ($job) {
                $this->logger->info('Found job on {queue}', array('queue' => $job->queue));
                return $job;
            }
        }
        return null;
    }
}
