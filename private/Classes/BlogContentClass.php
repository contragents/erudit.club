<?php


/** @property ?ArticleModel $model Модель статьи блога */

class BlogContent extends AbstractViewContent
{
    /** @var ArticleModel[] Модели статей для перелинковки  */
    public array $featuredArticles = [];
}
