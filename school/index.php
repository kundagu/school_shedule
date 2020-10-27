<?
include('header.php');

require_once('my_db.php');

$q = my_db::Query('select * from bells where active = true order by start asc');
$bell_assoc = [];
$i = 0;
foreach ($q as $item)
    $bell_assoc[$item['start']] = ++$i;
$query = '
select 
lessons.date as date, 
classes.title as class_title,
lessons.class_id as class_id,
min(bells.start) as min_start,
max(bells.start) as max_start,
sum(subject.coverage) as count_lessons
from lessons as lessons
join bells as bells 
on lessons.bell_id = bells.ID
join subject as subject
on lessons.subject_id = subject.ID
join classes as classes
on lessons.class_id = classes.ID
group by lessons.class_id, lessons.date
order by lessons.date';

$answer = my_db::Query($query);

?>
<div class="content">
    <div class="table">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_name">Дата</div>
            <div class="col col_classroom">Класс</div>
            <div class="col col_options">Окна</div>
        </div>
        <?
        $i = 0;
        foreach ($answer as $item) {
            $d = explode('-', $item['date']);
            $date = $d[2].'.'.$d[1].'.'.$d[0];
        ?>
        <div class="trow">
            <div class="col col_n"><?=++$i?></div>
            <div class="col col_name"><?=$date?></div>
            <div class="col col_classroom"><?=str_replace('∞', '"', $item['class_title'])?></div>
            <div class="col col_options">
                <?=($bell_assoc[$item['max_start']] - $bell_assoc[$item['min_start']] + 1 > $item['count_lessons'] ?
                '<span class="red">Есть окна</span>':'<span class="green">Окон нет</span>')?></div>
        </div>
        <?}?>
    </div>
</div>

<?

include('footer.php');?>
