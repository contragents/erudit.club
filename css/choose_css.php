<?php
header("Content-type: text/css; charset: UTF-8");
$json = file_get_contents('../img/letters.json');
$arr = json_decode($json,true);
//print_r($arr); exit();
foreach($arr['textures'][0]['frames'] as $num => $frame)
    if ($num <= 31) {
        if ($num != 31)
            print ".letter_$num{
                display: inline-block;
                background: url(https://xn--d1aiwkc2d.club/img/letters.png);
                background-color: grey;    
                background-position: -".($frame['frame']['x']+3)."px -".$frame['frame']['y']."px; 
                width:44px;
                height:54px;
                border-radius: 5px;
            }

            .letter_$num:hover{
                background-color: green;    
            }
            ";
        elseif ($num == 31)
            print ".letter_$num{
                display: inline-block;
                background: url(https://xn--d1aiwkc2d.club/img/letters.png);
                background-color: grey;    
                background-position: -".($frame['frame']['x']+3)."px -".($frame['frame']['y']-4)."px; 
                width:44px;
                height:54px;
                border-radius: 5px;
            }

        .letter_$num:hover{
            background-color: green;    
        }
        ";
    }
    
$koef = 44/76;
$imgWidth = round(1187*$koef);
$json = file_get_contents('../img/letters_english.json');
$arr = json_decode($json,true);
//print_r($arr); exit();
foreach($arr['textures'][0]['frames'] as $num => $frame)
    if ($num > 31) {
        if ($num == 53) 
            print ".letter_$num{
                display: inline-block;
                background: url(https://xn--d1aiwkc2d.club/img/letters_english.png);
                background-color: grey;    
                background-position: -".round($frame['frame']['x']*$koef)."px -".round($frame['frame']['y']*$koef)."px; 
                background-size: {$imgWidth}px;
                width:44px;
                height:54px;
                border-radius: 5px;
            }

            .letter_$num:hover{
                background-color: green;    
            }
            ";
            elseif ($num == 43) 
                print ".letter_$num{
                    display: inline-block;
                    background: url(https://xn--d1aiwkc2d.club/img/letters_english.png);
                    background-color: grey;    
                    background-position: -".round($frame['frame']['x']*$koef)."px -".round(($frame['frame']['y']-8)*$koef)."px; 
                    background-size: {$imgWidth}px;
                    width:44px;
                    height:54px;
                    border-radius: 5px;
                }

                .letter_$num:hover{
                    background-color: green;    
                }
                ";
            elseif ($num == 46) 
                print ".letter_$num{
                    display: inline-block;
                    background: url(https://xn--d1aiwkc2d.club/img/letters_english.png);
                    background-color: grey;    
                    background-position: -".round($frame['frame']['x']*$koef)."px -".round(($frame['frame']['y']+6)*$koef)."px; 
                    background-size: {$imgWidth}px;
                    width:44px;
                    height:54px;
                    border-radius: 5px;
                }

                .letter_$num:hover{
                    background-color: green;    
                }
                ";
                elseif ($num == 40) 
                    print ".letter_$num{
                        display: inline-block;
                        background: url(https://xn--d1aiwkc2d.club/img/letters_english.png);
                        background-color: grey;    
                        background-position: -".round($frame['frame']['x']*$koef)."px -".round(($frame['frame']['y']-2)*$koef)."px; 
                        background-size: {$imgWidth}px;
                        width:44px;
                        height:54px;
                        border-radius: 5px;
                    }

                    .letter_$num:hover{
                        background-color: green;    
                    }
                    ";
                elseif ($num == 56) //w
                    print ".letter_$num{
                        display: inline-block;
                        background: url(https://xn--d1aiwkc2d.club/img/letters_english.png);
                        background-color: grey;    
                        background-position: -".round($frame['frame']['x']*$koef*0.9)."px -".round(($frame['frame']['y']-2)*$koef*0.9)."px; 
                        background-size: ".round($imgWidth*0.9)."px;
                        width:44px;
                        height:54px;
                        border-radius: 5px;
                    }

                    .letter_$num:hover{
                        background-color: green;    
                    }
                    ";
            else
                print ".letter_$num{
                    display: inline-block;
                    background: url(https://xn--d1aiwkc2d.club/img/letters_english.png);
                    background-color: grey;    
                    background-position: -".(round(($frame['frame']['x']+3)*$koef))."px -".round($frame['frame']['y']*$koef)."px; 
                    background-size: {$imgWidth}px;
                    width:44px;
                    height:54px;
                    border-radius: 5px;
                }

                .letter_$num:hover{
                    background-color: green;    
                }
                ";
    }
    

print ".letter_999{
    display: inline-block;
    background: url(https://xn--d1aiwkc2d.club/img/star_transparent.png);
    background-color: grey;    
    background-position: center center;
    background-size: 30px;
    background-repeat: no-repeat;    
    width:44px;
    height:54px;
    border-radius: 5px;
}

.letter_999:hover{
    background-color: green;    

}
";

print ".letter_9999{
    display: inline-block;
    background: url(https://xn--d1aiwkc2d.club/img/star_transparent.png);
    background-color: grey;    
    background-position: center center;
    background-size: contain;
    background-repeat: no-repeat;    
    width:76px;
    height:92px;
    border-radius: 5px;
}

.letter_9999:hover{
    background-color: green;    

}
";

exit();    