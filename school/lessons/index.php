<?
include('../header.php');

require_once('../my_db.php');

$classes_list = my_db::Query(
'select '.
    'lessons.ID as ID, '.
    'lessons.date as date, '.
    'lessons.weekday as weekday, '.
    'lessons.bell_id as bell_id, '.
    'bells.number as bell_title, '.
    'lessons.class_id as class_id, '.
    'classes.title as class_title, '.
    'lessons.subject_id as subject_id, '.
    'subject.title as subject_title '.
'from '.
    '(select * from lessons) as lessons '.
'join '.
    '(select * from bells) as bells '.
'on bells.ID = lessons.bell_id '.
'join '.
    '(select * from classes) as classes '.
'on classes.ID = lessons.class_id '.
'join '.
    '(select * from subject) as subject '.
'on subject.ID = lessons.subject_id order by date, bell_title, class_title;');

//echo '<pre>'.print_r($classes_list, 1).'</pre>';
?>
<div class="content">
    <div class="table">
        <div class="trow thead">
            <div class="col col_start">Дата</div>
            <div class="col col_n">Класс</div>
            <div class="col col_m">Урок</div>
            <div class="col col_name">Предмет</div>
            <div class="col col_options">Действия</div>
        </div>
        <?
        foreach ($classes_list as $item) {
            $dd = explode('-', $item['date']);
            $date = $dd[2].'.'.$dd[1].'.'.$dd[0];
            ?>
            <div class="trow" data-id="<?=$item['ID']?>">
                <div class="col col_start"><?=$date?></div>
                <div class="col col_n" data-id="<?=$item['class_id']?>"><?=str_replace('∞', '"', $item['class_title'])?></div>
                <div class="col col_m" data-id="<?=$item['bell_id']?>"><?=str_replace('∞', '"', $item['bell_title'])?></div>
                <div class="col col_name" data-id="<?=$item['subject_id']?>"><?=str_replace('∞', '"', $item['subject_title'])?></div>
                <div class="col col_options" data-id="<?=$item['ID']?>"><button>Изменить</button></div>
            </div>
            <?
        }
        ?>
        <div class="trow" data-id="0">
            <div class="col col_start">Дата</div>
            <div class="col col_n">Класс</div>
            <div class="col col_m">Урок</div>
            <div class="col col_name">Предмет</div>
            <div class="col col_options"><button id="add_item">Добавить</button></div>
        </div>
    </div>
</div>
    <script>
        function getClasses(id = 0, value = '') {
            var date = document.getElementById('date_input').value;
            var lessonId = document.getElementById('date_input').parentElement.parentElement.dataset.id;
            var bell_select = document.getElementById('bell_select');
            var bell = bell_select.options[bell_select.selectedIndex].dataset.id;
            var arOptions = syncRequest(PATH + '/ajax.php', {request: 'get_classes', date: date, bell: bell, lesson: lessonId});
            arOptions = JSON.parse(arOptions);
            var classSelectBlock = document.getElementById('class_select');
            var selectedClassId = classSelectBlock.options[classSelectBlock.selectedIndex].dataset.id;
            if(id > 0) selectedClassId = id;
            console.log(selectedClassId);
            classSelectBlock.innerHTML = '';
            classSelectBlock.append(createEl('option', {
                value: '0',
                disabled: true,
                data: [{id: 0}],
                text: 'Выбор'
            }));
            var i = 0;
            classSelectBlock.selectedIndex = 0;
            arOptions.classes.forEach(e => {
                    i++;
                if(e.class_id == selectedClassId) classSelectBlock.selectedIndex = i;
                    classSelectBlock.append(createEl('option', {
                        value: i.toString(),
                        selected: (selectedClassId == e.class_id),
                        data: [{id: e.class_id}],
                        text: e.title.replaceAll('∞', '"')
                    }));
            });
            if(classSelectBlock.selectedIndex == 0) classSelectBlock.firstElementChild.setAttribute('selected', 'selected');
            else classSelectBlock.removeChild(classSelectBlock.firstElementChild);
        }

        function getBells(id = 0, value = '') {
            var date = document.getElementById('date_input').value;
            var lessonId = document.getElementById('date_input').parentElement.parentElement.dataset.id;
            var class_select = document.getElementById('class_select');
            var clas = class_select.options[class_select.selectedIndex].dataset.id;
            var arOptions = syncRequest(PATH + '/ajax.php', {request: 'get_bells', date: date, class: clas, lesson: lessonId});
            arOptions = JSON.parse(arOptions);
            var bellSelectBlock = document.getElementById('bell_select');
            var selectedBellId = bellSelectBlock.options[bellSelectBlock.selectedIndex].dataset.id;
            if(id > 0) selectedBellId = id;
            bellSelectBlock.innerHTML = '';
            bellSelectBlock.append(createEl('option', {
                value: '0',
                disabled: true,
                data: [{id: 0}],
                text: 'Выбор'
            }));
            var i = 0;
            bellSelectBlock.selectedIndex = 0;
            arOptions.bells.forEach(e => {
                i++;
                if(e.bell_id == id) bellSelectBlock.selectedIndex = i;
                bellSelectBlock.append(createEl('option', {
                        value: i.toString(),
                        selected: (selectedBellId == e.bell_id),
                        data: [{id: e.bell_id}],
                        text: e.title.replaceAll('∞', '"')
                    }));
            });
            if(bellSelectBlock.selectedIndex == 0) bellSelectBlock.firstElementChild.setAttribute('selected', 'selected');
            else bellSelectBlock.removeChild(bellSelectBlock.firstElementChild);
        }

        function getSubjects(id = 0, value = '') {
            var date = document.getElementById('date_input').value;
            var lessonId = document.getElementById('date_input').parentElement.parentElement.dataset.id;
            var class_select = document.getElementById('class_select');
            var clas = class_select.options[class_select.selectedIndex].dataset.id;
            var bell_select = document.getElementById('bell_select');
            var bell = bell_select.options[bell_select.selectedIndex].dataset.id;
            var arOptions = syncRequest(PATH + '/ajax.php', {request: 'get_subjects', date: date, bell: bell, class: clas, lesson: lessonId});
            arOptions = JSON.parse(arOptions);
            var classSubjectBlock = document.getElementById('subject_select');
            var selectedSubjectId = classSubjectBlock.options[classSubjectBlock.selectedIndex].dataset.id;
            // if(id > 0) selectedClassId = id;
            classSubjectBlock.innerHTML = '';
            classSubjectBlock.append(createEl('option', {
                value: '0',
                disabled: true,
                data: [{id: 0}],
                text: 'Выбор'
            }));
            var i = 0;
            classSubjectBlock.selectedIndex = 0;
            arOptions.subjects.forEach(e => {
                if(e.subject_id != selectedSubjectId) classSubjectBlock.selectedIndex = i;
                    i++;
                classSubjectBlock.append(createEl('option', {
                        value: i.toString(),
                        data: [{id: e.subject_id}],
                        selected: (selectedSubjectId == e.subject_id),
                        text: e.title.replaceAll('∞', '"')
                    }));
            });
            if(classSubjectBlock.selectedIndex == 0) classSubjectBlock.firstElementChild.setAttribute('selected', 'selected');
            else classSubjectBlock.removeChild(classSubjectBlock.firstElementChild);
        }

        function addItem() {
            var target = this.parentElement.parentElement;
            var table = target.parentElement;
            var clone = target.cloneNode(true);
            var dateBlock = target.querySelector('.col_start');
            var classBlock = target.querySelector('.col_n');
            var bellBlock = target.querySelector('.col_m');
            var subjectBlock = target.querySelector('.col_name');
            var optionsBlock = target.querySelector('.col_options');

            dateBlock.innerHTML = '';
            dateBlock.append(createEl('input', {
                type: 'date',
                name: 'date',
                id: 'date_input',
                events: {
                    change: function () {
                        getClasses();
                        getBells();
                        getSubjects();
                    }
                }
            }));
            classBlock.innerHTML = '';
            classBlock.append(createEl('select', {
                name: 'classes',
                id: 'class_select',
                children: [
                    createEl('option', {
                        value: '0',
                        disabled: true,
                        selected: true,
                        data: [{id: 0}],
                        text: 'Выбор'
                    })
                ],
                events: {
                    change: function () {
                        getBells();
                        getSubjects();
                    }
                }

            }));
            bellBlock.innerHTML = '';
            bellBlock.append(createEl('select', {
                name: 'bells',
                id: 'bell_select',
                children: [
                    createEl('option', {
                        value: '0',
                        disabled: true,
                        selected: true,
                        data: [{id: 0}],
                        text: 'Выбор'
                    })
                ],
                events: {
                    change: function () {
                        getClasses();
                        getSubjects();
                    }
                }
            }));
            subjectBlock.innerHTML = '';
            subjectBlock.append(createEl('select', {
                name: 'subjects',
                id: 'subject_select',
                children: [
                    createEl('option', {
                        value: '0',
                        disabled: true,
                        selected: true,
                        data: [{id: 0}],
                        text: 'Выбор'
                    })
                ],
                events: {
                    change: function () {
                        getClasses();
                        getBells();
                    }
                }
            }));
            optionsBlock.innerHTML = '';
            optionsBlock.append(createEl('button', {
                text: 'Сохранить',
                events: {
                    click: function () {
                        var date = dateBlock.firstElementChild.value;
                        var bell_id = bellBlock.firstElementChild.options[bellBlock.firstElementChild.selectedIndex].dataset.id;
                        var class_id = classBlock.firstElementChild.options[classBlock.firstElementChild.selectedIndex].dataset.id;
                        var subject_id = subjectBlock.firstElementChild.options[subjectBlock.firstElementChild.selectedIndex].dataset.id;
                        var erTarget = null;
                        if(subject_id == '0') erTarget = subjectBlock.firstElementChild;
                        if(bell_id == '0') erTarget = bellBlock.firstElementChild;
                        if(class_id == '0') erTarget = classBlock.firstElementChild;
                        if(date.length < 1) erTarget = dateBlock.firstElementChild;
                        if(erTarget) erTarget.focus();
                        else {
                            var answer = syncRequest(PATH + '/ajax.php', {request: 'add_lesson', date: date, bell: bell_id, class: class_id, subject: subject_id});
                            answer = JSON.parse(answer);
                            location.reload();
                        }
                    }
                }
            }));
            optionsBlock.append(createEl('button', {
                    text: 'Отмена',
                    events: {
                        click: function () {
                            target.parentElement.removeChild(target);
                            table.append(clone);
                            document.getElementById('add_item').addEventListener('click', addItem);
                        }
                    }
                }));
        }
        function changeItem() {
            var target = this.parentElement.parentElement;
            var table = target.parentElement;
            var clone = target.cloneNode(true);
            var dateBlock = target.querySelector('.col_start');
            var classBlock = target.querySelector('.col_n');
            var bellBlock = target.querySelector('.col_m');
            var subjectBlock = target.querySelector('.col_name');
            var optionsBlock = target.querySelector('.col_options');
            var dd = dateBlock.innerHTML.split('.');
            var dateValue =  dd[2]+'-'+dd[1]+'-'+dd[0];
            var classId = classBlock.dataset.id;
            var bellId = bellBlock.dataset.id;
            var subjectId = subjectBlock.dataset.id;
            var classValue = classBlock.innerHTML;
            var bellValue = bellBlock.innerHTML;
            var subjectValue = subjectBlock.innerHTML;
            dateBlock.innerHTML = '';
            dateBlock.append(createEl('input', {
                type: 'date',
                name: 'date',
                id: 'date_input',
                value: dateValue,
                events: {
                    change: function () {
                        getClasses();
                        getBells();
                        getSubjects();
                    }
                }
            }));
            classBlock.innerHTML = '';
            classBlock.append(createEl('select', {
                name: 'classes',
                id: 'class_select',
                children: [
                    createEl('option', {
                        value: '0',
                        disabled: true,
                        selected: true,
                        data: [{id: 0}],
                        text: 'Выбор'
                    })
                ],
                events: {
                    change: function () {
                        getBells();
                    }
                }

            }));
            bellBlock.innerHTML = '';
            bellBlock.append(createEl('select', {
                name: 'bells',
                id: 'bell_select',
                children: [
                    createEl('option', {
                        value: '0',
                        disabled: true,
                        selected: true,
                        data: [{id: 0}],
                        text: 'Выбор'
                    })
                ],
                events: {
                    change: function () {
                        getClasses();
                        getSubjects();
                    }
                }
            }));
            subjectBlock.innerHTML = '';
            subjectBlock.append(createEl('select', {
                name: 'subjects',
                id: 'subject_select',
                children: [
                    createEl('option', {
                        value: '0',
                        disabled: true,
                        selected: true,
                        data: [{id: 0}],
                        text: 'Выбор'
                    })
                ]
            }));
            getClasses(classId, classValue);
            getBells(bellId, bellValue);
            getSubjects(subjectId, subjectValue);
            optionsBlock.innerHTML = '';
            optionsBlock.append(createEl('button', {
                text: 'Сохранить',
                events: {
                    click: function () {
                        var date = dateBlock.firstElementChild.value;
                        var bell_id = bellBlock.firstElementChild.options[bellBlock.firstElementChild.selectedIndex].dataset.id;
                        var class_id = classBlock.firstElementChild.options[classBlock.firstElementChild.selectedIndex].dataset.id;
                        var subject_id = subjectBlock.firstElementChild.options[subjectBlock.firstElementChild.selectedIndex].dataset.id;
                        var id = target.dataset.id;
                        var erTarget = null;
                        if(subject_id == '0') erTarget = subjectBlock.firstElementChild;
                        if(bell_id == '0') erTarget = bellBlock.firstElementChild;
                        if(class_id == '0') erTarget = classBlock.firstElementChild;
                        if(date.length < 1) erTarget = dateBlock.firstElementChild;
                        if(erTarget) erTarget.focus();
                        else {
                            var answer = syncRequest(PATH + '/ajax.php', {request: 'update_item', table: 'lessons', id: id, data: {date: '"'+date+'"', bell_id: bell_id, class_id: class_id, subject_id: subject_id}});
                            answer = JSON.parse(answer);
                            // console.log(answer);
                            location.reload();
                        }
                    }
                }
            }));
            optionsBlock.append(createEl('button', {
                text: 'Удалить',
                events: {
                    click: function () {
                        var id = this.parentElement.parentElement.dataset.id;
                        postData(PATH+'/ajax.php', {request: 'delete_lesson', id: id}).then(data => {
                            var answer = JSON.parse(data);
                            // console.log(answer);
                            location.reload();
                        });
                    }
                }
            }));
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