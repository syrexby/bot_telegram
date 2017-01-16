<?php
$user = DB::$the->query("SELECT ban,id_key,cat FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

// Берем информацию о разделе
$row = DB::$the->query("SELECT * FROM `sel_subcategory` WHERE `id` = '".$message."' ");
$subcat = $row->fetch(PDO::FETCH_ASSOC);

// Берем информацию о категории
$row = DB::$the->query("SELECT name, id FROM `sel_category` WHERE `id` = '".$subcat['id_cat']."' ");
$cat = $row->fetch(PDO::FETCH_ASSOC);

// Проверяем наличие ключей
$total = DB::$the->query("SELECT id FROM `sel_subsubcategory` where `id_subcat` = '".$subcat['id']."'");
$total = $total->fetchAll();

if(count($total) == 0) // Если пусто, вызываем ошибку
{
	$text .= "⛔ Нет ничего в этом районе! \n\n";
	$text .= "Нажмите 👉 /city{$cat['id']} для того, чтобы вернуться назад в город <b>".urldecode($cat['name'])."</b> и выбрать другой район.
Либо нажмите 👉 /start для того, чтобы вернуться к выбору города.";
// Отправляем текст
$bot->sendMessage($chat, $text, 'html');
}
else // Иначе выводим результат
{
	$subsubcats = DB::$the->query("SELECT * FROM `sel_subsubcategory` where `id_subcat` = '".$subcat['id']."'");
	$subsubcats = $subsubcats->fetchAll();

	$text = "";
	$text .= '🏠 Город: <b>' . urldecode($cat['name']) . "</b> \n";
	$text .= '🏠 Район: <b>' . urldecode($subcat['name']) . "</b> \n\n"; // ЭТО НАЗВАНИЕ КАТЕГОРИЙ
	$text .= "Выберите товар:\n";
	$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	foreach ($subsubcats as $subsubcat){
		$text .= "🎁 <b>".urldecode($subsubcat['name']) . "</b>\n";
		$text .= "💰 Цена: <b>".urldecode($subsubcat['amount']) . "</b> руб.\n";
		$text .= "[Для выбора нажмите 👉 /buy" . $subsubcat['id'] . "]\n";
		$text .= "➖➖➖➖➖➖➖➖➖➖\n";
	}
	$text .= "\nЕсли Вы выбрали не тот район, нажмите 👉 /city{$cat['id']} для того, чтобы вернуться назад в город <b>".urldecode($cat['name'])."</b> и выбрать нужный район.
Либо нажмите 👉 /start для того, чтобы вернуться к выбору города.";
	$bot->sendMessage($chat, $text, 'html');


}	

exit;
?>