<?php

class Paginator
{
    private $datas;
    private $perPage;
    private $currentPage = 1;
    private $total;
    private $before;
    private $after;
    private $listPage;
    function __construct($datas, $total, $region, $perPage = 5)
    {
        $this->datas = $datas;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->before = $region;
        $this->after = $region;
        $this->listPage = $this->getListPage();
    }
    public function getDatas()
    {
        return array_slice($this->datas, ($this->currentPage - 1)*$this->perPage, $this->perPage);
    }
    public function getCurrentPage() {
        return $this->currentPage;
    }
    public function sort($field, $order) {
        $flag = SORT_REGULAR;

        if($field == 'price') {
            $flag = SORT_NUMERIC;
        }

        if($field == 'name') {
            $flag = SORT_STRING;
        }

        $fields = array_column($this->datas, $field);

        array_multisort($fields, $order == 'asc' ? SORT_ASC : SORT_DESC, $flag, $this->datas);
    }
    public function nextPage()
    {
        if(1 <= $this->currentPage && $this->currentPage < count($this->listPage)) {
            return $this->currentPage + 1;
        }
        return $this->currentPage;
    }
    public function prevPage()
    {
        if(1 < $this->currentPage && $this->currentPage <= count($this->listPage)) {
            return $this->currentPage - 1;
        }
        return $this->currentPage;
    }
    public function setPage($page)
    {
        $min = 1;
        $max = count($this->listPage);

        if ($min <= $page && $page <= $max) {
            $this->currentPage = $page;
        } else if ($page > $max) {
            $this->currentPage = $max;
        } else {
            $this->currentPage = $min;
        }
    }
    protected function getListPage()
    {
        $listPage = [];

        $i = 1;
        do {
            $pages = $this->perPage * $i;
            array_push($listPage, $i);
            $i++;
        } while ($pages < $this->total);

        return $listPage;
    }
    protected function beforePage($start, $before)
    {
        $listBeforePage = [];

        $min = 0;

        for ($i = $start - 1; $i > $min; $i--) {
            if (count($listBeforePage) < $before) {
                array_unshift($listBeforePage, $i);
            }
        }

        return $listBeforePage;
    }
    protected function afterPage($start, $after)
    {
        $listAfterPage = [];

        $max = count($this->listPage);

        for ($i = $start + 1; $i <= $max; $i++) {
            if (count($listAfterPage) < $after) {
                array_push($listAfterPage, $i);
            }
        }
        return $listAfterPage;
    }
    protected function addIfMissing(&$beforePage, &$afterPage)
    {
        $less = count($beforePage) - count($afterPage);

        $additional = [];

        if ($less == 0) {
            return $additional;
        }

        if ($less > 0) {
            $additional = $this->beforePage($beforePage[0], abs($less));
            $beforePage = array_merge($additional, $beforePage);
        } else {
            $additional = $this->afterPage($afterPage[count($afterPage) - 1], abs($less));
            $afterPage = array_merge($afterPage, $additional);
        }
    }
    public function getLinks()
    {
        $beforePage = $this->beforePage($this->currentPage, $this->before);
        $afterPage = $this->afterPage($this->currentPage, $this->after);
        $this->addIfMissing($beforePage, $afterPage);

        return array_merge($beforePage, [$this->currentPage], $afterPage);
    }
    public function isCurrentPage($page) {
        return $page == $this->currentPage;
    }
}
