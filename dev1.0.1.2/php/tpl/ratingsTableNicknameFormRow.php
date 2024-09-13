<?php
use ViewHelper as VH;

return VH::tr(
    VH::td(
        VH::div(
            VH::div(
                VH::tagOpen(
                    'input',
                    '',
                    [
                        'width' => '50%',
                        'class' => 'form-control input-sm',
                        'id' => 'player_name',
                        'title' => 'максимум 16 символов',
                        'maxlength' => '16',
                        'style' => "background-color: rgba(255, 255, 255, 0.4);",
                        'name' => 'name',
                        'placeholder' => 'Ваш Ник',
                        'type' => 'text'
                    ]
                )
                ,
                ['class' => 'col-xs-2']
            )
            . VH::button(
                'Задать',
                [
                    'type' => 'submit',
                    'class' => 'btn btn-outline-secondary',
                    'onclick' => "savePlayerName($('#player_name').val());return false;"
                ]
            )
            ,
            ['style' => 'margin-bottom:0; margin-left: 0; padding-left: 0', 'class' => 'form-group row']
        )
        ,
        ['colspan' => '3']
    )
);
