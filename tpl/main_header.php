<?php
/**
 * @var string $title
 * @var string $description
 * @var string $canonical
 */
?>
<!DOCTYPE html>
<html lang="ru-RU">
<head>
<meta name="yandex-verification" content="b3164d5ddf5901c2" />	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="aGRURF9VS0gZMBwPEREqPSk9BhVuPAA.Ggc5ERY8Gw4NDTsLFGUZHg==">
	<title><?=$title?></title>
    <meta name="description" content="<?=$description?>" />
    <?= $canonical ?? ''?>
	<link rel="shortcut icon" href="/favicon.ico" type="image/png">
    </head>
<body>
<div class="page">
	<header class="header">
		<div class="wrapper">
			
			<div class="logo" style="display:inline-block;">
				<a href="/" title="Играть!" target="_blank">
					<img src="/img/ER_LOGO_1024v4.png" width="128px"/>
				</a>
                <a href="/scramble.html" title="Играть в английскую версию!" target="_blank">
					<img src="/img/bid-win.jfif" width="128px"/>
				</a>
			</div>
            <div class="logo" style="display:inline-block;">
			<nav class="nav">
				<ul>
					<li><a href="/news/">Новости</a></li>
					<li><a href="/contacts/">Контакты</a></li>
				</ul>
			</nav>
            </div>
            <div class="logo" style="display:inline-block;">
			<form>
			<fieldset id="SearchForm" class="search">
				<input type="text" id="form-query" name="search" value="" placeholder="Искать слово">				<input type="submit" id="searchInfo" name="submit" value="Найти!" title="Поиск">			</fieldset>
			</form>
            </div>
		</div>
	</header>
<div class="main">
<div class="wrapper" style="width:100%">
<div class="content" style="width:100%">
<div class="blog-list" style="width:100%">
