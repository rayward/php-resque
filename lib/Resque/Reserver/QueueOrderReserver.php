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
    public function reserve()
    {
        foreach ($this->getQueues() as $queue) {
            $this->logger->debug('[{reserver}] Checking queue {queue} for jobs', [
                'queue'    => $queue,
                'reserver' => $this->getName(),
            ]);

            $job = Resque_Job::reserve($queue);
            if ($job) {
                $this->logger->info('[{reserver}] Found job on queue {queue}', [
                    'queue'    => $queue,
                    'reserver' => $this->getName(),
                ]);
                return $job;
            }
        }

        return null;
    }
}
