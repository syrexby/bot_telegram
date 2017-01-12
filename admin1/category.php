<?php
ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../bot1/classes/PDO.php';

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
header("Location: /admin1");		
exit;
}

$My_Class->title("Категории");

if(isset($_GET['category'])){
$header = DB::$the->query("SELECT id FROM `sel_category` WHERE `id` = '".intval($_GET['category'])."' ");
$header = $header->fetchAll();
if(count($header) == 0){
header("Location: /admin1");		
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

switch ($cmd){
case 'create':
?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="category.php">Категории</a></li>
  <li class="active">Создание категории</li>
</ol>
<?
if(isset($_POST['create'])) {

if($_POST['cat'] != "") {
$cat=urlencode(trim($_POST['cat']));

$cat_m = DB::$the->query("SELECT mesto FROM `sel_category` order by `mesto` DESC limit 1 ");
$cat_m = $cat_m->fetch(PDO::FETCH_ASSOC);
$new_mesto = $cat_m['mesto']+1;

$params = array( 'name' => ''.$cat.'', 'time' => ''.time().'', 'mesto' => $new_mesto);  
 
$q= DB::$the->prepare("INSERT INTO `sel_category` (name, time, mesto) VALUES (:name, :time, :mesto)");  
$q->execute($params);

header("Location: category.php");
}
else
{
echo '<div class="alert alert-danger">Пустое название</div>';
}
}

echo '<form action="category.php?cmd=create" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
    <span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span> </span>
<input type="text" placeholder="Название категории" class="form-control" name="cat" value="">
</div>
<br />
<button type="submit" name="create" class="btn btn-danger btn-lg btn-block" data-loading-text="Создаю">Создать</button>
</div></form>';

break;
 	
case 'edit':	
?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="category.php">Категории</a></li>
  <li class="active">Редактирование категории</li>
</ol>
<?

$row = DB::$the->query("SELECT * FROM `sel_category` WHERE `id` = {$category} ");
$cat = $row->fetch(PDO::FETCH_ASSOC);

// Редактирование категории
if(isset($_POST['edit'])) {

if($_POST['name'] != "") {
$name=urlencode(trim($_POST['name']));
$mesto=intval($_POST['mesto']);

DB::$the->prepare("UPDATE sel_category SET name=? WHERE id=? ")->execute(array("$name", $category)); 
DB::$the->prepare("UPDATE sel_category SET mesto=? WHERE id=? ")->execute(array("$mesto", $category)); 

header("Location: category.php");
}
else
{
echo '<div class="alert alert-danger">Пустое название</div>';
}
}


echo '<form action="?cmd=edit&category='.$category.'" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span> </span>
<input type="text" placeholder="'.urldecode($cat['name']).'" class="form-control" name="name" value="'.urldecode($cat['name']).'">
</div><br />
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-flag"></span> </span>
<input type="text" placeholder="'.$cat['mesto'].'" class="form-control" name="mesto" value="'.$cat['mesto'].'">
</div><br />
<button type="submit" name="edit" class="btn btn-danger btn-lg btn-block" data-loading-text="Изменяю">Изменить</button>
</div></form>';

	
break;

case 'delete':	
$row = DB::$the->query("SELECT * FROM `sel_category` WHERE `id` = '".$category."'");
$cat = $row->fetch(PDO::FETCH_ASSOC);
?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="category.php">Категории</a></li>
  <li class="active">Удаление категории: <b><?=urldecode($cat['name']);?></b></li>
</ol>
<div class="alert alert-danger">Будут удалены все подкатегории данной категории и ключи из всех подкатегорий данной категории!</div>

<div class="btn-group">
  <button type="button" class="btn btn-danger dropdown-toggle" data-loading-text="Думаем" data-toggle="dropdown">Вы уверены? <span class="caret"></span></button>
  <ul class="dropdown-menu" role="menu">
    <li><a href="?cmd=delete&category=<?=$category;?>&ok">Да, удалить</a></li>
    <li class="divider"></li>
    <li><a href="category.php">Нет, отменить</a></li>
  </ul>
</div><br /><br />

<?

if(isset($_GET['ok'])) {
DB::$the->query("DELETE FROM `sel_category` WHERE `id` = '".$category."' ");
DB::$the->query("DELETE FROM `sel_subcategory` WHERE `id_cat` = '".$category."' ");
DB::$the->query("DELETE FROM `sel_keys` WHERE `id_cat` = '".$category."' ");

header("Location: category.php");
}

break;

case 'remove_sale':	

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="category.php">Категории</a></li>
  <li class="active">Удаление всех проданных ключей</b></li>
</ol>
<div class="alert alert-danger">Будут удалены все проданные ключи из всех категорий!</div>

<div class="btn-group">
  <button type="button" class="btn btn-danger dropdown-toggle" data-loading-text="Думаем" data-toggle="dropdown">Вы уверены? <span class="caret"></span></button>
  <ul class="dropdown-menu" role="menu">
    <li><a href="?cmd=remove_sale&ok">Да, удалить все проданные ключи</a></li>
    <li class="divider"></li>
    <li><a href="category.php">Нет, отменить</a></li>
  </ul>
</div><br /><br />

<?

if(isset($_GET['ok'])) {
DB::$the->query("DELETE FROM `sel_keys` WHERE `sale` = '1' ");

header("Location: category.php");
}

break;
	
default:

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li class="active">Категории</li>
</ol>

<div class="list-group">
<a class="list-group-item" href="?cmd=create">
<span class="glyphicon glyphicon-plus-sign"></span> Создать категорию
</a>
</div>
<?



$total = DB::$the->query("SELECT * FROM `sel_category` ");
$total = $total->fetchAll();
$max = 15;
$pages = $My_Class->k_page(count($total),$max);
$page = $My_Class->page($pages);
$start=($max*$page)-$max;

if(count($total) == 0){
echo '<div class="alert alert-danger">Нет категорий!</div>';
}	

echo '<div class="list-group">';
$query = DB::$the->query("SELECT * FROM `sel_category` order by `mesto` LIMIT $start, $max");
while($cat = $query->fetch()) {

$total = DB::$the->query("SELECT id_cat FROM `sel_subcategory` WHERE `id_cat` = '".$cat['id']."' ");
$total = $total->fetchAll();
	
echo '<span class="list-group-item"><font color="green">['.$cat['mesto'].']</font> 
<a href="subcategory.php?category='.$cat['id'].'"><b>'.urldecode($cat['name']).'</b></a> ('.count($total).')';
echo '<a href="?cmd=edit&category='.$cat['id'].'"> <span class="badge pull-right"><span class="glyphicon glyphicon-pencil"></span> </a>';
echo '<a href="?cmd=delete&category='.$cat['id'].'&hash='.md5($_cat['time']).'"> <span class="badge pull-right"><span class="glyphicon glyphicon-remove"></span> </a>';
echo '</span>';
}
echo '</div>';

if ($pages>1) $My_Class->str('?',$pages,$page); 

?>
<div class="list-group">
<a class="list-group-item" href="?cmd=remove_sale">
<span class="glyphicon glyphicon-remove"></span> Удалить все проданные ключи
</a>
</div>
<?
}

$My_Class->foot();
?>