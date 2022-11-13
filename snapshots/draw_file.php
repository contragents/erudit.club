<?php
header('Content-Type: image/png');
$p = new Redis();
$p->pconnect("127.0.0.1", 6379);
print base64_decode(substr($p->get('snapshots_'.$_GET['q']),21));