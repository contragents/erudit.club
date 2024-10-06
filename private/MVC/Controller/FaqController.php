<?php

class FaqController extends BaseController
{
    const NO_STONE_PARAM = 'no_stone';
    const NO_BRONZE_PARAM = 'no_bronze';
    const NO_SILVER_PARAM = 'no_silver';
    const NO_GOLD_PARAM = 'no_gold';
    const FILTER_PLAYER_PARAM = 'opponent_id';

    const COMMON_URL = 'mvc/faq/';


    public function Run()
    {
        ini_set("display_errors", 1);
        error_reporting(E_ALL);

        return parent::Run();
    }

    public function getAllAction(): string
    {
        return json_encode(
            [
                'faq_rules' => T::S('faq_rules'),
                'faq_rating' => T::S('faq_rating'),
                'faq_rewards' => T::S('faq_rewards'),
                'faq_coins' => T::S('faq_coins'),
            ],
            JSON_UNESCAPED_UNICODE
        );
    }
}