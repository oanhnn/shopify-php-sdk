<?php

namespace Shopify\Exception;

/**
 * ApiLimitExceedException.
 */
class ApiLimitExceedException extends RuntimeException implements HttpClientException
{
    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $reset;

    /**
     * ApiLimitExceedException constructor.
     *
     * @param int $limit
     * @param int $reset Microseconds
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($limit = 40, $reset = 100, $code = 0, $previous = null)
    {
        $this->limit = (int)$limit;
        $this->reset = (int)$reset;

        parent::__construct(
            sprintf('You have reached Shopify api rate limit! Actual limit is: %d', $limit),
            $code,
            $previous
        );
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getResetTime()
    {
        return $this->reset;
    }
}
