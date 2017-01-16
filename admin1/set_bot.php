<?php
ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../bot1/classes/PDO.php';

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
header("Location: /admin1");		
exit;
}

$My_Class->title("Настройки бота");
	
$row = DB::$the->query("SELECT * FROM `sel_set_bot` ");
$set = $row->fetch(PDO::FETCH_ASSOC);


if(isset($_GET['ok']) and isset($_POST['submit'])) {

if($_POST['token'] != "" and $_POST['verification'] != "" and $_POST['block'] != "" and $_POST['on_off'] != "") {
$token=$_POST['token'];
$verification=$_POST['verification'];
$block=$_POST['block'];
$hello=urlencode($_POST['hello']);
$footer=$_POST['footer'];
$proxy=$_POST['proxy'];
$proxy_login=$_POST['proxy_login'];
$proxy_pass=$_POST['proxy_pass'];
$url=$_POST['url'];
$nomer1=$_POST['nomer1'];
$nomer2=$_POST['nomer2'];
$nomer3=$_POST['nomer3'];
$limits=$_POST['limits'];
$on_off=$_POST['on_off'];

DB::$the->prepare("UPDATE sel_set_bot SET token=? ")->execute(array("$token")); 
DB::$the->prepare("UPDATE sel_set_bot SET verification=? ")->execute(array("$verification")); 
DB::$the->prepare("UPDATE sel_set_bot SET block=? ")->execute(array("$block")); 
DB::$the->prepare("UPDATE sel_set_bot SET hello=? ")->execute(array("$hello")); 
DB::$the->prepare("UPDATE sel_set_bot SET footer=? ")->execute(array("$footer")); 
DB::$the->prepare("UPDATE sel_set_bot SET proxy=? ")->execute(array("$proxy")); 
DB::$the->prepare("UPDATE sel_set_bot SET proxy_login=? ")->execute(array("$proxy_login")); 
DB::$the->prepare("UPDATE sel_set_bot SET proxy_pass=? ")->execute(array("$proxy_pass")); 
DB::$the->prepare("UPDATE sel_set_bot SET url=? ")->execute(array("$url")); 
DB::$the->prepare("UPDATE sel_set_bot SET nomer1=? ")->execute(array("$nomer1")); 
DB::$the->prepare("UPDATE sel_set_bot SET nomer2=? ")->execute(array("$nomer2")); 
DB::$the->prepare("UPDATE sel_set_bot SET nomer3=? ")->execute(array("$nomer3")); 
DB::$the->prepare("UPDATE sel_set_bot SET limits=? ")->execute(array($limits)); 
DB::$the->prepare("UPDATE sel_set_bot SET on_off=? ")->execute(array("$on_off")); 

header("Location: ?");
}
else
{
?>
<div class="alert alert-danger"> Пустые данные!</div>
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

<ol class="breadcrumb">
  <li><a href="/admin1">Админ-панель</a></li>
  <li class="active">Настройки бота</li>
</ol>

<form method="POST" action="?ok"><div class="form-group col-sm-10">
<div class="input-group input-group-lg">
    <span class="input-group-addon">TOKEN</span>
    <input type="text" class="form-control" name="token" value="<?=$set['token'];?>">
	</div><br />
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Антифлуд (проверка оплаты)</span>
    <input type="text" class="form-control" name="verification" value="<?=$set['verification'];?>">
    </div><br />
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Время для оплаты ключа</span>
    <input type="text" class="form-control" name="block" value="<?=$set['block'];?>">
    </div><br />
<div class="input-group">	
	<span class="input-group-addon">Приветствие бота</span>
<textarea class="form-control" name="hello" rows="3"><?=urldecode($set['hello']);?></textarea>	
    </div><br />
<div class="input-group">	
	<span class="input-group-addon">Текст в самом низу</span>
<textarea class="form-control" name="footer" rows="3"><?=$set['footer'];?></textarea>	
    </div><br />	
<div class="input-group input-group-lg">	
	<span class="input-group-addon">IP прокси</span>
    <input type="text" class="form-control" name="proxy" value="<?=$set['proxy'];?>">
    </div><br />
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Логин прокси</span>
    <input type="text" class="form-control" name="proxy_login" value="<?=$set['proxy_login'];?>">
    </div><br />
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Пароль прокси</span>
    <input type="text" class="form-control" name="proxy_pass" value="<?=$set['proxy_pass'];?>">
    </div><br />	
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Адрес сайта (с https://)</span>
    <input type="text" class="form-control" name="url" value="<?=$set['url'];?>">
    </div><br />	
<div class="input-group input-group-lg">	
	<span class="input-group-addon">1. Номер QIWI резервный</span>
    <input type="text" class="form-control" name="nomer1" value="<?=$set['nomer1'];?>">
    </div><br />
<div class="input-group input-group-lg">	
	<span class="input-group-addon">2. Номер QIWI резервный</span>
    <input type="text" class="form-control" name="nomer2" value="<?=$set['nomer2'];?>">
    </div><br />
<div class="input-group input-group-lg">	
	<span class="input-group-addon">3. Номер QIWI резервный</span>
    <input type="text" class="form-control" name="nomer3" value="<?=$set['nomer3'];?>">
    </div><br />	
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Сумма перевода на резерв</span>
    <input type="text" class="form-control" name="limits" value="<?=$set['limits'];?>">
    </div><br />	
Состояние бота
<label class="radio"> 
  <input type="radio" name="on_off" class="form-control" value="on" <?if($set['on_off']=='on')echo'checked';?>>
   Включен
</label>
<hr>
<label class="radio">
  <input type="radio" name="on_off" class="form-control" value="off" <?if($set['on_off']=='off')echo'checked';?>>
  Отключен
</label>	
<hr>
<button type="submit" name="submit" data-loading-text="Сохраняю" class="btn btn-danger btn-lg btn-block">Сохранить</button></form>
</div>
<?

$My_Class->foot();
?>