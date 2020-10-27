<?
include('../header.php');

require_once('../my_db.php');

$start = $end = date('Y-m-d');
$bells = my_db::Query('select * from bells where active = true order by start');
$classes = my_db::Query('select * from classes');
$asocc = [];
$r = my_db::Query('select * from classes');
foreach ($r as $e)
    $assoc['id-'.$e['ID']] = $e['title'];
?>

    <style>
        .col_les {
            width: calc(90% / <?=count($bells)?>);
        }
    </style>
<??>
<div class="content">
    <div class="options_block">
        <div class="range_block">
            <label for="start">ОТ:</label>
            <input type="date" id="start" value="<?=$start?>">
            <label for="END">ДО:</label>
            <input type="date" id="end" value="<?=$end?>">
        </div>
        <div class="classes_block">
            <?
            foreach ($classes as $item) {
            ?>
            <div class="class_item">
                <input type="checkbox" id="class-<?=$item['ID']?>" data-id="<?=$item['ID']?>">
                <label for="class-<?=$item['ID']?>"><?=str_replace('∞', '"', $item['title'])?></label>
            </div>
            <?}?>
        </div>
    </div>

    <div class="table">
        <div class="trow thead">
            <div class="col col_d"><span>Дд</span></div>
            <div class="sub_row">
                <div class="col col_n">Класс</div>
                <?
                foreach ($bells as $bell) {
                    ?>
                    <div class="col col_les" data-id="<?=$bell['ID']?>"><?=str_replace('∞', '"', $bell['number'])?></div>
                <?}?>
            </div>
        </div>
    </div>
</div>
    <script>
        let assoc = JSON.parse('<?=json_encode($assoc)?>');
        function drawTable() {
            var start = document.getElementById('start').value;
            var end = document.getElementById('end').value;
            var classes = [];
            document.querySelectorAll('input[type=checkbox]').forEach(e => {
                if(e.checked) classes.push(e.dataset.id);
            });
            var table = document.querySelector('.table');
            var clone = table.firstElementChild.cloneNode(true);
            table.innerHTML = '';
            table.append(clone);
            postData(PATH+'/ajax.php', {request: 'get_shedule', start: start, end: end, classes: classes}).then(data => {
                data = JSON.parse(data).timetable;
                for(var date in data) {
                    var row = [];
                    var dd = date.split('-');
                    row.push(createEl('div', {
                        class: 'col col_d vert',
                        children: [
                            createEl('span', {
                                text: dd[2]+'.'+dd[1]+'.'+dd[0]
                            })
                        ]
                    }));
                    var block = [];
                    for(var clas in data[date]) {
                        var sub_row = [];
                        sub_row.push(createEl('div', {
                            class: 'col col_n',
                            text: assoc['id-'+clas.toString()].replaceAll('∞', '"')
                        }));
                        document.querySelector('.thead').querySelectorAll('.col_les').forEach(bell => {
                            if(data[date][clas].hasOwnProperty(parseInt(bell.dataset.id)-1)) {
                                var ins = [];
                                data[date][clas][parseInt(bell.dataset.id)-1].forEach(eee => {
                                    ins.push(createEl('div', {
                                        class: 'sec',
                                        children: [
                                            createEl('span', {
                                                text: eee.lesson
                                            }),
                                            createEl('span', {
                                                text: eee.teacher
                                            })
                                        ]
                                    }));
                                });
                                sub_row.push(createEl('div', {
                                    class: 'col col_les',
                                    children: ins
                                }))
                            }
                            else sub_row.push(createEl('div', {class: 'col col_les'}));
                        });
                        block.push(createEl('div', {
                            class: 'sub_row',
                            children:sub_row
                        }));
                    }
                    row.push(createEl('div', {
                        class: 'block',
                        children: block
                    }));
                    table.append(createEl('div', {
                        class: 'trow',
                        children: row
                    }));
                }

            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type=checkbox]').forEach(e => {
                e.addEventListener('change', drawTable);
            });
            document.querySelectorAll('input[type=date]').forEach(e => {
                e.addEventListener('change', drawTable);
            });
        });
    </script>
<?
include('../footer.php'); ?>