<?
include('../header.php');

require_once('../my_db.php');
$s = 'select teacher.full_name as full_name, sub.sub_sum as sub_sum from teachers as teacher '.
    'join (select teacher_id, count(*) as sub_sum from subject group by teacher_id) as sub '.
    'on teacher.ID = sub.teacher_id order by sub.sub_sum desc, teacher.full_name asc';

$classes_list = my_db::Query($s);
?>
<div class="content">
    <div class="table">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_title">Фамилия Имя Отчество</div>
            <div class="col col_options">Количество предметов</div>
        </div>
        <?
        $i = 1;
        foreach ($classes_list as $item) {
            ?>
            <div class="trow"">
                <div class="col col_n"><?=$i?></div>
                <div class="col col_title"><?=str_replace('∞', '"', $item['full_name'])?></div>
                <div class="col col_options"><?=$item['sub_sum']?></div>
            </div>
            <?
            $i++;
        }
        ?>
    </div>
</div>
<?
include('../footer.php'); ?>