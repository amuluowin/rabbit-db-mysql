<?php


namespace rabbit\db\mysql;

use rabbit\db\Command;
use rabbit\db\ConnectionInterface;
use rabbit\db\Exception;
use rabbit\db\RetryHandlerInterface;

/**
 * Class RetryHandler
 * @package rabbit\db\mysql
 */
class RetryHandler extends RetryHandlerInterface
{
    /** @var int */
    private $sleep = 1;

    /**
     * RetryHandler constructor.
     * @param int $totalCount
     */
    public function __construct(int $totalCount = 3)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @param int $count
     */
    public function setTotalCount(int $count): void
    {
        $this->totalCount = $count;
    }

    /**
     * @param Connection $db
     * @param \Throwable $e
     * @param int $count
     */
    public function handle(ConnectionInterface $db, \Throwable $e, int $count): bool
    {
        $isConnectionError = $this->isConnectionError($e);
        if ($isConnectionError && $count < $this->totalCount) {
            $count > 1 && \Co::sleep($this->sleep);
            $db->reconnect();
            return true;
        }
        return false;
    }

    /**
     * @param Command $cmd
     * @param \Throwable $exception
     * @return bool
     */
    private function isConnectionError(\Throwable $exception): bool
    {
        if ($exception instanceof Exception) {
            $errorInfo = $exception->errorInfo;
            if ($errorInfo[1] == 70100 || $errorInfo[1] == 2006) {
                return true;
            }
        }
        return false;
    }
}