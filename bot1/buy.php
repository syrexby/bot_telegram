<?php
$user = DB::$the->query("SELECT ban,id_key,cat FROM `sel_users` WHERE `chat` = {$chat} ");
$user = $user->fetch(PDO::FETCH_ASSOC);

$row = DB::$the->query("SELECT name, id FROM `sel_category` WHERE `id` = '".$subcat['id_cat']."' ");
$cat = $row->fetch(PDO::FETCH_ASSOC);

// Проверяем наличие ключей
$total = DB::$the->query("SELECT id FROM `sel_keys` where `id_subsubcat` = '".$id_subsubcat."' and `sale` = '0' and `block` = '0' ");
$total = $total->fetchAll();
if(count($total) == 0) // Если пусто, вызываем ошибку
{
	$text .= "Товара нет в наличии! \n\n";
	$text .= "Нажмите 👉 /buy{$subsubcat['id']} для того, чтобы вернуться к выбору района.
Либо нажмите 👉 /start для того, чтобы вернуться к выбору города.";
// Отправляем текст
$bot->sendMessage($chat, $text, 'html');
}
else // Иначе выводим результат
{
	$tovar = urldecode($subsubcat['name']);
	$stoimost = $subsubcat['amount'];
	$gorod = urldecode($cat['name']);
	$rajon = urldecode($subcat['name']);
	$text .= "<b>Вы приобретаете</b>
	🎁 <b>{$tovar}</b> 🎁
	💰 Стоимость <b>{$stoimost} руб.</b> 💰
	🏠 город <b>{$gorod}</b>
	🏠 район <b>{$rajon}</b>
	( для смены района нажмите
	👉 /buy{$subsubcat['id']} ) \n";
	$text .= "\ ➖➖➖➖➖➖➖➖➖➖  \n";
	
	if(mb_substr($message, -4) == 'qiwi'){
		$clear = DB::$the->query("SELECT block_user FROM `sel_keys` where `block_user` = '".$chat."' ");
		$clear = $clear->fetchAll();

		if(count($clear) != 0){
			DB::$the->prepare("UPDATE sel_keys SET block=? WHERE block_user=? ")->execute(array("0", $chat));
			DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE block_user=? ")->execute(array('0', $chat));
			DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE block_user=? ")->execute(array('0', $chat));
		}

		// Получаем информацию о ключе
		$key = DB::$the->query("SELECT id,code,id_subsubcat FROM `sel_keys` where `id_subsubcat` = '".$subsubcat['id']."' and `sale` = '0' and `block` = '0' order by rand() limit 1");
		$key = $key->fetch(PDO::FETCH_ASSOC);


		DB::$the->prepare("UPDATE sel_keys SET block=? WHERE id=? ")->execute(array("1", $key['id']));
		DB::$the->prepare("UPDATE sel_keys SET block_user=? WHERE id=? ")->execute(array($chat, $key['id']));
		DB::$the->prepare("UPDATE sel_keys SET block_time=? WHERE id=? ")->execute(array(time(), $key['id']));

		DB::$the->prepare("UPDATE sel_users SET id_key=? WHERE chat=? ")->execute(array($key['id'], $chat));
		DB::$the->prepare("UPDATE sel_users SET verification=? WHERE chat=? ")->execute(array(time(), $chat));
		$set_qiwi = DB::$the->query("SELECT number FROM `sel_set_qiwi` WHERE `active` = '1' ");
		$set_qiwi = $set_qiwi->fetch(PDO::FETCH_ASSOC);

		DB::$the->prepare("UPDATE sel_users SET pay_number=? WHERE chat=? ")->execute(array($set_qiwi['number'], $chat));
        
        
		$text .= "Для приобретения выбранного товара,
оплатите <b>{$stoimost} рублей</b> на номер QIWI:
<b>{$set_qiwi['number']}</b>
комментарий к платежу
<b>{$key['code']}</b>\n";
		
		$text .= "Внимание! Обязательно укажите этот комментарий при оплате, иначе оплата не будет засчитана в автоматическом режиме.\n";
		$text .= "После оплаты нажмите
👉 /check{$chat}_{$key['code']}, чтобы получить адрес. Чтобы отказаться от заказа, нажмите 👉 /start";
	}else {
		$text .= "<b>Выберите способ оплаты:</b>\n";
		$text .= "<b>Qiwi Walet</b>
Для выбора нажмите 👉 /buy{$subsubcat['id']}_{$subcat['id']}_qiwi\n";
	     $text .= "➖➖➖➖➖➖➖➖➖➖\n";
		$text .= "<b>Bitcoin</b>
Для выбора нажмите 👉 /buy{$subsubcat['id']}_{$subcat['id']}_btc\n";
	     $text .= "➖➖➖➖➖➖➖➖➖➖\n";
     }
	$bot->sendMessage($chat, $text, 'html');


}	

exit;
?>