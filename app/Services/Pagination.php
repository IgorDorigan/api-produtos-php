<?php

namespace App\Services;


class Pagination
{
    private $data;
    private $page;
    private $limit;
    private $totalPages;


    public function __construct($data, int $page = 1, int $limit = 10)
    {
        $this->data = $data;
        $this->page = max(1, $page);
        $this->limit = max(1, $limit);

        $this->totalPages = (int) ceil(count($data) / $this->limit);
    }

    public function getPageData()
    {
        $offset = ($this->page - 1) * $this->limit;
        return array_slice($this->data, $offset, $this->limit);
    }

    public function getInfo()
    {
        return [
            'current_page' => $this->page,
            'limit' => $this->limit,
            'total_pages' => $this->totalPages
        ];
    }
}