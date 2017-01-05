<?
class My_Class{



public function title($str) {
?>
<title><?=$str;?></title>
<div class="well">
<nav class="navbar navbar-inverse">
<div class="container-fluid">
<div class="navbar-header">
<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" > Админ-панель </a>
</div>
<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
<ul class="nav navbar-nav">
<li><a href="category.php">Категории</a></li>
<li><a href="keys.php">Ключи</a></li>
<li><a href="set_bot.php">Настройки</a></li>
<li><a href="set_qiwi.php">Оплата</a></li>
<li><a href="users.php">Пользователи</a></li>
<li><a href="add_info.php">Дополнительная информация</a></li>
</ul>
</li>
</div>
</div>
</nav>
</div>
<div class="panel panel-default">
  <!-- Default panel contents -->

  <div class="panel-body">
<br />
<?
}


	
public function foot() {

?><br />
</div>
<nav class="navbar navbar-inverse">
<div class="container-fluid">
<div class="navbar-header">
<a class="navbar-brand" href="/"> &copy; Админ-панель </a>
</div>
</div>
</nav>
</div>
</body>
<?

}



public function page($k_page=1)
{ 
	$page = 1;

	if (isset($_GET['page']))
	{
		if ($_GET['page'] == 'end')
			$page = intval($k_page);
			
		elseif(is_numeric($_GET['page'])) 
		$page = intval($_GET['page']);
	}

	if ($page < 1)$page = 1;

	if ($page > $k_page)
		$page = $k_page;
		
	return $page;
}

public function k_page($k_post = 0, $k_p_str = 10)
{ 
	if ($k_post != 0) 
	{
		$v_pages = ceil($k_post / $k_p_str);
		return $v_pages;
	}

	else return 1;
}

public function str($link = '?', $k_page = 1,$page = 1)
{ 
	if ($page < 1)
		$page = 1;

	echo '<div class="btn-group btn-group-justified">';

	
	if ($page != 1){
echo '<div class="btn-group"><a href="'.$link.'page=1" title="Начало"><button type="button" class="btn btn-default">Начало</button></a></div>';		
echo '<div class="btn-group"><a href="'.$link.'page='.($page-1).'" title="Назад"><button type="button" class="btn btn-default">Назад</button></a></div>';
	}else {
echo '<div class="btn-group"><button type="button" class="btn btn-default" disabled>Начало</button></div>';	
echo '<div class="btn-group"><button type="button" class="btn btn-default" disabled>Назад</button></div>';		
	}
	
	if ($k_page > 1)
echo '<div class="btn-group"><button type="button" id="showHideContent" class="btn btn-default" > <b>'.$page.'</b> из <b>'.$k_page.'</b></button></div>';	
	
		
	if ($k_page > 1 and $page!= $k_page)
echo '<div class="btn-group"><a href="'.$link.'page='.($page+1).'" title="Вперёд"><button type="button" class="btn btn-default">Вперёд</button></a></div>';
else
echo '<div class="btn-group"><button type="button" class="btn btn-default" disabled>Вперёд</button></div>';	
	if ($page!= $k_page)
echo '<div class="btn-group"><a href="'.$link.'page='.$k_page.'" title="Конец"><button type="button" class="btn btn-default">Конец</button></a></div>';
else
echo '<div class="btn-group"><button type="button" class="btn btn-default" disabled>Конец</button></div>';		
	
	echo '</div><br />';
	
	
	
echo '<div id="content" style="display:none;">';

	echo '<div class="btn-group btn-group-justified">';

	
?>

 <form method="POST" action="<?=$link?>&get"><div class="form-group col-sm-8"> 
    <div class="input-group">
      <input type="text" name="get" class="form-control">
      <span class="input-group-btn">
        <button type="submit" name="submit" class="btn btn-default">Перейти на страницу</button>
      </span>
  </div>

<?
if(isset($_GET['get']))	 {
header("Location: ".$link."page=".$_POST['get']."");		
}
	
	echo '</div><br />';
	
echo '</div>';	

?>
<script>
  $(document).ready(function(){

	    $("#showHideContent").click(function () {
			if ($("#content").is(":hidden")) {

				$("#content").show("slow");

			} else {

				$("#content").hide("slow");

			}
  return false;
});
});
</script>
<?
}

}



$My_Class = new My_Class;

?>

