<?php
$user = DB::$the->query("SELECT ban,id_key,cat FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);
$row = DB::$the->query("SELECT name, id, amount, id_subcat FROM `sel_subsubcategory` WHERE `id` = '".$id."' ");
$subsubcat = $row->fetch(PDO::FETCH_ASSOC);

// Берем информацию о категории
$row = DB::$the->query("SELECT id_cat FROM `sel_subcategory` WHERE `id` = '".$subsubcat['id_subcat']."' ");
$subcat = $row->fetch(PDO::FETCH_ASSOC);

// Берем информацию о категории
$row = DB::$the->query("SELECT id, name FROM `sel_category` WHERE `id` = '".$subcat['id_cat']."' ");
$cat = $row->fetch(PDO::FETCH_ASSOC);

$subcats = DB::$the->query("SELECT * FROM `sel_subcategory` where `id_cat` = '".$cat['id']."'");
$subcats = $subcats->fetchAll();

$tovar = urldecode($subsubcat['name']);
$stoimost = $subsubcat['amount'];
$gorod = urldecode($cat['name']);

$text = "";
$text .= '🏠 <b>' . urldecode($cat['name']) . "</b> \n\n";
$text .= "🎁 <b>{$tovar}</b>, 🎁\n";
$text .= "💰 Цена: <b>{$stoimost} руб.</b> 💰\n";
$text .= "\nВыберите район:\n";
$text .= "➖➖➖➖➖➖➖➖➖➖\n";
foreach ($subcats as $subcat){
	$text .= "🏃 район <b>".urldecode($subcat['name']) . "</b>\n";
	$text .= "[Для выбора нажмите 👉 /buy" . $subsubcat['id'] . "_". $subcat['id'] ."]\n";
	$text .= "➖➖➖➖➖➖➖➖➖➖\n";
}
$text .= "\nЕсли Вы выбрали не тот товар, нажмите 👉 /city{$cat['id']} для того, чтобы вернуться назад в город <b>{$gorod}</b> и выбрать нужный товар.
Либо нажмите 👉 /start для того, чтобы вернуться к выбору города.";
$bot->sendMessage($chat, $text, 'html');



exit;
?>