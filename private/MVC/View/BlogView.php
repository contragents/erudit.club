<?php
/** @var BlogContent $content */
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $content->model->_title ?></title>
    <meta name="description" content="<?= $content->model->_desc ?>" />
    <style>
        :root {
            --bg-color: #0b132b;
            --card-bg: rgba(28, 37, 65, 0.7);
            --accent-blue: #3a506b;
            --neon-cyan: #5bc0be;
            --neon-yellow: #f9d423;
            --text-color: #ffffff;
        }

        body {
            background: radial-gradient(circle at center, #1c2541 0%, #0b132b 100%);
            color: var(--text-color);
            font-family: 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 2px solid var(--accent-blue);
            padding-bottom: 20px;
        }

        h1 {
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 0 0 10px var(--neon-cyan);
            color: var(--neon-cyan);
        }

        /* Стиль основной статьи */
        .featured-article {
            background: var(--card-bg);
            border: 1px solid var(--neon-cyan);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 50px;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 30px rgba(91, 192, 190, 0.1);
        }

        .article-header {
            border-left: 4px solid var(--neon-yellow);
            padding-left: 20px;
            margin-bottom: 30px;
        }

        .featured-article h2 {
            color: var(--neon-cyan);
            text-transform: uppercase;
            margin-top: 30px;
        }

        .featured-article ul {
            list-style: none;
            padding: 0;
        }

        .featured-article ul li {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
        }

        .featured-article ul li::before {
            content: "➤";
            position: absolute;
            left: 0;
            color: var(--neon-yellow);
        }

        .featured-article a {
            color: var(--neon-cyan);
            text-decoration: none;
            border-bottom: 1px dashed var(--neon-cyan);
        }

        .featured-article a:hover {
            color: var(--neon-yellow);
            border-color: var(--neon-yellow);
        }

        .summary-box {
            background: rgba(91, 192, 190, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 40px;
            border: 1px solid var(--accent-blue);
        }

        .summary-box h4 {
            margin-top: 0;
            color: var(--neon-yellow);
            text-transform: uppercase;
        }

        /* Пагинация */
        .pagination {
            text-align: center;
            margin-top: 30px;
            padding-bottom: 50px;
        }

        .page-link {
            color: var(--text-color);
            padding: 10px 15px;
            text-decoration: none;
            border: 1px solid var(--accent-blue);
            margin: 0 5px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .page-link:hover, .page-link.active {
            border-color: var(--neon-cyan);
            background: rgba(91, 192, 190, 0.1);
            box-shadow: 0 0 10px var(--neon-cyan);
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>База данных: Модуль Чтения</h1>
    </header>

    <!-- Основная уникализированная статья -->
    <article class="featured-article">
        <?= $content->model->_text ?>
    </article>

    <!-- Пагинация -->
    <div class="pagination">
        <a href="#" class="page-link">« Назад</a>
        <a href="#" class="page-link active">1</a>
        <a href="#" class="page-link">2</a>
        <a href="#" class="page-link">3</a>
        <a href="#" class="page-link">Вперед »</a>
    </div>
</div>

</body>
</html>
