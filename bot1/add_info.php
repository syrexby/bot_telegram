<?php
namespace Bot;
/**
 * @var \TelegramBot\Api\BotApi $bot
 */
//require 'classes/PDO.php';
//require 'vendor/autoload.php';

//$set_bot = DB::$the->query("SELECT token,block FROM `sel_set_bot` ");
//$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);
//$token = $set_bot['token']; // токен бота
//
//$chat = trim($argv[1]);
//$chat = '213586898';
//$bot = new \TelegramBot\Api\Client($token);
//
//
//$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([['1', '2', '3'], ['Помощь']], null, true);
//
//$bot->sendMessage($chat, 'asdasd', false, null, null, $keyboard);

class AddInfo{
    private $x;

    function __construct() {
        $this->x = 'qwert';
        return $this->x;
    }
    
    public static function fnc(){
        return "asdas";
    }
}
