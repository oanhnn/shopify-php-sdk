<?php

namespace Shopify\Exception;

/**
 * MissingArgumentException.
 */
class MissingArgumentException extends ErrorException
{
    /**
     * MissingArgumentException constructor.
     * @param array|string $required
     * @param int $code
     * @param null $previous
     */
    public function __construct($required, $code = 0, $previous = null)
    {
        if (is_string($required)) {
            $required = [$required];
        }

        parent::__construct(
            sprintf('One or more of required ("%s") parameters is missing!', implode('", "', $required)),
            $code,
            $previous
        );
    }
}
