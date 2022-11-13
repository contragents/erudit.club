<!DOCTYPE html>
<html lang="ru-RU">
<head>
<title>Снапшот игры Эрудит.club</title>
<meta name="viewport" content="width=518">
<meta property="og:description"   content="У Вас есть все буквы алфавита и возможность сразиться с онлайн-противником в классической игре Эрудит! Стандартные правила" />
<meta property="og:image"         content="//xn--d1aiwkc2d.club/snapshots/<?=$_GET['file']?>.png" />
<!--
<link rel='stylesheet' id='snapshots'  href='/css/snapshots.css' type='text/css' media='all' />
-->
</head>
<body>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "https://connect.facebook.net/ru_RU/sdk.js#xfbml=1&version=v3.0";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	
	<div style="position:fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);"><!--margin-top:20px;margin-left:20px;">-->
		<div  class="fb-share-button" 
			data-href="//эрудит.club/snapshots/<?=$_GET['file']?>/" 
			data-layout="button">
		</div>
	</div>
	<div>
		<img onload="setTimeout(function(){alert('Картинка сформирована! Выберите соцсеть, чтобы поделиться изображением');},1000);" src="/snapshots/<?=$_GET['file']?>.png" />
	</div>
	
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript" >
		(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
		m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
		(window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

		ym(66170950, "init", {
			clickmap:true,
			trackLinks:true,
			accurateTrackBounce:true
		});
	</script>
	<noscript><div><img src="https://mc.yandex.ru/watch/66170950" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->

</body>
</html>
