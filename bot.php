<?php
error_reporting(1) ; // включить все виды ошибок, включая  E_STRICT
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
//$chat		= '213586898'; // ID чата
$username	= $action['message']['from']['username']; // username пользователя
$first_name	= $action['message']['from']['first_name']; // имя пользователя
$last_name	= $action['message']['from']['last_name']; // фамилия пользователя
$token		= $set_bot['token']; // токен бота
//291326668:AAEEkeDIluD-__nGzWl-qUetY_pwjDE6sSE
//199870151:AAGiGx8yksHxX-oP_78N-0obO5tNzGae4UM


$bot = new \TelegramBot\Api\BotApi($token);
$slash = false;
if(mb_substr($message, 0, 1) == '/'){
    $message = mb_substr($message, 1);
    $slash = true;
};
//$bot->sendMessage($chat, $message);

// Если бот отключен, прерываем все!
if($set_bot['on_off'] == "off") exit;

// Получаем всю информацию о пользователе
$user = DB::$the->query("SELECT ban,cat FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

// Если юзер забанен, отключаем для него все!
if($user['ban'] == "3") exit;


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
	$msg = "Выберите :\n";
	$i = 0;
	$k = 0;
	foreach ($info as $el){
		$keys[][] = urldecode($el['request']);
		$msg .= urldecode($el['request'])."\n";

	}
	$keys[][] = '↪Назад';
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

// Если сделан запрос оплата
if ($message == "оплата" or $message == "Оплата") {
	require_once("./verification.php");
    exit;
}

// Если проверяют список покупок
if ($message == "заказы" or $message == "Заказы") {
	$chat = escapeshellarg($chat);
	exec('bash -c "exec nohup setsid php ./orders.php '.$chat.' > /dev/null 2>&1 &"');
	exit;

}

// Команда помощь
/*if ($message == "помощь" or $message == "Помощь" or $message == "🆘Помощь") {


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
}*/
if ($message == "0" or $message == "↪️Отмена" or $message == "Отмена") {

	DB::$the->prepare("UPDATE sel_users SET cat=? WHERE chat=? ")->execute(array("0", $chat));
	DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $chat));
	DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $chat));
	DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $chat));
	DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array('0', $chat));
	DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array('pay_number', $chat));
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

	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([['♻️Главное меню']/*, ['📦Оплата', '💰Заказы', '↪️Отмена'], ['🆘Помощь']*/], null, true);
	$text = "🚫 Заказ отменен!
	Запрещено резервировать товар без оплаты более трех раз.
	{$warn}";
// Отправляем все это пользователю
	$bot->sendMessage($chat, $text, false, null, null, $keyboard);

	exit;
}

if ($message == 'ПРАЙС' || $message == '33'){
    $cats = DB::$the->query("SELECT id,name,mesto FROM `sel_category` order by `mesto` ");
	$cats = $cats->fetchAll();
    $text = '';
    $keys = [];
    $keys[][] = 'Главное меню';
    $i = 0;
    $k = 0;
	if (count($cats) > 0){
    	foreach($cats as $cat) {
			$subcats = DB::$the->query("SELECT id, name, mesto, amount FROM sel_subcategory WHERE id_cat = ".$cat['id']." order by mesto ");
			$subcats = $subcats->fetchAll();
			if (count($subcats) > 0) {
				$text .= $cat['mesto'] . '. ' . urldecode($cat['name']) . ": \n"; // ЭТО НАЗВАНИЕ КАТЕГОРИЙ
				foreach ($subcats as $subcat) {
					$text .= urldecode($subcat['name']) . " (" . $subcat['amount'] . "руб) - ответ \"" .
						$subcat['id'] . "\" \n"; // ЭТО НАЗВАНИЕ КАТЕГОРИЙ
					$keys[][] = $subcat['id'] . " - " . urldecode($cat['name']) . " - ". urldecode($subcat['name']) .
						" (" . $subcat['amount'] ."руб)";
				}
				$text .= "\n";
			}
		}
    }
    $keys[][] = 'Назад';
	$text .= "Сделайте выбор и введите соответствующий номер \n";
    $text .= "\n".$set_bot['footer'];


    $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keys, null, true);
    $bot->sendMessage($chat, $text, false, null, null, $keyboard);
    exit;
}

if(/*$user['cat'] == 0 &&*/ !empty($message)){
	$message = strstr($message, ' ', true);
	// Проверяем наличие категории
	$cat = DB::$the->query("SELECT id FROM `sel_subcategory` WHERE `name` = '".urlencode($message)."' ");
	$cat = $cat->fetchAll();

	if (count($cat) > 0 && !$slash){
		$message = urlencode($message);
		$output = "";
		require_once "./select.php";
		exit;
	} else{
		$cat = DB::$the->query("SELECT id FROM `sel_subcategory` WHERE `id` = '".urlencode($message)."' ");
		$cat = $cat->fetchAll();

		if (count($cat) != 0){
			$message = urlencode($message);
			$output = "";
			require_once "./select.php";
			exit;
		}
	}
}
/*if($user['cat'] > 0 && !empty($message)){
	// Проверяем наличие товара
	$cat = DB::$the->query("SELECT id FROM `sel_subcategory` WHERE `id_cat` = '".$user['cat']."' ");
	$cat = $cat->fetchAll();

	if (count($cat) != 0)
	{
		$message = urlencode($message);
		require_once "./select.php";
		exit;
	}
}*/
$text = urldecode($set_bot['hello'])."\n\n";
$text .= "\n".$set_bot['footer'];
$keys[][] = 'ПРАЙС';
$keys[][] = 'Выход';
$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keys, null, true);
$bot->sendMessage($chat, $text, false, null, null, $keyboard);
