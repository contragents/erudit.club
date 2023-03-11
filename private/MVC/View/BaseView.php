<?php
class BaseView
{
    public static function render($vars, $subview)
    {
        extract($vars);
        include 'Tpl/HeaderTemplate.php';
        include "SubViews/$subview.php";
        include 'Tpl/FooterTemplate.php';
    }
}
