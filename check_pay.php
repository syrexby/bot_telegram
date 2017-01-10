<?php

$nulled = DB::$the->query("SELECT id FROM `sel_keys` where `sale` = '0' and `block` = '1' and `block_time` < '".(time()-(60*$set_bot['block']))."' ");
$nulled = $nulled->fetchAll();
foreach ($nulled as $item){
    $bot->sendMessage($chat, $item['id']);
}
if(count($nulled > 0)){
    
    $query = DB::$the->query("SELECT block_user FROM `sel_keys` where `sale` = '0' and `block` = '1' and `block_time` < '".(time()-(60*$set_bot['block']))."' order by `id` ");
    while($us = $query->fetch()) {
        DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $us['block_user']));
        DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $us['block_user']));
        DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $us['block_user']));

        DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array('0', $us['block_user']));
        DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array('0', $us['block_user']));

        DB::$the->prepare("UPDATE sel_users SET ban=ban+1 WHERE chat=? ")->execute(array($chat));
        $warn = DB::$the->query("SELECT ban FROM sel_users WHERE chat= {$chat} order by id limit 1");
        $warn = $warn->fetch(PDO::FETCH_ASSOC)['ban'];
        switch($warn){
            case 1:
                $warn = 'Первое предупреждение!';
                break;
            case 2:
                $warn = 'Второе предупреждение!';
                break;
            case 3:
                $warn = 'Вы успешно забанены!';
                break;
        }
        $keys[][] = 'ПРАЙС';
        $keys[][] = 'Выход';
        $text = "Вы не произвели оплату в течение {$set_bot['block']} минут. 
                Заказ отменен.
                Запрещено резервировать товар без оплаты более трех раз
                {$warn}";
        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keys, null, true);
        $bot->sendMessage($chat, $text, false, null, null, $keyboard);
    }
    exit;
}