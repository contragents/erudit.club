<?php

$top = $rating['top'];
$marginTop = '';
if (is_numeric($top) && $top <= 10) {
    $marginTop = ' margin-top:86px;';
}
return "
<tr>
	<td colspan='2'>"
    . $recImgs

    . "</td>
</tr>";