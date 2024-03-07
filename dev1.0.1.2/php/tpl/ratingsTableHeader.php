<?php
//ini_set("display_errors", 1); error_reporting(E_ALL);
if (isset($_POST['vertical'])) {
    $vertical = true;
} else {
    $vertical = false;
}

return '<form id="'. AchievesModel::ACHIEVES_ELEMENT_ID .'">
<table class="table table-sm table-borderless">
  <thead>
    <tr>
      <th scope="col" class="align-top">Имя</th>
      <th scope="col" class="align-top"><div align="center">Рейтинг / ТОП</div></th>' .
    ($vertical
        ? ''
        : '<th scope="col" class="align-top"><div align="center">Сыграл игр</div></th>
               <!--<th scope="col" class="align-top">% побед/отказа</th>-->'
    ) . '
    </tr>
  </thead>
  <tbody>';