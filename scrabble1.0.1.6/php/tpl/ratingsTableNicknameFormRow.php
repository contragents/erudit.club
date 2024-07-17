<?php
return "
<tr>
    <td colspan='3'>
        <div style='margin-bottom:0; margin-left: 0; padding-left: 0' class='form-group row'>
            <div class='col-xs-2'>
                <input width='50%' class='form-control input-sm' id='player_name' title='максимум 16 символов' maxlength='16'  style = \"background-color: rgba(255, 255, 255, 0.4);\" name='name' placeholder='Ваш Ник' type='text'>
            </div>
            &nbsp;&nbsp;
            <button type='submit' class='btn btn-outline-secondary' onclick='savePlayerName($(\"#player_name\").val());return false;'>
                Задать
            </button>  
        </div>
    </td>
</tr>
";