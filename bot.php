<?php
error_reporting(-1) ; // включить все виды ошибок, включая  E_STRICT
ini_set('display_errors', 'On');  // вывести на экран помимо логов

require 'classes/Curl.php';
require 'classes/PDO.php';
require 'vendor/autoload.php';
/**
 * @var \TelegramBot\Api\BotApi $bot
 */
$curl = new Curl();


$json = file_get_contents('php://input'); // Получаем запрос от пользователя
$action = json_decode($json, true); // Расшифровываем JSON

// Получаем информацию из БД о настройках бота
$set_bot = DB::$the->query("SELECT * FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);

$message	= $action['message']['text']; // текст сообщения от пользователя
$chat		= $action['message']['chat']['id']; // ID чата
$username	= $action['message']['from']['username']; // username пользователя
$first_name	= $action['message']['from']['first_name']; // имя пользователя
$last_name	= $action['message']['from']['last_name']; // фамилия пользователя
$token		= $set_bot['token']; // токен бота


//$message	= '🏠Омск'; // текст сообщения от пользователя
//$message	= '🏠Томск'; // текст сообщения от пользователя
//$chat		= '213586898'; // ID чата
//$username	= 'syrexby'; // username пользователя
//$first_name	= 'Yuri'; // имя пользователя
//$last_name	= ''; // фамилия пользователя
//$token		= '317364050:AAHBb1wnvALyY0MDZ5s3V9udc47NmeYr7tA'; // токен бота


$bot = new \TelegramBot\Api\Client($token);
// Если бот отключен, прерываем все!
if($set_bot['on_off'] == "off") exit;
//$message = "Доп. инфо";

if ($message == "↪Назад") {

	DB::$the->prepare("UPDATE sel_users SET cat=? WHERE chat=? ")->execute(array("0", $chat));

	DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $chat));
	DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $chat));
	DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $chat));

	DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array('0', $chat));
	DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array('pay_number', $chat));

}

if ($message == "🔷Доп. инфо") {
	$info = DB::$the->query("SELECT request, response FROM `sel_addinfo`");
	$info = $info->fetchAll();
	$keys = [];
	$msg = "Выберите из кнопок:\n";
	$i = 0;
	$k = 0;
	foreach ($info as $el){
		$keys[][] = urldecode($el['request']);
		$msg .= urldecode($el['request'])."\n";
		// $k++;
		// if($k >= 3){ $i++; $k = 0;}
	}
	$keys[][] = '↪Назад';
//	print_r($keys);
//	die();
	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keys, null, true);
	$bot->sendMessage($chat, $msg, false, null, null, $keyboard);
	exit;
}

$info = DB::$the->query("SELECT request, response FROM `sel_addinfo`");
$info = $info->fetchAll();
foreach ($info as $el) {
	if (urldecode($el['request']) == $message) {
		$bot->sendMessage($chat, urldecode($el['response']));
		exit;
	}
}

//}
//$requests = [];
//foreach ($info as $el){
//	$requests[] = $el['request'];
//}
//if (in_array($message, $requests)) {
//	
//	$bot->sendMessage($chat, 'OOK');
//	exit;
//}
//var_dump($requests);
//die;
// Проверяем наличие пользователя в БД
$vsego = DB::$the->query("SELECT chat FROM `sel_users` WHERE `chat` = {$chat} ");
$vsego = $vsego->fetchAll();

// Если отсутствует, записываем его
if(count($vsego) == 0){

// Записываем в БД
	$params = array('username' => $username, 'first_name' => $first_name, 'last_name' => $last_name,
		'chat' => $chat, 'time' => time() );

	$q = DB::$the->prepare("INSERT INTO `sel_users` (username, first_name, last_name, chat, time) 
VALUES (:username, :first_name, :last_name, :chat, :time)");
	$q->execute($params);
}

// Получаем всю информацию о пользователе
$user = DB::$the->query("SELECT ban,cat FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

// Если юзер забанен, отключаем для него все!
if($user['ban'] == "1") exit;

// Если сделан запрос оплата 
if ($message == "📦оплата" or $message == "📦Оплата") {
	$chat = escapeshellarg($chat);
	exec('bash -c "exec nohup setsid wget -q -O - '.$set_bot['url'].'/verification.php?chat='.$chat.' > /dev/null 2>&1 &"');
	exit;
}

// Если проверяют список покупок
if ($message == "💰заказы" or $message == "💰Заказы") {
	$chat = escapeshellarg($chat);
	exec('bash -c "exec nohup setsid php ./orders.php '.$chat.' > /dev/null 2>&1 &"');
	exit;
}

// Команда помощь
if ($message == "🆘помощь" or $message == "🆘Помощь") {


	$text = "СПИСОК КОМАНД

Оплата - для проверки оплаты

Заказы - список всех ваших заказов

Отмена или '0' - отмена заказа

Помощь - вызов списка команд
";

	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([['♻️Главное меню'], ['📦Оплата', '💰Заказы'], ['🆘Помощь']], null, true);

// Отправляем все это пользователю
	$bot->sendMessage($chat, $text, false, null, null, $keyboard);
	exit;
}

if ($message == "0" or $message == "↪️Отмена") {

	DB::$the->prepare("UPDATE sel_users SET cat=? WHERE chat=? ")->execute(array("0", $chat));

	DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $chat));
	DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $chat));
	DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $chat));

	DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array('0', $chat));
	DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array('pay_number', $chat));

	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([['♻️Главное меню'], ['📦Оплата', '💰Заказы', '↪️Отмена'], ['🆘Помощь']], null, true);

// Отправляем все это пользователю
	$bot->sendMessage($chat, "🚫 Заказ отменен!", false, null, null, $keyboard);

	exit;
}
// $user['cat'] = 0;
//$bot->sendMessage($chat, $message);
// var_dump($message);
// var_dump($user['cat']);
if($user['cat'] == 0 && !empty($message)){
	// Проверяем наличие категории
	$cat = DB::$the->query("SELECT id FROM `sel_category` WHERE `name` = '".urlencode($message)."' ");
	$cat = $cat->fetchAll();

	if (count($cat) != 0){
		$message = urlencode($message);
		$output = "";
		require_once "./select_cat.php";
		exit;
	}
}
if($user['cat'] > 0 && !empty($message)){
	// Проверяем наличие товара
	$cat = DB::$the->query("SELECT id FROM `sel_subcategory` WHERE `id_cat` = '".$user['cat']."' ");
	$cat = $cat->fetchAll();
	// $message = urldecode('%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0+%F0%9F%98%8E');
	if (count($cat) != 0)
	{
		$message = urlencode($message);
		require_once "./select.php";
		exit;
	}
}
$text = urldecode($set_bot['hello'])."\n\n";

$query = DB::$the->query("SELECT id,name,mesto FROM `sel_category` order by `mesto` ");

$keys = [];
$i = 0;
$k = 0;
while($cat = $query->fetch()) {
	$text .= "🔷 ".urldecode($cat['name'])."\n\n"; // ЭТО НАЗВАНИЕ КАТЕГОРИЙ

	$keys[][] = urldecode($cat['name']);
	// $k++;
	// if($k >= 3){ $i++; $k = 0;}
}

$keys[][] = '🆘Помощь';
$keys[][] = '🔷Доп. инфо';
$text .= "\n".$set_bot['footer'];

$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keys, null, true);
$bot->sendMessage($chat, $text, false, null, null, $keyboard);
