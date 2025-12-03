<?php

class QueryParts
{
    public array $where = [];
    public $order = null;
    public $limit = null;
    public ?array $fields = null;
    public $groupBy = null;
}