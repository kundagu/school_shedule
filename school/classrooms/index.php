<?
include('../header.php');

require_once('../my_db.php');

$classes_list = my_db::Query('select * from classrooms where active = true order by title');
?>
<div class="content">
    <div class="table">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_title">Аудитория</div>
            <div class="col col_options">Действия</div>
        </div>
        <?
        $i = 1;
        foreach ($classes_list as $item) {
            ?>
            <div class="trow" data-id="<?=$item['ID']?>">
                <div class="col col_n"><?=$i?></div>
                <div class="col col_title"><?=str_replace('∞', '"', $item['title'])?></div>
                <div class="col col_options" data-id="<?=$item['ID']?>"><button>Изменить</button></div>
            </div>
            <?
            $i++;
        }
        ?>
        <div class="trow">
            <div class="col col_n"><?=$i?></div>
            <div class="col col_title">Новая</div>
            <div class="col col_options"><button id="add_item">Добавить</button></div>
        </div>
    </div>
</div>
    <script>
        function addItem() {
            var target = this.parentElement.parentElement;
            var table = target.parentElement;
            var clone = target.cloneNode(true);
            var number = parseInt(target.firstElementChild.innerHTML.toString());
            target.innerHTML = '';
            target.append(createEl('div', {
                class: 'col col_n',
                HTML: number.toString()
            }));
            target.append(createEl('div', {
                class: 'col col_title',
                children: [
                    createEl('input', {
                        type: 'text',
                        id: 'input_title',
                        placeholder: 'Номер или название удитории',
                        events: {
                            input: function () {
                                if(this.value.length > 49) this.value = this.value.substr(0, 50);
                            }
                        }
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
                                if(inputTitle.value.length > 1) {
                                    var val = inputTitle.value.replaceAll('"', '∞');
                                    console.log(val);

                                    var isFree = syncRequest(PATH+'/ajax.php', {request: 'check_classroom_title', title: val});
                                    isFree = JSON.parse(isFree);
                                    if(isFree.status == 'free') {
                                        postData(PATH+'/ajax.php', {request: 'add_classroom', title: val}).then(data => {
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
                                                            class: 'col col_title',
                                                            text: val.replaceAll('∞', '"')
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
            var titleBlock = parent.querySelector('.col_title');
            var optionsBlock = parent.querySelector('.col_options');
            var id = optionsBlock.dataset.id;
            var value = titleBlock.innerText;
            titleBlock.innerHTML = '';
            titleBlock.append(createEl('input', {
                type: 'text',
                id: 'input_title',
                value: value,
                placeholder: 'Номер и литера',
                events: {
                    input: function () {
                        if(this.value.length > 49) this.value = this.value.substr(0, 50);
                    }
                }
            }));
            optionsBlock.innerHTML = '';
            optionsBlock.append(createEl('button', {
                text: 'Сохранить',
                events: {
                    click: function () {
                        var inputTitle = document.getElementById('input_title');
                        if(inputTitle.value.length > 1) {
                            var val = inputTitle.value.replaceAll('"', '∞');
                                postData(PATH+'/ajax.php', {request: 'update_item', table: 'classrooms', id: id, data: {title: '"'+val+'"'}}).then(data => {
                                    var answer = JSON.parse(data);
                                    switch(answer.status) {
                                        case 'ok':
                                            titleBlock.innerHTML = '';
                                            titleBlock.innerText = answer.obj.title.replaceAll('"', '').replaceAll('∞', '"');
                                            optionsBlock.innerHTML = '';
                                            optionsBlock.append(createEl('button', {
                                                text: 'Изменить',
                                                events: {
                                                    click: changeItem
                                                }
                                            }));
                                            break;
                                        case 'double':
                                            titleBlock.innerHTML = '';
                                            titleBlock.innerText = value.replaceAll('∞', '"');
                                            optionsBlock.innerHTML = '';
                                            optionsBlock.append(createEl('button', {
                                                text: 'Изменить',
                                                events: {
                                                    click: changeItem
                                                }
                                            }));
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
                                            titleBlock.innerHTML = '';
                                            titleBlock.innerText = value.replaceAll('∞', '"');
                                            optionsBlock.innerHTML = '';
                                            optionsBlock.append(createEl('button', {
                                                text: 'Изменить',
                                                events: {
                                                    click: changeItem
                                                }
                                            }));
                                            optionsBlock.dataset.id = answer.obj.ID;
                                            parent.dataset.id = answer.obj.ID;
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
                        postData(PATH+'/ajax.php', {request: 'delete_classroom', id: id}).then(data => {
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