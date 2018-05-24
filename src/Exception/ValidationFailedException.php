<?php

namespace Shopify\Exception;

/**
 * When GitHub returns with a HTTP response that says our request is invalid.
 */
class ValidationFailedException extends ErrorException implements HttpClientException
{
}
