<?php

namespace Shopify\Pagination;

use Shopify\Client;
use Shopify\HttpClient\Message\ResponseMediator;

class Pager implements PagerInterface
{
    /**
     * The GitHub Client to use for pagination.
     *
     * @var \Shopify\Client
     */
    protected $client;

    /**
     * Comes from pagination info in Shopify API results.
     *
     * @var array
     */
    protected $pagination;

    /**
     * The Shopify client to use for pagination.
     * This must be the same instance that you got the Api instance from.
     *
     * @param \Shopify\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(PageableApi $api, $method, array $parameters = [])
    {
        $result = $this->callApi($api, $method, $parameters);
        $this->postFetch();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(PageableApi $api, $method, array $parameters = [])
    {
        // store the original perPage from the api
        $perPage = $api->getPerPage();

        // set parameters per_page to GitHub max to minimize number of requests
        $api->setPerPage(100);

        try {
            $result = $this->callApi($api, $method, $parameters);
            $this->postFetch();

            $result = $result['items'] ?? $result;

            while ($this->hasNext()) {
                $next = $this->fetchNext();

                $result = array_merge($result, $next['items'] ?? $next);
            }
        } finally {
            // restore the original perPage
            $api->setPerPage($perPage);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function postFetch()
    {
        $this->pagination = ResponseMediator::getPagination($this->client->getLastResponse());
    }

    /**
     * {@inheritdoc}
     */
    public function hasNext(): bool
    {
        return $this->has('next');
    }

    /**
     * {@inheritdoc}
     */
    public function fetchNext()
    {
        return $this->get('next');
    }

    /**
     * {@inheritdoc}
     */
    public function hasPrevious(): bool
    {
        return $this->has('prev');
    }

    /**
     * {@inheritdoc}
     */
    public function fetchPrevious()
    {
        return $this->get('prev');
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFirst()
    {
        return $this->get('first');
    }

    /**
     * {@inheritdoc}
     */
    public function fetchLast()
    {
        return $this->get('last');
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function has($key): bool
    {
        return !empty($this->pagination) && isset($this->pagination[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \Http\Client\Exception
     */
    protected function get($key)
    {
        if ($this->has($key)) {
            $result = $this->client->getHttpClient()->get($this->pagination[$key]);
            $this->postFetch();

            return ResponseMediator::getContent($result);
        }
    }

    /**
     * @param PageableApi $api
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    protected function callApi(PageableApi $api, $method, array $parameters)
    {
        return call_user_func_array([$api, $method], $parameters);
    }
}
