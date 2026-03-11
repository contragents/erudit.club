<?php
/** @var BlogContent $content */

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $content->model->_title ?></title>
    <meta name="description" content="<?= $content->model->_desc ?>"/>
    <link rel="stylesheet" type="text/css" href="/css/blog.css">
</head>
<body>

<div class="hero">
    <!-- БАННЕР 16:9 С ВИДЕО -->
    <a href="/" class="video-banner">
        <video autoplay loop muted playsinline>
            <source src="/images/video_banner_hor.mp4" type="video/mp4">
        </video>
        <div class="banner-info">
            <div class="btn-play">Играть сейчас →</div>
            <h2 class="banner-title"></h2>
            <p style="opacity: 0.8; margin: 5px 0 0;"></p>
        </div>
    </a>
</div>

<div class="container">
    <!-- Основная уникализированная статья -->
    <?php
    if ($content->model) { ?>
        <article class="featured-article">
            <?php
            if (!strpos($content->model->_text, 'article-date')) { ?>
                <p class="article-date"><?= substr($content->model->_created_at, 0, 10) ?></p>
            <?php
            } ?>
            <?= $content->model->_text ?>
        </article>
        <?php
    } ?>

    <div class="blog-grid">
        <?php
        $postImages = ['🧩', '🚀', '🧠'];
        $articleType = ['Стратегии', 'Обновления', 'Польза'];
        foreach ($content->featuredArticles as $num => $article) { ?>
            <article class="post-card">
                <div class="post-image"><?= $postImages[$num] ?? '' ?></div>
                <div class="post-content">
                    <span class="post-tag"><?= $articleType[$num] ?? '' ?></span>
                    <h3 class="post-title"><?= $article->_title ?></h3>
                    <p class="post-excerpt"><?= $article->_desc ?></p>
                    <a href="<?= $article->_title ?>" class="read-more">Читать</a>
                </div>
            </article>
            <?php
        } ?>

        <!-- Статья 2 -->
        <!--<article class="post-card">
            <div class="post-image">🚀</div>
            <div class="post-content">
                <span class="post-tag">Обновления</span>
                <h3 class="post-title">Новый рейтинг 1700: что изменилось?</h3>
                <p class="post-excerpt">В последнем патче мы обновили систему начисления очков. Узнайте, как теперь
                    рассчитывается ваш личный прогресс.</p>
                <a href="#" class="read-more">Читать лог</a>
            </div>
        </article>-->

        <!-- Статья 3 -->
        <!--<article class="post-card">
            <div class="post-image">🧠</div>
            <div class="post-content">
                <span class="post-tag">Польза</span>
                <h3 class="post-title">Судоку и нейропластичность мозга</h3>
                <p class="post-excerpt">Научное обоснование того, почему ежедневное решение головоломок замедляет
                    старение мозга на 10 лет.</p>
                <a href="#" class="read-more">Открыть архив</a>
            </div>
        </article>-->
    </div>


    <!-- Пагинация -->
    <!--<div class="pagination">
        <a href="#" class="page-link">« Назад</a>
        <a href="#" class="page-link active">1</a>
        <a href="#" class="page-link">2</a>
        <a href="#" class="page-link">3</a>
        <a href="#" class="page-link">Вперед »</a>
    </div>-->
</div>

</body>
</html>
