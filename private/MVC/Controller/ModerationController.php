<?php

class ModerationController extends BaseController
{

    public function Run()
    {
        return parent::Run();
    }

    public function indexAction()
    {
        //$somePlayer = PlayerModel::getRand();
        //return print_r($somePlayer, true);
        LotOrderView::render(compact('action', 'request', 'Lot', 'user', 'address', 'paymentMethods'), ['vueForm', 'test', 'vueFormJS', 'bootboxDialogs']);
    }
}