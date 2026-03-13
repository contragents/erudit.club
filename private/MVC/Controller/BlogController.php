<?php

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
            ->union([
                        // Находим 3 следующие статьи
                        ArticleModel::find()
                            ->where(
                                [
                                    [
                                        'field_name' => ArticleModel::ID_FIELD,
                                        'condition' => '>',
                                        'value' => $model->_id ?? 1000000,
                                        'raw' => true
                                    ]
                                ]
                            )
                            ->order(ArticleModel::ID_FIELD)
                            ->limit(3),
                        // Объединяем по UNION с тремя случайными статьями
                        ArticleModel::find()
                            ->where(
                                [
                                    [
                                        'field_name' => ArticleModel::ID_FIELD,
                                        'condition' => '!=',
                                        'value' => $model->_id ?? 0,
                                        'raw' => true
                                    ]
                                ]
                            )
                            ->order('rand()')
                            ->limit(3),
                    ])
            ->limit(3) // Отдаем 3 статьи из 6ти
            ->all();

        $content = new BlogContent();
        $content->model = $model;
        $content->featuredArticles = $featuredArticles;

        return self::render('Blog', $content);
    }
}