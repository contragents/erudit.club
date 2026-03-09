<?php

use BaseController;

class BlogController extends BaseController
{
    const DEFAULT_ACTION = 'article';
    const MAIN_PARAM = 'title';

    public function Run()
    {
        return parent::Run();
    }

    public function articleAction(): string
    {
        $model = ArticleModel::find()
            ->where([ArticleModel::TITLE_FIELD => urldecode(self::$Request[self::MAIN_PARAM])])
            ->one();

        $featuredArticles = ArticleModel::find()
            ->where([['field_name' => ArticleModel::ID_FIELD, 'condition' => '!=', 'value' => $model->_id ?? 0, 'raw' => true]])
            ->order('rand()')
            ->limit(3)
            ->all();

        /*todo заменить заголовок на...
         * <div class="article-header">
            <h1 style="font-size: 2rem; margin: 0;">Судоку сложные распечатать бесплатно</h1>
        </div>
        */

        $content = new BlogContent();
        $content->model = $model;
        $content->featuredArticles = $featuredArticles;

        return self::render('Blog', $content);
    }
}