<?php 
session_start();
ob_start();
require '../style/head.php';
require '../classes/PDO.php';
require '../classes/My_Class.php';

$My_Class->title("Админ-панель");

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {

if (isset($_GET['get_save']) and isset($_POST['submit'])) {

if(($_POST['captcha']) ==  $_SESSION['captcha'])
{	
if(($_POST['secretkey']) == $password) 
{
$time = time();
setcookie('secretkey', $secretkey, time()+86400, '/');
setcookie('time', md5($time), time()+86400, '/');
setcookie('password', base64_encode($time), time()+86400, '/');

header("Location: index.php"); 
}
else
{
echo '<div class="alert alert-danger">Неверный секретный ключ!</div>';
}
}
else
{
echo '<div class="alert alert-danger">Неверный код с картинки!</div>';
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



<form method="POST" action="?get_save">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
    <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span> </span>
    <input type="password" class="form-control" name="secretkey" placeholder="Secret Key"></div>
<img src="captcha.php" alt="защитный код">
	<div class="input-group input-group-lg">
    <span class="input-group-addon"><span class="glyphicon glyphicon-qrcode"></span> </span>
    <input type="password" class="form-control" name="captcha" placeholder="Код с картинки"></div>
    <br />
    <button type="submit" name="submit" class="btn btn-danger btn-lg btn-block" data-loading-text="Идет проверка данных">ВОЙТИ</button></form>
</div>


<? 
}
else
{

?>


<div class="list-group">
<a class="list-group-item" href="category.php">
<span class="glyphicon glyphicon-ok"></span> Управление ключами
</a>
</div>

<div class="list-group">
<a class="list-group-item" href="keys.php">
<span class="glyphicon glyphicon-check"></span> Списки ключей
</a>
</div>

<div class="list-group">
<a class="list-group-item" href="set_bot.php">
<span class="glyphicon glyphicon-wrench"></span> Настройки бота
</a>
</div>

<div class="list-group">
<a class="list-group-item" href="set_qiwi.php">
<span class="glyphicon glyphicon-shopping-cart"></span> Настройки оплаты
</a>
</div>

<div class="list-group">
<a class="list-group-item" href="users.php">
<span class="glyphicon glyphicon-user"></span> Пользователи
</a>
</div>

<div class="list-group">
<a class="list-group-item" href="rassylka.php">
<span class="glyphicon glyphicon-volume-up"></span> Рассылка сообщений
</a>
</div>

<div class="list-group">
<a class="list-group-item" href="?exit">
<span class="glyphicon glyphicon-eye-close"></span> ВЫХОД
</a>
</div>
<?

if(isset($_GET['exit'])) {	
setcookie('secretkey', $secretkey, time()-86400, '/');	
header("Location: index.php");
}	

}

$My_Class->foot();
?>