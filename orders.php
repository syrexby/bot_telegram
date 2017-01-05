<?
require 'classes/Curl.php';
require 'classes/PDO.php';

$curl = new Curl();

// Получаем информацию из БД о настройках бота
$set_bot = DB::$the->query("SELECT token FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);
$token		= $set_bot['token']; // токен бота

$chat = $argv[1];

// Получаем информацию о всех покупках
$orders = DB::$the->query("SELECT * FROM `sel_orders` where `chat` = {$chat} ");
$orders = $orders->fetchAll();
// Если их нет
if(count($orders) == 0)
{
$text = "⛔ У вас нет заказов!\n\n";
}
else // Иначе
{	
$text = "📦 Ваши заказы:\n\n";
// Показываем список ключей
$query = DB::$the->query("SELECT id_key,id_subcat FROM `sel_orders` where `chat` = {$chat} ");
while($order = $query->fetch()) {
// Получаем информацию о подкатегории	
$subcat = DB::$the->query("SELECT name,amount FROM `sel_subcategory` where `id` = {$order[id_subcat]} ");
$subcat = $subcat->fetch(PDO::FETCH_ASSOC);
// Получаем информацию о ключах
$key = DB::$the->query("SELECT code FROM `sel_keys` where `id` = {$order[id_key]} ");
$key = $key->fetch(PDO::FETCH_ASSOC);

$text .= " 📬 {$subcat[name]}: {$key[code]}\n\n";	

}
}	

// Отправляем все это пользователю
$curl->get('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $chat,
	'text' => $text,
	)); 	
exit;
?>