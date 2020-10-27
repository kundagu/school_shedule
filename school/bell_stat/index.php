<?
include('../header.php');

require_once('../my_db.php');
$date = time();
$weekday = date("w", $date);

$startWeek = $date - 60*60*24*($weekday - 1);
$endWeek = $startWeek + 60*60*24*6;

$arWeeks = [];
for($i = - 7; $i < 8; $i++) {
    $arWeeks[] = [
        'start' => date('Y-m-d', $startWeek + 60*60*24*7*$i),
        'end' => date('Y-m-d', $endWeek + 60*60*24*7*$i),
        'title' => date('d.m.Y', $startWeek + 60*60*24*7*$i).' - '.date('d.m.Y', $endWeek + 60*60*24*7*$i)
    ];
}

$classes_list = my_db::Query('select les.class_title as class_title, les.lesson_sum as lesson_sum, teach.teacher_count as teacher_count 
                from (
                    select classes.ID as class_id, classes.title as class_title, count(lessons.ID) as lesson_sum 
                    from classes as classes 
                    join (
                        select * 
                        from lessons 
                        where date between "'.$arWeeks[7]['start'].'" and "'.$arWeeks[7]['end'].'" 
                    ) as lessons 
                    on classes.ID = lessons.class_id 
                    group by classes.ID
                ) as les 
                join (
                    select class_id as class_id, count(teacher_id) as teacher_count 
                    from (
                        select distinct classes.ID as class_id, subject.teacher_id as teacher_id 
                        from classes as classes join lessons as lessons 
                        on classes.ID = lessons.class_id 
                        join subject as subject 
                        on lessons.subject_id = subject.ID
                    ) as list 
                    group by class_id
                ) as teach 
                on les.class_id = teach.class_id');
?>
<div class="content">
    <div class="centred">
        <select name="week" id="week">
            <?
            foreach ($arWeeks as $key => $item) {
                ?>
                <option value="<?=$key?>" data-start="<?=$item['start']?>" data-end="<?=$item['end']?>" <?=($key == 7 ? 'selected':'')?>><?=$item['title']?></option>
                <?
            }
            ?>
        </select>
    </div>
    <div class="table">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_classroom">Класс</div>
            <div class="col col_name">Количество уроков</div>
            <div class="col col_options">Количество учителей</div>
        </div>
        <div id="tab">
        <?
        $i = 1;
        foreach ($classes_list as $item) {
            ?>
            <div class="trow">
                <div class="col col_n"><?=$i?></div>
                <div class="col col_classroom"><?=str_replace('∞', '"', $item['class_title'])?></div>
                <div class="col col_name"><?=$item['lesson_sum']?></div>
                <div class="col col_options"><?=$item['teacher_count']?></div>
            </div>
            <?
            $i++;
        }
        ?>
        </div>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
           document.getElementById('week').addEventListener('change', function () {
               var target = document.getElementById('tab');
               target.innerHTML = '';
               var index = this.selectedIndex;
               var start = this.options[index].dataset.start;
               var end = this.options[index].dataset.end;
               postData(PATH+'/ajax.php', {request: 'get_bell_stat', start: start, end: end}).then(data => {
                   var table = JSON.parse(data);
                   var i = 1;
                   table.answer.forEach(e => {
                       target.append(createEl('div', {
                           class: 'trow',
                           children: [
                               createEl('div', {
                                   class: 'col col_n',
                                   text: i
                               }),
                               createEl('div', {
                                   class: 'col col_classroom',
                                   text: e.class_title.replaceAll('∞', '"')
                               }),
                               createEl('div', {
                                   class: 'col col_name',
                                   text: e.lesson_sum
                               }),
                               createEl('div', {
                                   class: 'col col_options',
                                   text: e.teacher_count
                               })
                           ]
                       }));
                   });
               });
           });
        });
    </script>
<?
include('../footer.php'); ?>