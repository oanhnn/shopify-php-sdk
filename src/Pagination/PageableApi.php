<?php

namespace Shopify\Pagination;

interface PageableApi
{
    /**
     * @return int
     */
    public function getPerPage(): int;

    /**
     * @param int|null $limit
     * @return void
     */
    public function setPerPage(int $limit = null);
}
