<?php
ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../bot1/classes/PDO.php';

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
header("Location: /admin1");		
exit;
}

$row = DB::$the->query("SELECT name, id FROM `sel_category` WHERE `id` = '".intval($_GET['category'])."'");
$cat = $row->fetch(PDO::FETCH_ASSOC);
$row = DB::$the->query("SELECT name, id FROM `sel_subcategory` WHERE `id` = '".intval($_GET['subcategory'])."'");
$subcat = $row->fetch(PDO::FETCH_ASSOC);
$My_Class->title("Категория: ".urldecode($cat['name']));

if(isset($_GET['category'])){
$header = DB::$the->query("SELECT id FROM `sel_category` WHERE `id` = '".intval($_GET['category'])."' ");
$header = $header->fetchAll();
if(count($header) == 0){
header("Location: /admin1");		
exit;
}}	

if(isset($_GET['subcategory'])){
$header = DB::$the->query("SELECT id FROM `sel_subcategory` WHERE `id` = '".intval($_GET['subcategory'])."' ");
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
if(isset($_GET['subcategory'])){$subcategory = abs(intval($_GET['subcategory']));}else{$subcategory = '0';}

switch ($cmd){
case 'create':

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li class="active">Создание раздела</li>
</ol>
<?

if(isset($_POST['create'])) {

if($_POST['subcat'] != "" and $_POST['amount'] != "") {
$subcat=urlencode(trim($_POST['subcat']));
$amount=$_POST['amount'];

$cat_m = DB::$the->query("SELECT mesto FROM `sel_subcategory` WHERE `id_cat` = {$category} order by `mesto` DESC limit 1 ");
$cat_m = $cat_m->fetch(PDO::FETCH_ASSOC);
$mesto = $cat_m['mesto']+1;

$params = array('name' => $subcat, 'amount' => $amount, 'id_cat' => $category, 'time' => time(), 'mesto' => $mesto);  
 
$q= DB::$the->prepare("INSERT INTO `sel_subcategory` (name, amount, id_cat, time, mesto) VALUES (:name,:amount, :id_cat, :time, :mesto)");  
$q->execute($params);

header("Location: ?category=$category");
}
else
{
echo '<div class="alert alert-danger">Не заполнены все поля!</div>';
}
}

?>
<form action="?cmd=create&category=<?=$category?>" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span> </span>
<input type="text" placeholder="Название раздела" class="form-control" name="subcat">
</div><br />
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-usd"></span> </span>
<input type="text" placeholder="Цена ключей" class="form-control" name="amount">
</div><br />
<button type="submit" name="create" class="btn btn-danger btn-lg btn-block" data-loading-text="Создаю">Создать</button></form>
</div>
<?

break;
 	
case 'edit':
?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li class="active">Редактирование раздела</li>
</ol>
<?	
$row = DB::$the->query("SELECT * FROM `sel_subcategory` WHERE `id` = {$subcategory} and `id_cat` = {$category}");
$subcat = $row->fetch(PDO::FETCH_ASSOC);

// Редактирование раздел
if(isset($_POST['edit'])) {

if($_POST['name'] != "" and $_POST['amount'] != "") {
$name=urlencode(trim($_POST['name']));
$amount=$_POST['amount'];
$mesto=intval($_POST['mesto']);

DB::$the->prepare("UPDATE sel_subcategory SET name=? WHERE id=? ")->execute(array("$name", $subcategory)); 
DB::$the->prepare("UPDATE sel_subcategory SET amount=? WHERE id=? ")->execute(array("$amount", $subcategory)); 
DB::$the->prepare("UPDATE sel_subcategory SET mesto=? WHERE id=? ")->execute(array("$mesto", $subcategory)); 

header("Location: ?category=$category");
}
else
{
echo '<div class="alert alert-danger">Не заполнены все поля!</div>';
}
}


?>
<form action="?cmd=edit&category=<?=$category?>&subcategory=<?=$subcategory?>" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span> </span>
<input type="text" placeholder="<?=urldecode($subcat['name'])?>" class="form-control" name="name" value="<?=urldecode($subcat['name'])?>">
</div><br />
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-usd"></span> </span>
<input type="text" placeholder="<?=$subcat['amount']?>" class="form-control" name="amount" value="<?=$subcat['amount']?>">
</div><br />
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-flag"></span> </span>
<input type="text" placeholder="<?=$subcat['mesto']?>" class="form-control" name="mesto" value="<?=$subcat['mesto']?>">
</div><br />
<button type="submit" name="edit" class="btn btn-danger btn-lg btn-block" data-loading-text="Изменяю">Изменить</button>
</div></form>
<?

	
break;

case 'delete':	
$row = DB::$the->query("SELECT name FROM `sel_subcategory` WHERE `id` = {$subcategory} and `id_cat` = {$category}");
$subcat = $row->fetch(PDO::FETCH_ASSOC);
?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="subcategory.php?category=<?=$category;?>"><?=urldecode($cat['name']);?></a></li>
  <li class="active">Удаление раздела: <b><?=$subcat['name'];?></b></li>
</ol>
<div class="alert alert-danger">Будут удалены все ключи из данного раздела!</div>

<div class="btn-group">
  <button type="button" class="btn btn-danger dropdown-toggle" data-loading-text="Думаем" data-toggle="dropdown">Вы уверены? <span class="caret"></span></button>
  <ul class="dropdown-menu" role="menu">
    <li><a href="?cmd=delete&category=<?=$category;?>&subcategory=<?=$subcategory;?>&ok">Да, удалить</a></li>
    <li class="divider"></li>
    <li><a href="?category=<?=$category;?>">Нет, отменить</a></li>
  </ul>
</div><br /><br />
<?


if(isset($_GET['ok'])) {
DB::$the->query("DELETE FROM `sel_subcategory` WHERE `id` = {$subcategory} ");
DB::$the->query("DELETE FROM `sel_keys` WHERE `id_subcat` = {$subcategory} ");

header("Location: ?category=$category");
}

break;
	
default:

?>
<ol class="breadcrumb">
  <li><a href="/admin">Админ-панель</a></li>
  <li><a href="category.php">Категории</a></li>
  <li><a href="subcategory.php?category=<?=$cat['id']?>"><?=urldecode($cat['name']);?></a></li>
    <li class="active"><?=urldecode($subcat['name']);?></li>
</ol>

<div class="list-group">
<a class="list-group-item" href="?cmd=create&category=<?=$category;?>">
<span class="glyphicon glyphicon-plus-sign"></span> Создать раздел
</a>
</div>
<?


$total = DB::$the->query("SELECT * FROM `sel_subsubcategory` where `id_subcat` = {$subcategory} ");
$total = $total->fetchAll();
$max = 5;
$pages = $My_Class->k_page(count($total),$max);
$page = $My_Class->page($pages);
$start=($max*$page)-$max;

if(count($total) == 0){
echo '<div class="alert alert-danger">В данной категории нет разделов!</div>';
}	

echo '<div class="list-group">';
$query = DB::$the->query("SELECT * FROM `sel_subsubcategory` where `id_subcat` = {$subcategory} order by `mesto` LIMIT $start, $max");
while($cat = $query->fetch()) {
$total = DB::$the->query("SELECT id_subsubcat FROM `sel_keys` WHERE `id_subsubcat` = {$cat[id]} ");
$total = $total->fetchAll();
	
echo '<span class="list-group-item"><font color="green">['.$cat['mesto'].'] </font>
<a href="key.php?category='.$category.'&subsubcategory='.$cat['id'].'"><b>'.urldecode($cat['name']).'</b></a> ('.count($total).')';
echo '<a href="?cmd=edit&category='.$category.'&subsubcategory='.$cat['id'].'"> <span class="badge pull-right"><span class="glyphicon glyphicon-pencil"></span> </a>';
echo '<a href="?cmd=delete&category='.$category.'&subsubcategory='.$cat['id'].'"> <span class="badge pull-right"><span class="glyphicon glyphicon-remove"></span> </a>';
echo '</span>';
}
echo '</div>';

if ($pages>1) $My_Class->str('?category='.$category.'&',$pages,$page); 

}

$My_Class->foot();
?>