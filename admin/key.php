<?php
ob_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require '../style/head.php';
require '../classes/My_Class.php';
require '../classes/PDO.php';

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
header("Location: /admin");		
exit;
}

$row = DB::$the->query("SELECT name FROM `sel_category` WHERE `id` = '".intval($_GET['category'])."'");
$cat = $row->fetch(PDO::FETCH_ASSOC);

$row = DB::$the->query("SELECT name FROM `sel_subcategory` WHERE `id` = '".intval($_GET['subcategory'])."'");
$subcat = $row->fetch(PDO::FETCH_ASSOC);

$My_Class->title("Подкатегория: ".urldecode($subcat['name']));


if(isset($_GET['category'])){
$header = DB::$the->query("SELECT id FROM `sel_category` WHERE `id` = '".intval($_GET['category'])."' ");
$header = $header->fetchAll();
if(count($header) == 0){
header("Location: /admin");		
exit;
}}	

if(isset($_GET['subcategory'])){
$header = DB::$the->query("SELECT id FROM `sel_subcategory` WHERE `id` = '".intval($_GET['subcategory'])."' ");
$header = $header->fetchAll();
if(count($header) == 0){
header("Location: /admin");		
exit;
}}	
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

if(isset($_GET['cmd'])){$cmd = htmlspecialchars($_GET['cmd']);}else{$cmd = '0';}

if(isset($_GET['category'])){$category = abs(intval($_GET['category']));}else{$category = '0';}
if(isset($_GET['subcategory'])){$subcategory = abs(intval($_GET['subcategory']));}else{$subcategory = '0';}
if(isset($_GET['key'])){$key = abs(intval($_GET['key']));}else{$key = '0';}

switch ($cmd){
case 'create':

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li><a href="key.php?category=<?=$category;?>&subcategory=<?=$subcategory;?>"><?=urldecode($subcat['name']);?></a></li>
  <li class="active">Добавление ключа</li>
</ol>
<?

if(isset($_POST['create'])) {
  
if($_POST['key'] != "") {
$code=$_POST['key'];
$params = array('code' => $code, 'id_cat' => $category, 'id_subcat' => $subcategory, 'time' => time(), 'sale' => 0);

$q= DB::$the->prepare("INSERT INTO `sel_keys` (code, id_cat, id_subcat, time, sale) VALUES (:code, :id_cat, :id_subcat, :time, :sale)");  
$q->execute($params);

header("Location: ?category=$category&subcategory=$subcategory");
}
else
{
echo '<div class="alert alert-danger">Не введен ключ!</div>';
}
}

echo '<form action="?cmd=create&category='.$category.'&subcategory='.$subcategory.'" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-qrcode"></span> </span>
<input type="text" placeholder="Ключ" class="form-control" name="key">
</div><br>
<button type="submit" name="create" class="btn btn-danger btn-lg btn-block" data-loading-text="Добавляю">Добавить</button></form></div>';

break;
 
case 'txt':

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li><a href="key.php?category=<?=$category;?>&subcategory=<?=$subcategory;?>"><?=urldecode($subcat['name']);?></a></li>
  <li class="active">Добавление ключей</li>
</ol>
<?

if(isset($_POST['ok'])) {

if($_FILES['txt']['type'] == 'text/plain') {

$tmp = $_FILES['txt']['tmp_name'];

move_uploaded_file($tmp, 'txt/'.$subcategory.'.txt');


$file = file('txt/'.$subcategory.'.txt'); //Открываем файл. 
$count = count($file); //Узнаём сколько строк. 

for($i = 0; $i < $count; $i++){ // читаем все строки. 
echo $file[$i]; //Выводим по 1. 

$params = array('code' => $file[$i], 'id_cat' => $category, 'id_subcat' => $subcategory, 'time' => time(), 'sale' => 0);  
 
$q= DB::$the->prepare("INSERT INTO `sel_keys` (code, id_cat, id_subcat, time, sale) VALUES (:code, :id_cat, :id_subcat, :time, :sale)");  
$q->execute($params);

} 

unlink('txt/'.$subcategory.'.txt');



header("Location: ?category=$category&subcategory=$subcategory");
}
else
{
echo '<div class="alert alert-danger">Выгрузите txt файл!</div>';
}
}


echo '<form action="?cmd=txt&category='.$category.'&subcategory='.$subcategory.'" method="POST" enctype="multipart/form-data">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-qrcode"></span> </span>
<input type="file" placeholder="Ключ" class="form-control" name="txt">
</div><br>
<button type="submit" name="ok" class="btn btn-danger btn-lg btn-block" data-loading-text="Выгружаю">Выгрузить</button></form></div>';

break;
 
case 'edit':
?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li><a href="key.php?category=<?=$category;?>&subcategory=<?=$subcategory;?>"><?=urldecode($subcat['name']);?></a></li>
  <li class="active">Редактирование ключа</li>
</ol>
<?	
$key_edit = DB::$the->query("SELECT code FROM `sel_keys` WHERE `id` = {$key} and `id_subcat` = {$subcategory}");
$key_edit = $key_edit->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['edit'])) {

if($_POST['key'] != "") {
$code=$_POST['key'];

DB::$the->prepare("UPDATE sel_keys SET code=? WHERE id=? ")->execute(array("$code", $key)); 

header("Location: ?category=$category&subcategory=$subcategory");
}
else
{
echo '<div class="alert alert-danger">Не введен ключ!</div>';
}
}


echo '<form action="?cmd=edit&category='.$category.'&subcategory='.$subcategory.'&key='.$key.'" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-qrcode"></span> </span>
<input type="text" placeholder="'.$key_edit['code'].'" class="form-control" name="key" value="'.$key_edit['code'].'">
</div><br>
<button type="submit" name="edit" class="btn btn-danger btn-lg btn-block" data-loading-text="Изменить">Изменяю</button></form></div>';

	
break;

case 'delete':	
$key_del = DB::$the->query("SELECT code FROM `sel_keys` WHERE `id` = {$key} and `id_subcat` = {$subcategory}");
$key_del = $key_del->fetch(PDO::FETCH_ASSOC);
?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li><a href="key.php?category=<?=$category;?>&subcategory=<?=$subcategory;?>"><?=urldecode($subcat['name']);?></a></li>
  <li class="active">Удаление ключа: <b><?=$key_del['code'];?></b></li>
</ol>
<div class="alert alert-danger">Ключ будет удален навсегда!</div>

<div class="btn-group">
  <button type="button" class="btn btn-danger dropdown-toggle" data-loading-text="Думаем" data-toggle="dropdown">Вы уверены? <span class="caret"></span></button>
  <ul class="dropdown-menu" role="menu">
    <li><a href="?cmd=delete&category=<?=$category;?>&subcategory=<?=$subcategory;?>&key=<?=$key;?>&ok">Да, удалить</a></li>
    <li class="divider"></li>
    <li><a href="?category=<?=$category;?>&subcategory=<?=$subcategory;?>">Нет, отменить</a></li>
  </ul>
</div><br /><br />
<?


if(isset($_GET['ok'])) {
DB::$the->query("DELETE FROM `sel_keys` WHERE `id` = {$key} ");

header("Location: ?category=$category&subcategory=$subcategory");
}

break;

case 'remove_sale':	

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li><a href="key.php?category=<?=$category;?>&subcategory=<?=$subcategory;?>"><?=urldecode($subcat['name']);?></a></li>
  <li class="active">Удаление всех не проданных ключей</li>
</ol>
<div class="alert alert-danger">Будут удалены все не проданные ключи!</div>

<div class="btn-group">
  <button type="button" class="btn btn-danger dropdown-toggle" data-loading-text="Думаем" data-toggle="dropdown">Вы уверены? <span class="caret"></span></button>
  <ul class="dropdown-menu" role="menu">
    <li><a href="?cmd=remove_sale&category=<?=$category;?>&subcategory=<?=$subcategory;?>&ok">Да, удалить все не проданные ключи</a></li>
    <li class="divider"></li>
    <li><a href="key.php?category=<?=$category;?>&subcategory=<?=$subcategory;?>">Нет, отменить</a></li>
  </ul>
</div><br /><br />

<?

if(isset($_GET['ok'])) {
DB::$the->query("DELETE FROM `sel_keys` WHERE `id_cat` = {$category} and `id_subcat` = {$subcategory} and `sale` = '0' ");

header("Location: key.php?category=category&subcategory=$subcategory");
}

break;
	
default:

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li class="active"><?=urldecode($subcat['name']);?></li>
</ol>

<div class="list-group">
<a class="list-group-item" href="?cmd=create&category=<?=$category;?>&subcategory=<?=$subcategory;?>">
<span class="glyphicon glyphicon-plus-sign"></span> Добавить 1 ключ
</a>
</div>
<div class="list-group">
<a class="list-group-item" href="?cmd=txt&category=<?=$category;?>&subcategory=<?=$subcategory;?>">
<span class="glyphicon glyphicon-plus-sign"></span> Добавить много ключей
</a>
</div>
<?

$total = DB::$the->query("SELECT * FROM `sel_keys` where `id_cat` = {$category} and `id_subcat` = {$subcategory} ");
$total = $total->fetchAll();
$max = 5;
$pages = $My_Class->k_page(count($total),$max);
$page = $My_Class->page($pages);
$start=($max*$page)-$max;

if(count($total) == 0){
echo '<div class="alert alert-danger">В данной подкатегории нет ключей!</div>';
}	

echo '<div class="list-group">';
$query = DB::$the->query("SELECT * FROM `sel_keys` where `id_cat` = {$category} and `id_subcat` = {$subcategory} order by rand() LIMIT $start, $max");
while($key = $query->fetch()) {
if($key['sale'] == 1) {
$sales = '<font color="red">[ПРОДАН]</font>';
}
else $sales = null;
	
echo '<span class="list-group-item"> <b>'.$key['code'].'</b> '.$sales;
echo '<a href="?cmd=edit&category='.$category.'&subcategory='.$subcategory.'&key='.$key['id'].'"> <span class="badge pull-right"><span class="glyphicon glyphicon-pencil"></span> </a>';
echo '<a href="?cmd=delete&category='.$category.'&subcategory='.$subcategory.'&key='.$key['id'].'"> <span class="badge pull-right"><span class="glyphicon glyphicon-remove"></span> </a>';
echo '</span>';
}
echo '</div>';

if ($pages>1) $My_Class->str('?category='.$category.'&subcategory='.$subcategory.'&',$pages,$page); 
?>
<div class="list-group">
<a class="list-group-item" href="key.php?cmd=remove_sale&category=<?=$category;?>&subcategory=<?=$subcategory;?>">
<span class="glyphicon glyphicon-remove"></span> Удалить все не проданные ключи
</a>
</div>
<?
}

$My_Class->foot();
ob_end_flush();
?>