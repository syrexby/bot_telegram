<?php
ob_start();
require '../style/head.php';
require '../classes/My_Class.php';
require '../bot1/classes/PDO.php';

if (!isset($_COOKIE['secretkey']) or $_COOKIE['secretkey'] != $secretkey) {
    header("Location: /admin1");
    exit;
}

$My_Class->title("Дополнительная информация");

?>
    <script type="text/javascript">
        $(function() {
            $(".btn").click(function(){
                $(this).button('loading').delay(2000).queue(function() {
                    $(this).button('reset');
                    $(this).dequeue();
                });
            });
        });
    </script>
<?

if(isset($_GET['cmd'])){$cmd = htmlspecialchars($_GET['cmd']);}else{$cmd = '0';}
if(isset($_GET['item'])){$item_id = abs(intval($_GET['item']));}else{$item = '0';}

switch ($cmd){
    case 'create':
        ?>
        <ol class="breadcrumb">
            <li><a href="/admin">Админ-панель</a></li>
            <li><a href="add_info.php">Дополнительная информация</a></li>
            <li class="active">Создание пункта</li>
        </ol>
        <?
        if(isset($_POST['create'])) {

            if($_POST['request'] != "") {
                $item_request=$_POST['request'];
                $item_response=$_POST['response'];
//                $cat_m = DB::$the->query("SELECT mesto FROM `sel_category` order by `mesto` DESC limit 1 ");
//                $cat_m = $cat_m->fetch(PDO::FETCH_ASSOC);
//                $new_mesto = $cat_m['mesto']+1;

                $params = array( 'request' => ''.urlencode($item_request).'', 'response' => urlencode($item_response));
                try{
                    $q= DB::$the->prepare("INSERT INTO `sel_addinfo` (request, response) VALUES (:request, :response)");
                    $q->execute($params);
                    header("Location: add_info.php");
                } catch (Exception $e){
                    echo $e->getCode() == 23000 ? '<div class="alert alert-danger">Такой запрос уже существует.</div>' :
                        '<div class="alert alert-danger">Что-то пошло не так...</div>';
                }
            }
            else
            {
                echo '<div class="alert alert-danger">Неверный запрос</div>';
            }
        }

        echo '<form action="add_info.php?cmd=create" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
    <span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span> </span>
<input type="text" placeholder="Запрос" class="form-control" name="request" value="">
<input type="text" placeholder="Ответ" class="form-control" name="response" value="">
</div>
<br />
<button type="submit" name="create" class="btn btn-warning btn-lg btn-block" data-loading-text="Создаю">Создать</button>
</div></form>';

        break;

    case 'edit':
        $item_id = (empty($_GET['item'])) ? '0' : $_GET['item'];
        ?>
        <ol class="breadcrumb">
            <li><a href="/admin">Админ-панель</a></li>
            <li><a href="add_info.php">Дополнительная информация</a></li>
            <li class="active">Редактирование пункта <?= $item_id ?></li>
        </ol>
        <?

        if($item_id == 0) {echo '<div class="alert alert-danger">Нет такого пункта</div>'; break;}
        $row = DB::$the->query("SELECT * FROM `sel_addinfo` WHERE `id` = {$item_id} ");
        $item = $row->fetch(PDO::FETCH_ASSOC);
        if(!($item)) {echo '<div class="alert alert-danger">Нет такого пункта</div>'; break;}
//        var_dump($item);
// Редактирование категории
        if(isset($_POST['edit'])) {

            if (!empty($_POST['request']) && !empty($_POST['response'])) {
                $item_request = $_POST['request'];
                $item_response = $_POST['response'];
                
                $params = array('item_id' => $item_id, 'request' => urlencode($item_request), 'response' => urlencode($item_response));
//                var_dump($params);
//                die();
                try {
                    $q = DB::$the->prepare("UPDATE `sel_addinfo` SET `request` = :request, `response` = :response
                                        WHERE `id` = :item_id");
                    $q->execute($params);
                    header("Location: add_info.php?cmd=edit&item=" . $item_id);
                } catch (Exception $e){
                    echo $e->getCode() == 23000 ? '<div class="alert alert-danger">Такой запрос уже существует.</div>' : 
                        '<div class="alert alert-danger">Что-то пошло не так...</div>';
                }

            } else {
                echo '<div class="alert alert-danger">Проверьте введенные данные</div>';
            }
        }


        echo '<form action="?cmd=edit&item='.$item_id.'" method="POST">
<div class="form-group col-sm-8">
<div class="input-group input-group-lg">
<span class="input-group-addon"><span class="glyphicon glyphicon-pencil"></span> </span>
<input type="text" placeholder="'.urldecode($item['request']).'" class="form-control" name="request" value="'.urldecode($item['request']).'">
<input type="text" placeholder="'.urldecode($item['response']).'" class="form-control" name="response" value="'.urldecode($item['response']).'">
</div><br />
<button type="submit" name="edit" class="btn btn-primary btn-lg btn-block" data-loading-text="Изменяю">Изменить</button>
</div></form>';


        break;

    case 'delete':
        $row = DB::$the->query("SELECT * FROM `sel_addinfo` WHERE `id` = '".$item_id."'");
        $item = $row->fetch(PDO::FETCH_ASSOC);
        if(!($item)) {echo '<div class="alert alert-danger">Нет такого пункта</div>'; break;}
        ?>
        <ol class="breadcrumb">
            <li><a href="/admin">Админ-панель</a></li>
            <li><a href="add_info.php">Дополнительная информация</a></li>
            <li class="active">Удаление пункта: <b><?= $item_id ?></b></li>
        </ol>
        <div class="alert alert-danger">Будет удален пункт <?= $item_id ?>!</div>

        <div class="btn-group">
            <button type="button" class="btn btn-danger dropdown-toggle" data-loading-text="Думаем" data-toggle="dropdown">Вы уверены? <span class="caret"></span></button>
            <ul class="dropdown-menu" role="menu">
                <li><a href="?cmd=delete&item=<?= $item_id ?>&confirmed">Да, удалить</a></li>
                <li class="divider"></li>
                <li><a href="add_info.php">Нет, отменить</a></li>
            </ul>
        </div><br /><br />

        <?

        if(isset($_GET['confirmed'])) {
            DB::$the->query("DELETE FROM `sel_addinfo` WHERE `id` = '".$item_id."' ");

            header("Location: add_info.php");
        }

        break;

    default:

        ?>
        <ol class="breadcrumb">
            <li><a href="/admin">Админ-панель</a></li>
            <li class="active">Дополнительная информация</li>
        </ol>

        <div class="list-group">
            <a class="list-group-item" href="?cmd=create">
                <span class="glyphicon glyphicon-plus-sign"></span> Создать пункт
            </a>
        </div>
        <?



        $total = DB::$the->query("SELECT * FROM `sel_addinfo` ");
        $total = $total->fetchAll();
        $max = 15;
        $pages = $My_Class->k_page(count($total),$max);
        $page = $My_Class->page($pages);
        $start=($max*$page)-$max;

        if(count($total) == 0){
            echo '<div class="alert alert-danger">Нет записей!</div>';
        }

        echo '<div class="list-group">';
        $query = DB::$the->query("SELECT * FROM `sel_addinfo` LIMIT $start, $max");
        while($el = $query->fetch()) {
            echo '<span class="list-group-item"><a href="?cmd=edit&item='.$el['id'].'" >['.urldecode($el['request']).']
                    <b>'.urldecode($el['response']).'</b> ';
            echo '<a href="?cmd=edit&item='.$el['id'].'"> <span class="badge pull-right"><span class="glyphicon glyphicon-pencil"></span> </a>';
            echo '<a href="?cmd=delete&item='.$el['id'].'"> <span class="badge pull-right"><span class="glyphicon glyphicon-remove"></span> </a>';
            echo '</span>';
        }
        echo '</div>';

        if ($pages>1) $My_Class->str('?',$pages,$page);
}

$My_Class->foot();
?>