<?php

namespace Resque\Reserver;

use Resque_Job;

interface ReserverInterface
{
    /**
     * Gets the queues to reserve jobs from.
     *
     * @return array
     */
    public function getQueues();

    /**
     * Reserves a job.
     *
     * @return Resque_Job|null A job instance or null if not job was available to reserve.
     */
    public function reserve();

    /**
     * If there was no job available to reserve, should the worker wait before attempting to reserve a job again?
     *
     * @return bool
     */
    public function waitAfterReservationAttempt();
}
