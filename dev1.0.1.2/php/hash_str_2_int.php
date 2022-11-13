<?php
function hash_str_2_int($str,$len=16)
{
$hash_int=base_convert("0x".substr(md5($str),0,$len), 16, 10);
return $hash_int;
}