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
        //print self::$Request[self::MAIN_PARAM]; exit;
        $model = ArticleModel::find()->where([ArticleModel::TITLE_FIELD => urldecode(self::$Request[self::MAIN_PARAM])])
            //->getQuery();
            ->one();

        //print_r($model); //exit;

        /*todo заменить заголовок на...
         * <div class="article-header">
            <h1 style="font-size: 2rem; margin: 0;">Судоку сложные распечатать бесплатно</h1>
        </div>
        */

        $content = new BlogContent();
        $content->model = $model;

        return self::render('Blog', $content);
    }
}