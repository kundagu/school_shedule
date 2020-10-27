<?
include('../header.php');

require_once('../my_db.php');

$classes_list = my_db::Query('select teachers.ID as ID, teachers.full_name as full_name, classrooms.title as title, teachers.classroom_id as classroom_id from (select * from teachers where active = true) as teachers join (select * from classrooms) as classrooms on teachers.classroom_id = classrooms.ID; ');
//echo '<pre>'.print_r($classes_list, 1).'</pre>';
?>
<div class="content">
    <div class="table one_column">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_name">Фамилия Имя Отчество</div>
            <div class="col col_classroom">Аудитория</div>
            <div class="col col_options">Действия</div>
        </div>
        <?
        $i = 1;
        foreach ($classes_list as $item) {
            ?>
            <div class="trow" data-id="<?=$item['ID']?>">
                <div class="col col_n"><?=$i?></div>
                <div class="col col_name"><?=str_replace('∞', '"', $item['full_name'])?></div>
                <div class="col col_classroom" data-id="<?=$item['classroom_id']?>"><?=str_replace('∞', '"', $item['title'])?></div>
                <div class="col col_options" data-id="<?=$item['ID']?>"><button>Изменить</button></div>
            </div>
            <?
            $i++;
        }
        ?>
        <div class="trow">
            <div class="col col_n"><?=$i?></div>
            <div class="col col_name">Новый</div>
            <div class="col col_classroom">&nbsp;</div>
            <div class="col col_options"><button id="add_item">Добавить</button></div>
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
                class: 'col col_name',
                children: [
                    createEl('input', {
                        type: 'text',
                        id: 'input_title',
                        placeholder: 'Фамилия Имя Отчество',
                        })
                    ]
                }));
            var ans = JSON.parse(syncRequest(PATH + '/ajax.php', {request: 'get_free_classrooms', id: 0}));
            var classrooms_list = [];
            classrooms_list.push(createEl('option', {
                value: '0',
                disabled: true,
                selected: true,
                text: 'Выбор'
            }));
            if(ans.status == 'ok')
                ans.classrooms_list.forEach( e => {
                    classrooms_list.push(createEl('option', {
                        value: e.ID,
                        text: e.title
                    }));
                });
            target.append(createEl('div', {
                class: 'col col_classroom',
                children: [
                    createEl('select', {
                        name: 'classroom',
                        id: 'classroom_select',
                        value: 0,
                        children: classrooms_list
                    })
                ]
            }));
            target.append(createEl('div', {
                class: 'col col_options',
                children: [
                    createEl('button', {
                        text: 'Сохранить',
                        events: {
                            click: function () {
                                var inputTitle = document.getElementById('input_title');
                                var sel = document.getElementById('classroom_select').selectedIndex;
                                var classroom = (sel > 0 ? parseInt(ans.classrooms_list[sel - 1].ID): 0);
                                if(classroom > 0) {
                                    var classroomName = ans.classrooms_list[sel - 1].title;
                                    if(inputTitle.value.length > 1) {
                                        var val = inputTitle.value.replaceAll('"', '∞');
                                        var isFree = syncRequest(PATH+'/ajax.php', {request: 'check_teacher_name', full_name: val});
                                        isFree = JSON.parse(isFree);
                                        if(isFree.status == 'free') {
                                            postData(PATH+'/ajax.php', {request: 'add_teacher', full_name: val, classroom_id: classroom}).then(data => {
                                                console.log(data);
                                                console.log(val);
                                                var answer = JSON.parse(data);
                                                if(answer.status == 'ok') {
                                                    table.removeChild(target);
                                                    table.append(createEl('div', {
                                                        class: 'trow',
                                                        data: [{id: answer.id}],
                                                        children: [
                                                            createEl('div', {
                                                                class: 'col col_n',
                                                                text: number.toString()
                                                            }),
                                                            createEl('div', {
                                                                class: 'col col_name',
                                                                text: val.replaceAll('∞', '"')
                                                            }),
                                                            createEl('div', {
                                                                class: 'col col_classroom',
                                                                data: [{id: classroom}],
                                                                text: classroomName
                                                            }),
                                                            createEl('div', {
                                                                class: 'col col_options',
                                                                data:[{id: answer.id}],
                                                                children: [
                                                                    createEl('button', {
                                                                        text: 'Изменить',
                                                                        events: {
                                                                            click: changeItem
                                                                        }
                                                                    })
                                                                ]
                                                            })
                                                        ]
                                                    }));
                                                    table.append(clone);
                                                    document.getElementById('add_item').parentElement.parentElement.firstElementChild.innerText = (number+1).toString();
                                                    document.getElementById('add_item').addEventListener('click', addItem);
                                                }
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
                                else document.getElementById('classroom_select').focus();

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
            var titleBlock = parent.querySelector('.col_name');
            var classroomBlock = parent.querySelector('.col_classroom');
            var classroom =  classroomBlock.dataset.id;
            var classroomSelected = 0;
            var oldClassroom = {ID: classroomBlock.dataset.id, title: classroomBlock.innerText};
            var ans = JSON.parse(syncRequest(PATH + '/ajax.php', {request: 'get_free_classrooms', id: classroom}));
            var classrooms_list = [];
            classrooms_list.push(createEl('option', {
                value: '0',
                disabled: true,
                selected: true,
                text: 'Выбор'
            }));
            if(ans.status == 'ok')
                ans.classrooms_list.forEach( (e, index) => {
                    if(classroom == e.ID) classroomSelected = index + 1;
                    classrooms_list.push(createEl('option', {
                        value: e.ID,
                        text: e.title,
                        selected: (classroom == e.ID)
                    }));
                });
            var optionsBlock = parent.querySelector('.col_options');
            var id = optionsBlock.dataset.id;
            var value = titleBlock.innerText;
            titleBlock.innerHTML = '';
            titleBlock.append(createEl('input', {
                type: 'text',
                id: 'input_title',
                value: value,
                placeholder: 'Фамилия Имя Отчество',
                events: {
                    input: function () {
                        if(this.value.length > 254) this.value = this.value.substr(0, 255);
                    }
                }
            }));
            classroomBlock.innerHTML = '';
            classroomBlock.append(createEl('select', {
                name: 'classroom',
                id: 'classroom_select',
                value: classroomSelected,
                children: classrooms_list
            }));
            optionsBlock.innerHTML = '';
            optionsBlock.append(createEl('button', {
                text: 'Сохранить',
                events: {
                    click: function () {
                        var sel = document.getElementById('classroom_select').selectedIndex;
                        var classroom = (sel > 0 ? parseInt(ans.classrooms_list[sel - 1].ID): 0);
                        var classroomName = ans.classrooms_list[sel - 1].title;
                        var inputTitle = document.getElementById('input_title');
                        if(inputTitle.value.length > 1) {
                            var val = inputTitle.value.replaceAll('"', '∞');
                            postData(PATH+'/ajax.php', {request: 'update_item', table: 'teachers', id: id, data: {full_name: '"'+val+'"', classroom_id: classroom}}).then(data => {
                                var answer = JSON.parse(data);
                                titleBlock.innerHTML = '';
                                classroomBlock.innerHTML = '';
                                optionsBlock.innerHTML = '';
                                optionsBlock.append(createEl('button', {
                                    text: 'Изменить',
                                    events: {
                                        click: changeItem
                                    }
                                }));
                                switch(answer.status) {
                                    case 'ok':
                                        titleBlock.innerText = answer.obj.full_name.replaceAll('"', '').replaceAll('∞', '"');
                                        classroomBlock.innerText = classroomName;
                                        classroomBlock.dataset.id = classroom;
                                        break;
                                    case 'double':
                                        classroomBlock.innerText = oldClassroom.title;
                                        classroomBlock.dataset.id = oldClassroom.ID;
                                        titleBlock.innerText = value.replaceAll('∞', '"');
                                        var doubleTarget = null;
                                        document.querySelectorAll('.trow').forEach(e => {
                                            if(!e.classList.contains('thead') && e.dataset.id == answer.obj.ID)
                                                doubleTarget = e;
                                        });
                                        if(doubleTarget) {
                                            doubleTarget.querySelector('.col_title').style.color = '#f00';
                                            animatedScroll(doubleTarget, 300, 100);
                                        }
                                        break;
                                    case 'reset':
                                        titleBlock.innerText = value.replaceAll('∞', '"');
                                        optionsBlock.dataset.id = answer.obj.ID;
                                        parent.dataset.id = answer.obj.ID;
                                        classroomBlock.innerText = classroomName;
                                        classroomBlock.dataset.id = classroom;
                                        break;
                                }

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
                        postData(PATH+'/ajax.php', {request: 'delete_teacher', id: id}).then(data => {
                                console.log(data);
                                var answer = JSON.parse(data);
                                if(answer.status == 'ok') {
                                    parent.parentElement.removeChild(parent);
                                    var i = 1;
                                    document.querySelectorAll('.trow').forEach(e => {
                                        if(!e.classList.contains('thead')) {
                                            e.firstElementChild.innerHTML = i.toString();
                                            i++;
                                        }
                                    });
                                }
                                else  {
                                    titleBlock.innerHTML = value;
                                    optionsBlock.innerHTML = '';
                                    optionsBlock.append(createEl('button', {
                                        text: 'Изменить',
                                        events: {
                                            click: changeItem
                                        }
                                    }));
                                }
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