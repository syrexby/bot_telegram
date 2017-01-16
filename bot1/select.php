<?php

$user = DB::$the->query("SELECT ban,id_key,cat FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

// Берем информацию о разделе
$row = DB::$the->query("SELECT * FROM `sel_category` WHERE `id` = '".$message."' ");
$cat = $row->fetch(PDO::FETCH_ASSOC);

// Берем информацию о категории
$row = DB::$the->query("SELECT * FROM `sel_subcategory` WHERE `id_cat` = '".$message."' ");
$subcats = $row->fetchAll();

$row = DB::$the->query("SELECT COUNT(id) FROM `sel_subcategory` WHERE `id_cat` = '".$message."' ");
$total = $row->fetch(PDO::FETCH_ASSOC);

if(count($total) == 0) // Если пусто, вызываем ошибку
{ 

// Отправляем текст
$bot->sendMessage($chat, '⛔ Нет ничего в этом городе!');
}
else // Иначе выводим результат
{
	$text = "";
	$text .= '🏠<b>' . urldecode($cat['name']) . "</b>: \n\n"; // ЭТО НАЗВАНИЕ КАТЕГОРИЙ
	$text .= "Выберите район:\n";
	$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	foreach ($subcats as $subcat){
		$text .= "🏃 район <b>".urldecode($subcat['name']) . "</b>\n";
		$text .= "[Для выбора нажмите 👉 /raj" . $subcat['id'] . "]\n";
		$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	}
	$text .= "\nЕсли Вы выбрали не тот город, нажмите 👉 /start для того, чтобы вернуться к выбору города.";
$bot->sendMessage($chat, $text, 'html');


}	

exit;
?>