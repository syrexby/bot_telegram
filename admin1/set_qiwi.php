<?php
ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../classes/PDO.php';

$My_Class->title("Настройки оплаты");



if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
header("Location: /admin");		
exit;
}
	
$row = DB::$the->query("SELECT * FROM `sel_set_qiwi` ");
$set = $row->fetch(PDO::FETCH_ASSOC);


if(isset($_GET['ok']) and isset($_POST['submit'])) {

if($_POST['number1'] != "" and $_POST['password1'] != "") {
$number1=$_POST['number1'];
$password1=$_POST['password1'];

$number2=$_POST['number2'];
$password2=$_POST['password2'];

$number3=$_POST['number3'];
$password3=$_POST['password3'];

$number4=$_POST['number4'];
$password4=$_POST['password4'];

$number5=$_POST['number5'];
$password5=$_POST['password5'];

DB::$the->prepare("UPDATE sel_set_qiwi SET number=? WHERE id=?")->execute(array("$number1", "1")); 
DB::$the->prepare("UPDATE sel_set_qiwi SET password=? WHERE id=?")->execute(array("$password1", "1")); 

DB::$the->prepare("UPDATE sel_set_qiwi SET number=? WHERE id=?")->execute(array("$number2", "2")); 
DB::$the->prepare("UPDATE sel_set_qiwi SET password=? WHERE id=?")->execute(array("$password2", "2")); 

DB::$the->prepare("UPDATE sel_set_qiwi SET number=? WHERE id=?")->execute(array("$number3", "3")); 
DB::$the->prepare("UPDATE sel_set_qiwi SET password=? WHERE id=?")->execute(array("$password3", "3")); 

DB::$the->prepare("UPDATE sel_set_qiwi SET number=? WHERE id=?")->execute(array("$number4", "4")); 
DB::$the->prepare("UPDATE sel_set_qiwi SET password=? WHERE id=?")->execute(array("$password4", "4")); 

DB::$the->prepare("UPDATE sel_set_qiwi SET number=? WHERE id=?")->execute(array("$number5", "5")); 
DB::$the->prepare("UPDATE sel_set_qiwi SET password=? WHERE id=?")->execute(array("$password5", "5")); 

DB::$the->prepare("UPDATE sel_set_qiwi SET active=? ")->execute(array("0")); 

DB::$the->prepare("UPDATE sel_set_qiwi SET active=? WHERE id=?")->execute(array("1", $_POST['active'])); 


header("Location: ?");
}
else
{
?>
<div class="alert alert-danger"> Первый номер и пароль обязательный к заполнению!</div>
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
  <li><a href="/admin">Админ-панель</a></li>
  <li class="active">Настройки оплаты</li>
</ol>

<form method="POST" action="?ok"><div class="form-group col-sm-8">

<?
$query = DB::$the->query("SELECT * FROM `sel_set_qiwi` order by `id` ");
while($res = $query->fetch()) {
	
$act = DB::$the->query("SELECT active FROM `sel_set_qiwi` where `id` = '".$res['id']."' ");
$act = $act->fetch(PDO::FETCH_ASSOC);
	
?>	
  <div class="panel panel-default">
  <div class="panel-heading"><input type="radio" name="active" value="<?=$res['id']?>" <? if($act['active'] == '1') echo 'checked'; ?>> QIWI № <?=$res['id']?>: 
  <? if($res['active'] == "1") echo '<font color="green">ПРИНИМАЕТ ОПЛАТУ</font>'; ?></div>
  <div class="panel-body">		
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Номер </span>
    <input type="text" class="form-control" name="number<?=$res['id']?>" value="<?=$res["number"]?>">
    </div>
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Пароль</span>
    <input type="password" class="form-control" name="password<?=$res['id']?>" value="<?=$res["password"]?>">
    </div>	

	<?
	if($res["number"] != ""){
	$lim = DB::$the->query("SELECT SUM(dAmount) FROM `sel_qiwi` where `iAccount` = ".$res["number"]);
    $lim = $lim->fetchAll();
	
	if($lim[0]["sum(dAmount)"] == '') $sum = 0; else $sum = $lim[0]["sum(dAmount)"];
	//echo $sum;
	?>
<div class="input-group input-group-lg">	
	<span class="input-group-addon">Получено (руб)</span>
    <input type="text" class="form-control" name="limit<?=$res['id']?>" value="<?=$sum;?>"disabled>
</div>
<? } ?>
    </div></div><hr>	
<? } ?>

	
<button type="submit" name="submit" data-loading-text="Сохраняю" class="btn btn-danger btn-lg btn-block">Сохранить</button></form>
</div>
<?


$My_Class->foot("© Админ-панель");
?>