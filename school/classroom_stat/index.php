<?
include('../header.php');

require_once('../my_db.php');

$weekDay = ['ВС', 'ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ'];


?>
<div class="content">
    <div class="centred">
        <?
        for($i = 1; $i < 7; $i++) {
            ?>
            <input type="checkbox" id="check-<?=$i?>" data-id="<?=$i?>" class="week">
            <label for="check-<?=$i?>"><?=$weekDay[$i]?></label>
            <?
        }
        ?>
    </div>
    <div class="table">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_title">Название аудитории</div>
            <div class="col col_options">Загруженность</div>
        </div>
        <?
        $classes_list = my_db::Query('select classrooms.ID as ID, classrooms.title as title, count(*) as ssum '.
        'from lessons as lessons join (select * from subject) as subject on lessons.subject_id = subject.ID '.
        'join (select * from teachers) as teachers on subject.teacher_id = teachers.ID join (select * from classrooms) '.
         'as classrooms on classrooms.ID = teachers.classroom_id group by classrooms.ID order by ssum asc');
        $i = 1;
        foreach ($classes_list as $item) {
            ?>
            <div class="trow">
                <div class="col col_n"><?=$i?></div>
                <div class="col col_title"><?=str_replace('∞', '"', $item['title'])?></div>
                <div class="col col_options"><?=$item['ssum']?></div>
            </div>
            <?
            $i++;
        }
        ?>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type=checkbox]').forEach(e => {
                e.addEventListener('change', function () {
                    var arDays = [];
                    document.querySelectorAll('input[type=checkbox]').forEach( el => {
                        if(el.checked) arDays.push(el.dataset.id);
                    });
                    postData(PATH+'/ajax.php', {request: 'classroom_stat', data: arDays}).then(data => {
                        var answer = JSON.parse(data);
                        if(answer.status == 'ok') {
                            var target = document.querySelector('.table');
                            var clone = target.firstElementChild.cloneNode(true);
                            target.innerHTML = '';
                            var i = 0;
                            target.append(clone);
                            answer.notes.forEach(note => {
                                target.append(createEl('div', {
                                    class: 'trow',
                                    children: [
                                        createEl('div', {
                                            class: 'col col_n',
                                            text: i++
                                        }),
                                        createEl('div', {
                                            class: 'col col_title',
                                            text: note.title
                                        }),
                                        createEl('div', {
                                            class: 'col col_options',
                                            text: note.ssum
                                        })
                                    ]
                                }));
                            });
                        }
                    });
                });
            });
        });
    </script>
<?
include('../footer.php'); ?>