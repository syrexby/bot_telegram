<?php

$user = DB::$the->query("SELECT ban,id_key,cat FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

// Берем информацию о разделе
$row = DB::$the->query("SELECT * FROM `sel_category` WHERE `id` = '".$message."' ");
$cat = $row->fetch(PDO::FETCH_ASSOC);

// Берем информацию о категории
$row = DB::$the->query("SELECT * FROM `sel_subcategory` WHERE `id_cat` = '".$message."' ");
$subcats = $row->fetchAll();

// Проверяем наличие ключей
$total = DB::$the->query("SELECT id FROM `sel_keys` where `id_cat` = '".$cat['id']."' and `sale` = '0' and `block` = '0' ");
$total = $total->fetchAll();

if(count($total) == 0) // Если пусто, вызываем ошибку
{ 

// Отправляем текст
$bot->sendMessage($chat, 'К сожалению в этом городе товар закончился

Выберите другой город,  нажмите 👉/start');
}
else // Иначе выводим результат
{
	$subsubcats = [];
	foreach ($subcats as $subcat){
		$qry = DB::$the->query("SELECT * FROM `sel_subsubcategory` where `id_subcat` = '".$subcat['id']."'");
		$qry = $qry->fetchAll();
		$subsubcats = array_merge_recursive($subsubcats, $qry);
	}
	$text = "";
	$text .= '🏠<b>' . urldecode($cat['name']) . "</b>: \n\n"; // ЭТО НАЗВАНИЕ КАТЕГОРИЙ
	$text .= "Выберите товар:\n";
	$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	foreach ($subsubcats as $subsubcat){
		$text .= "🎁 <b>".urldecode($subsubcat['name']) . "</b>\n";
		$text .= "💰 Цена: <b>".urldecode($subsubcat['amount']) . "</b> руб.\n";
		$text .= "[Для выбора нажмите 👉 /buy" . $subsubcat['id'] . "]\n";
		$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	}
	$text .= "\nЕсли Вы выбрали не тот город, нажмите 👉 /start для того, чтобы вернуться к выбору города.";

	/*$text .= "Выберите товар:\n";
	$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	foreach ($keys as $key){
		$text .= "🏃 район <b>".urldecode($subcat['name']) . "</b>\n";
		$text .= "[Для выбора нажмите 👉 /raj" . $subcat['id'] . "]\n";
		$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	}
	$text .= "\nЕсли Вы выбрали не тот город, нажмите 👉 /start для того, чтобы вернуться к выбору города.";*/
$bot->sendMessage($chat, $text, 'html');


}	

exit;
?>