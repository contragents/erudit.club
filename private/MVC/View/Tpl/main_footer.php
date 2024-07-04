</div>
</div>
</div>
</div>
</div>
<?php
try {
    $topPlayers = PlayerModel::getTopPlayersCached(2, 10);
    print ViewHelper::tag('pre', print_r($topPlayers, true)); exit;
    foreach ($topPlayers as $num => $playerArr) {
        foreach ($playerArr as $player) {
            print ViewHelper::tag(
                'span',
                "№$num "
                . ViewHelper::tag(
                    'img',
                    '',
                    [
                        'src' => $player['avatar_url'],
                        //'width' => '50px',
                        'style' => 'border-radius: 5px 5px 5px 5px; margin-bottom: 9px;',
                        'height' => '75px',
                        'max-width' => '100px',
                    ]
                )
                . ViewHelper::tag(
                    'a',
                    $player['name'],
                    [
                        'href' => '/' . StatsController::getUrl('games', ['common_id' => $player['common_id']]),
                        'title' => 'Перейти в статистику игрока'
                    ]
                )
            );
        }
    }
} catch(Throwable $e) {print $e->__toString();}
?>
</body>
</html>