<?php

namespace Kanchdev\LaravelWorker;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;

class CronJobWorker extends Worker
{
    public function getNextCronJob($connection, $queue)
    {
        return $this->getNextJob($this->getManager()->connection($connection), $queue);
    }

    public function runCronJob(Job $job, $connection, WorkerOptions $options)
    {
        $this->runJob($job, $connection, $options);
    }
}
