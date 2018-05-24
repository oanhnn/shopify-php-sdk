<?php

namespace Shopify\Pagination;

interface PagerInterface
{
    /**
     * Get pagination info of last request
     *
     * @return null|array
     */
    public function getPagination();

    /**
     * Fetch a single result (page) from an api call.
     *
     * @param PageableApi $api the Api instance
     * @param string $method the method name to call on the Api instance
     * @param array $parameters the method parameters in an array
     * @return array returns the result of the Api::$method() call
     */
    public function fetch(PageableApi $api, $method, array $parameters = []);

    /**
     * Fetch all results (pages) from an api call.
     *
     * Use with care - there is no maximum.
     *
     * @param PageableApi $api the Api instance
     * @param string $method the method name to call on the Api instance
     * @param array $parameters the method parameters in an array
     * @return array returns a merge of the results of the Api::$method() call
     */
    public function fetchAll(PageableApi $api, $method, array $parameters = []);

    /**
     * Method that performs the actual work to refresh the pagination property.
     */
    public function postFetch();

    /**
     * Check to determine the availability of a next page.
     *
     * @return bool
     */
    public function hasNext(): bool;

    /**
     * Check to determine the availability of a previous page.
     *
     * @return bool
     */
    public function hasPrevious(): bool;

    /**
     * Fetch the next page.
     *
     * @return array
     */
    public function fetchNext();

    /**
     * Fetch the previous page.
     *
     * @return array
     */
    public function fetchPrevious();

    /**
     * Fetch the first page.
     *
     * @return array
     */
    public function fetchFirst();

    /**
     * Fetch the last page.
     *
     * @return array
     */
    public function fetchLast();
}