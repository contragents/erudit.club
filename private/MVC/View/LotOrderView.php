<?php
class LotOrderView
{
    public static function render($vars, $subview)
    {
        extract($vars);
        include 'Tpl/HeaderTemplate.php';
        if (is_array($subview)) {
            foreach($subview as $sv) {
                include "SubViews/$sv.php";
            }
        } else {
            include "SubViews/$subview.php";
        }
        include 'Tpl/FooterTemplate.php';
    }
}
