<?
include('../header.php');

require_once('../my_db.php');

$classes_list = my_db::Query(
        'select subject.ID as ID, subject.title as title, subject.coverage as coverage, '.
        'subject.teacher_id as teacher_id, teachers.full_name as full_name, '.
        'subject.connection as connection, connect.title as connection_title from '.
        '(select * from subject where active = true) as subject join '.
        '(select * from teachers) as teachers on subject.teacher_id = teachers.ID '.'
        left join (select * from subject) as connect on subject.connection = connect.ID; ');
?>
<div class="content">
    <div class="table one_column">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_start">Название</div>
            <div class="col col_cov">Охват</div>
            <div class="col col_name small">Преподаватель</div>
            <div class="col col_finish">Связь</div>
            <div class="col col_options small">Действия</div>
        </div>
        <?
        $i = 1;
        foreach ($classes_list as $item) {
            ?>
            <div class="trow" data-id="<?=$item['ID']?>">
                <div class="col col_n"><?=$i?></div>
                <div class="col col_start"><?=str_replace('∞', '"', $item['title'])?></div>
                <div class="col col_cov"><?=($item['coverage'] >= 1 ? 'Общий': 'По группам')?></div>
                <div class="col col_name small" data-id="<?=$item['teacher_id']?>"><?=str_replace('∞', '"', $item['full_name'])?></div>
                <div class="col col_finish" data-id="<?=$item['connection']?>"><?=($item['connection_title'] ? str_replace('∞', '"', $item['connection_title']): 'Нет')?></div>
                <div class="col col_options small" data-id="<?=$item['ID']?>"><button>Изменить</button></div>
            </div>
            <?
            $i++;
        }
        ?>
        <div class="trow">
            <div class="col col_n"><?=$i?></div>
            <div class="col col_start">Новый</div>
            <div class="col col_cov">&nbsp;</div>
            <div class="col col_name small">&nbsp;</div>
            <div class="col col_finish">&nbsp;</div>
            <div class="col col_options small"><button id="add_item">Добавить</button></div>
        </div>
    </div>
</div>
    <script>
        function addItem() {
            var target = this.parentElement.parentElement;
            var table = target.parentElement;
            var clone = target.cloneNode(true);
            var number = parseInt(target.querySelector('.col_n').innerHTML.toString());
            target.innerHTML = '';
            target.append(createEl('div', {
                class: 'col col_n',
                text: number.toString()
            }));
            target.append(createEl('div', {
                class: 'col col_start',
                children: [
                    createEl('input', {
                        type: 'text',
                        id: 'input_title',
                        placeholder: 'Название предмета',
                        events: {
                            input: function () {
                                if(this.value.length > 49) this.value = this.value.substr(0, 50);
                            }
                        }
                        })
                    ]
                }));
            target.append(createEl('div', {
                class: 'col col_cov',
                children: [
                    createEl('input', {
                        id: 'group',
                        type: 'checkbox',
                        events: {
                            change: function () {
                                var tar = document.getElementById('connection_select');
                                if(this.checked) tar.removeAttribute('disabled');
                                else {
                                    tar.querySelectorAll('option').forEach(e => {
                                        e.removeAttribute('selected');
                                    });
                                    tar.firstElementChild.setAttribute('selected', 'selected');
                                    tar.selectedIndex = 0;
                                    tar.setAttribute('disabled', 'disabled');
                                }
                            }
                        }
                    }),
                    createEl('label', {
                        for: 'group',
                        text: 'По группам'
                    })
                ]
            }));
            var ans = JSON.parse(syncRequest(PATH + '/ajax.php', {request: 'get_teachers'}));
            var teachers_list = [];
            teachers_list.push(createEl('option', {
                value: '0',
                disabled: true,
                selected: true,
                text: 'Выбор'
            }));
            if(ans.status == 'ok')
                ans.teachers_list.forEach( e => {
                    teachers_list.push(createEl('option', {
                        value: e.ID,
                        text: e.full_name
                    }));
                });
            target.append(createEl('div', {
                class: 'col col_name small',
                children: [
                    createEl('select', {
                        name: 'name',
                        id: 'teacher_select',
                        value: 0,
                        children: teachers_list
                    })
                ]
            }));
            var ans1 = JSON.parse(syncRequest(PATH + '/ajax.php', {request: 'get_connections'}));
            console.log(ans1);
            var connection_list = [];
            connection_list.push(createEl('option', {
                value: '0',
                disabled: true,
                selected: true,
                text: 'Выбор'
            }));
            if(ans1.status == 'ok')
                ans1.connection_list.forEach( e => {
                    connection_list.push(createEl('option', {
                        value: e.ID,
                        text: e.title,
                    }));
                });
            target.append(createEl('div', {
                class: 'col col_finish',
                children: [
                    createEl('select', {
                        name: 'connection',
                        id: 'connection_select',
                        disabled: true,
                        value: 0,
                        children: connection_list
                    })
                ]
            }));
            target.append(createEl('div', {
                class: 'col col_options small',
                children: [
                    createEl('button', {
                        text: 'Сохранить',
                        events: {
                            click: function () {
                                var inputTitle = document.getElementById('input_title');
                                var sel = document.getElementById('teacher_select').selectedIndex;
                                var teacher = (sel > 0 ? parseInt(ans.teachers_list[sel - 1].ID): 0);
                                var sel1 = document.getElementById('connection_select').selectedIndex;
                                var connection = (sel1 > 0 ? parseInt(ans1.connection_list[sel1 - 1].ID): 0);
                                if(teacher > 0) {
                                    var teacherName = ans.teachers_list[sel - 1].full_name;
                                    var connectionTitle = (parseInt(connection) > 0 ? ans1.connection_list[sel1 - 1].title : '&nbsp;');
                                    var coverage = (document.getElementById('group').checked ? 0.5: 1);
                                    if(inputTitle.value.length > 1) {
                                        var val = inputTitle.value.replaceAll('"', '∞');
                                        var isFree = syncRequest(PATH+'/ajax.php', {request: 'check_subject', title: val});
                                        isFree = JSON.parse(isFree);
                                        console.log(isFree);
                                        console.log({
                                            request: 'add_subject',
                                            title: val,
                                            teacher_id: teacher,
                                            coverage: coverage,
                                            connection: connection});
                                        if(isFree.status == 'free') {
                                            postData(PATH+'/ajax.php', {
                                                request: 'add_subject',
                                                title: val,
                                                teacher_id: teacher,
                                                coverage: coverage,
                                                connection: connection}).then(data => {
                                                    location.reload();
                                            });
                                        }
                                        else {
                                            document.querySelectorAll('.trow').forEach( e => {
                                                if(e.dataset.id == isFree.id) e.querySelector('.col_title').style.color = '#f00';
                                                animatedScroll(e, 100, 100);
                                            });
                                            table.removeChild(target);
                                            table.append(clone);
                                            document.getElementById('add_item').addEventListener('click', addItem);
                                        }
                                    }
                                    else inputTitle.focus();
                                }
                                else document.getElementById('teacher_select').focus();

                            }
                        }
                    }),
                    createEl('button', {
                        text: 'Отмена',
                        events: {
                            click: function () {
                                target.parentElement.removeChild(target);
                                table.append(clone);
                                document.getElementById('add_item').addEventListener('click', addItem);
                            }
                        }
                    })
                ]
            }));
            document.getElementById('input_title').focus();
        }
        function changeItem() {
            var parent = this.parentElement.parentElement;
            var titleBlock = parent.querySelector('.col_start');
            var coverageBlock = parent.querySelector('.col_cov');
            var coverage = coverageBlock.innerHTML == 'Общий' ? 1: 0.5;
            var teacherBlock = parent.querySelector('.col_name');
            var teacher = teacherBlock.dataset.id;
            var teacherSelected = 0;
            var connectionBlock = parent.querySelector('.col_finish');
            var connection = connectionBlock.dataset.id;
            var connectionSelected = 1;
            var oldConnection = {ID: connectionBlock.dataset.id, title: connectionBlock.innerText};
            var oldTeacher = {ID: teacherBlock.dataset.id, full_name: teacherBlock.innerText};
            var optionsBlock = parent.querySelector('.col_options');
            var id = optionsBlock.dataset.id;
            var ans = JSON.parse(syncRequest(PATH + '/ajax.php', {request: 'get_teachers'}));
            var teachers_list = [];
            teachers_list.push(createEl('option', {
                value: '0',
                disabled: true,
                selected: true,
                text: 'Выбор'
            }));
            if(ans.status == 'ok')
                ans.teachers_list.forEach( (e, index) => {
                    if(teacher == e.ID) teacherSelected = index + 1;
                    teachers_list.push(createEl('option', {
                        value: e.ID,
                        text: e.full_name,
                        selected: (teacher == e.ID)
                    }));
                });
            var ans1 = JSON.parse(syncRequest(PATH + '/ajax.php', {request: 'get_connections'}));
            console.log(ans1);
            var connection_list = [];
            connection_list.push(createEl('option', {
                value: '0',
                data: [{id: 0}],
                text: 'Нет'
            }));
            connection_list.push(createEl('option', {
                value: '1',
                selected: true,
                data: [{id: oldConnection.ID}],
                text: oldConnection.title
            }));
            var i = 1;
            if(ans1.status == 'ok')
                ans1.connection_list.forEach( e => {
                    if(e.ID != id && e.ID != oldConnection.ID) connection_list.push(createEl('option', {
                        value: i++,
                        text: e.title,
                        data: [{id: e.ID}]
                    }));
                });

            connectionBlock.innerHTML = '';
            connectionBlock.append(createEl('select', {
                name: 'connection',
                id: 'connection_select',
                disabled: (coverage >= 1),
                value: 0,
                children: connection_list
            }));
            var value = titleBlock.innerText;
            titleBlock.innerHTML = '';
            titleBlock.append(createEl('input', {
                type: 'text',
                id: 'input_title',
                value: value,
                placeholder: 'Название предмета',
                events: {
                    input: function () {
                        if(this.value.length > 49) this.value = this.value.substr(0, 50);
                    }
                }
            }));
            teacherBlock.innerHTML = '';
            teacherBlock.append(createEl('select', {
                name: 'teacher',
                id: 'teacher_select',
                value: teacherSelected,
                children: teachers_list
            }));
            coverageBlock.innerHTML = '';
            coverageBlock.append(createEl('input', {
                id: 'group',
                type: 'checkbox',
                checked: !(coverage >= 1),
                events: {
                    change: function () {
                        var tar = document.getElementById('connection_select');
                        if(this.checked) tar.removeAttribute('disabled');
                        else {
                            tar.querySelectorAll('option').forEach(e => {
                                e.removeAttribute('selected');
                            });
                            tar.firstElementChild.setAttribute('selected', 'selected');
                            tar.selectedIndex = 0;
                            tar.setAttribute('disabled', 'disabled');
                        }
                    }
                }
            }));
            coverageBlock.append(createEl('label', {
                    for: 'group',
                    text: 'По группам'
                }));

            optionsBlock.innerHTML = '';
            optionsBlock.append(createEl('button', {
                text: 'Сохранить',
                events: {
                    click: function () {
                        var sel = document.getElementById('teacher_select').selectedIndex;
                        var teacher = (sel > 0 ? parseInt(ans.teachers_list[sel - 1].ID): 0);
                        var teacherName = ans.teachers_list[sel - 1].full_name;
                        var inputTitle = document.getElementById('input_title');
                        var newCov = document.getElementById('group').checked ? 0.5: 1;
                        var sel1 = document.getElementById('connection_select').selectedIndex;
                        var newCon = (sel > 0 ? parseInt(connection_list[sel1].dataset.id): 0);
                        if(inputTitle.value.length > 1) {
                            var val = inputTitle.value.replaceAll('"', '∞');
                            postData(PATH+'/ajax.php', {request: 'update_item', table: 'subject', id: id, data: {title: '"'+val+'"', teacher_id: teacher, coverage: newCov, connection: newCon}}).then(data => {
                                var answer = JSON.parse(data);
                                console.log(answer);
                                location.reload();
                            });

                        }
                        else inputTitle.focus();
                    }
                }
            }));
            optionsBlock.append(createEl('button', {
                text: 'Удалить',
                events: {
                    click: function () {
                        postData(PATH+'/ajax.php', {request: 'delete_subject', id: id}).then(data => {
                            console.log(data);
                            var answer = JSON.parse(data);
                            console.log(answer);
                            location.reload();
                            });
                    }
                }
            }))
        }
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('add_item').addEventListener('click', addItem);
            document.querySelectorAll('.col_options').forEach( e => {
                if(e.firstElementChild && !e.firstElementChild.hasAttribute('id')) e.firstElementChild.addEventListener('click', changeItem);
            });
        });
    </script>
<?
include('../footer.php'); ?>