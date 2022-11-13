<?php

namespace Dadata;
        spl_autoload_register(function ($class_name) {
            $Exploded_class = explode('\\', $class_name);
            include $Exploded_class[count($Exploded_class) - 1] . 'LangProvider.php';
        });

        //print Player::getPlayerID('111222aaf45b0793930123a6b981baa4', true);
        print_r(Prizes::playerCurrentRecords('aaf45b0793930123a6b981baa4fbbcd6'));