<?php
ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../classes/PDO.php';
require '../classes/Curl.php';

$curl = new Curl();

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
header("Location: /admin");		
exit;
}

$My_Class->title("Рассылка сообщений");
	
$set_bot = DB::$the->query("SELECT token FROM `sel_set_bot` ");
$set_bot = $set_bot->fetch(PDO::FETCH_ASSOC);
$token		= $set_bot['token']; // токен бота

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li class="active">Рассылка сообщений</li>
</ol>
<?

if(isset($_GET['ok']) and isset($_POST['submit'])) {

if($_POST['text'] != "") {
$text=htmlentities($_POST['text']);

$query = DB::$the->query("SELECT * FROM `sel_users` group by `chat` ");
while($user = $query->fetch()) {
$curl->post('https://api.telegram.org/bot'.$token.'/sendMessage',array(
	'chat_id' => $user['chat'],
	'text' => $text,
	)); 	
}	

header("Location: ?super");
}
else
{
?>
<div class="alert alert-danger"> Пустой текст!</div>
<?
}
}

?>
<script type="text/javascript">  
 $(function() { 
    $(".btn").click(function(){
        $(this).button('loading').delay(3000).queue(function() {
            $(this).button('reset');
            $(this).dequeue();
        });        
    });
});  
</script>

<?  
$total = DB::$the->query("SELECT * FROM `sel_users` group by `chat` ");
$total = $total->fetchAll();
if(isset($_GET['super'])) {
?>
<div class="alert alert-success"> Вашу рассылку получило <b><?=count($total)?></b> пользователей</div>

<? } ?>

<div class="alert alert-default"> Вашу рассылку получит <b><?=count($total)?></b> пользователей</div>

<form method="POST" action="?ok"><div class="form-group col-sm-8">

    Текст рассылки: <br />
    <textarea class="form-control" name="text"></textarea>
<br />
<button type="submit" name="submit" data-loading-text="Отправляю" class="btn btn-danger btn-lg btn-block">Отправить</button></form>
</div>
<?

$My_Class->foot();
?>