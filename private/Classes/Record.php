<?php

namespace classes;

/**
 * @inheritDoc
 * @property string $cookie
 */
class Record extends \AchievesModel
{
    const COOKIE_PARAM = 'cookie';

    public ?string $cookie = null;
}