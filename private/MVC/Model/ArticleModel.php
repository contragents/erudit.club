<?php

class ArticleModel extends BaseModel
{
    const TABLE_NAME = 'article';

    const CREATED_AT_FIELD = 'created_at';
    const TITLE_FIELD = 'title';
    const TEXT_FIELD = 'text';
    const DESC_FIELD = 'desc';

    public ?int $_id = null;
    public ?string $_created_at = null;

    /** @var string|null Заголовок статьи */
    public ?string $_title = null;
    public ?string $_text = null;
    public ?string $_desc = null;
    public bool $_is_deleted = false;
}