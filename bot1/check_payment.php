<?php
error_reporting(1) ; // включить все виды ошибок, включая  E_STRICT
ini_set('display_errors', 'On');  // вывести на экран помимо логов

require 'classes/Curl.php';
require 'classes/PDO.php';
require 'vendor/autoload.php';

$set_bot = DB::$the->query("SELECT * FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);

$token		= $set_bot['token']; // токен бота

//$chat		= '213586898'; // ID чата

$bot = new \TelegramBot\Api\BotApi($token);

$nulled = DB::$the->query("SELECT id FROM `sel_keys` where `sale` = '0' and `block` = '1' and `block_time` < '".(time()-(60*$set_bot['block']))."' ");
$nulled = $nulled->fetchAll();
//$bot->sendMessage($chat, count($nulled));
if(count($nulled) > 0){
    
    $query = DB::$the->query("SELECT block_user FROM `sel_keys` where `sale` = '0' and `block` = '1' and `block_time` < '".(time()-(60*$set_bot['block']))."' order by `id` ");
    while($us = $query->fetch()) {
        DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $us['block_user']));
        DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $us['block_user']));
        DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $us['block_user']));

        DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array('0', $us['block_user']));
        DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array('0', $us['block_user']));

        DB::$the->prepare("UPDATE sel_users SET ban=ban+1 WHERE chat=? ")->execute(array($us['block_user']));
        $warn = DB::$the->query("SELECT ban FROM sel_users WHERE chat= {$us['block_user']} order by id limit 1");
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
        $text = "
Вы не произвели оплату в течение {$set_bot['block']} минут. 
Заказ отменен.
Запрещено резервировать товар без оплаты более трех раз
{$warn}";
        $keys[][] = 'ПРАЙС';
        $keys[][] = 'Выход';
        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keys, null, true);
        $bot->sendMessage($us['block_user'], $text, false, null, null, $keyboard);
    }
    exit;
}