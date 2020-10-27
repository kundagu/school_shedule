<?
include('../header.php');

require_once('../my_db.php');

$classes_list = my_db::Query('select * from bells where active = true order by start');
?>
<div class="content">
    <div class="table one_column">
        <div class="trow thead">
            <div class="col col_n">№</div>
            <div class="col col_number">Порядок</div>
            <div class="col col_start">Начало</div>
            <div class="col col_finish">Конец</div>
            <div class="col col_options">Действия</div>
        </div>
        <?
        $i = 1;
        foreach ($classes_list as $item) {
            $hs = intval($item['start']/60);
            $ms = intval($item['start'] % 60);
            $hf = intval($item['finish']/60);
            $mf = intval($item['finish'] % 60);
            ?>
            <div class="trow" data-id="<?=$item['ID']?>">
                <div class="col col_n"><?=$i?></div>
                <div class="col col_number"><?=str_replace('∞', '"', $item['number'])?></div>
                <div class="col col_start"><?=($hs < 10 ? '0':'').$hs.':'.($ms < 10 ? '0':'').$ms?></div>
                <div class="col col_finish"><?=($hf < 10 ? '0':'').$hf.':'.($mf < 10 ? '0':'').$mf?></div>
                <div class="col col_options" data-id="<?=$item['ID']?>"><button>Изменить</button></div>
            </div>
            <?
            $i++;
        }
        ?>
        <div class="trow">
            <div class="col col_n"><?=$i?></div>
            <div class="col col_number">Следующий</div>
            <div class="col col_start">:</div>
            <div class="col col_finish">:</div>
            <div class="col col_options"><button id="add_item">Добавить</button></div>
        </div>
    </div>
</div>
    <script>
        function addItem() {
            var target = this.parentElement.parentElement;
            var table = target.parentElement;
            var clone = target.cloneNode(true);
            var num = parseInt(target.firstElementChild.innerHTML.toString());
            target.innerHTML = '';
            target.append(createEl('div', {
                class: 'col col_n',
                html: num.toString()
            }));
            target.append(createEl('div', {
                class: 'col col_number',
                children: [
                    createEl('input', {
                        type: 'text',
                        id: 'input_number',
                        placeholder: '1 или Первый'
                    })
                ]
            }));
            target.append(createEl('div', {
                class: 'col col_start',
                html: '<input type="number" min="0" max="23" value="08" id="shours">' +
                    '<span>:</span>' +
                    '<input type="number" min="0" max="59" value="00" id="sminutes">'
            }));
            target.append(createEl('div', {
                class: 'col col_finish',
                html: '<input type="number" min="0" max="23" value="08" id="fhours">' +
                    '<span>:</span>' +
                    '<input type="number" min="0" max="59" value="00" id="fminutes">'
            }));
            target.append(createEl('div', {
                class: 'col col_options',
                children: [
                    createEl('button', {
                        text: 'Сохранить',
                        events: {
                            click: function () {
                                var start = parseInt(document.getElementById('shours').value) * 60 + parseInt(document.getElementById('sminutes').value);
                                var finish = parseInt(document.getElementById('fhours').value) * 60 + parseInt(document.getElementById('fminutes').value);
                                var number = document.getElementById('input_number').value;
                                if(number.length > 0) {
                                    var checkResult = syncRequest(PATH+'/ajax.php', {request: 'check_bell', number: number.replaceAll('"', '∞'), start: start, finish: finish});
                                    checkResult = JSON.parse(checkResult);
                                    console.log(checkResult);
                                    switch(checkResult.status) {
                                        case 'collision':
                                            var errors = '';
                                            checkResult.ar_errors.forEach( e => {
                                                switch(e) {
                                                    case 'start':
                                                        errors += 'Наложение начала урока \n';
                                                        break;
                                                    case 'finish':
                                                        errors += 'Наложение конца урока \n';
                                                        break;
                                                    case 'number':
                                                        errors += 'Такой урок уже существует \n';
                                                        break;
                                                    case 'length':
                                                        errors += 'Ошибка продолжительности урока \n';
                                                        break;
                                                }
                                            });
                                            alert(errors);
                                            break;
                                        case 'ok':
                                            postData(PATH+'/ajax.php', {request: 'add_bell', number: number.replaceAll('"', '∞'), start: start, finish: finish}).then(data => {
                                                var answer = JSON.parse(data);
                                                console.log(answer);
                                                if(answer.status == 'ok') {
                                                    table.removeChild(target);
                                                    var hs = Math.floor(start / 60), ms = start % 60, hf = Math.floor(finish / 60), mf = finish % 60;
                                                    table.append(createEl('div', {
                                                        class: 'trow',
                                                        data: [{id: answer.id}],
                                                        children: [
                                                            createEl('div', {
                                                                class: 'col col_n',
                                                                text: num.toString()
                                                            }),
                                                            createEl('div', {
                                                                class: 'col col_number',
                                                                text: number.replaceAll('∞', '"')
                                                            }),
                                                            createEl('div', {
                                                                class: 'col col_start',
                                                                text: (hs < 10 ? '0':'')+ hs + ':' + (ms < 10 ? '0':'') + ms
                                                            }),
                                                            createEl('div', {
                                                                class: 'col col_finish',
                                                                text: (hf < 10 ? '0':'')+ hf + ':' + (mf < 10 ? '0':'') + mf
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
                                                    document.getElementById('add_item').parentElement.parentElement.firstElementChild.innerText = (num+1).toString();
                                                    document.getElementById('add_item').addEventListener('click', addItem);
                                                }
                                            });
                                            break;
                                    }
                                }
                                else document.getElementById('input_number').style.backgroundColor = "#f00";
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
            document.getElementById('input_number').focus();
        }
        function changeItem() {
            var target = this.parentElement.parentElement;
            var table = target.parentElement;
            var num = parseInt(target.firstElementChild.innerHTML.toString());
            var numberBlock = target.querySelector('.col_number');
            var number = numberBlock.innerHTML.toString();
            var startBlock = target.querySelector('.col_start');
            var startStr = startBlock.innerHTML.toString().split(':');
            var finishBlock = target.querySelector('.col_finish');
            var finishStr = finishBlock.innerHTML.toString().split(':');
            var start = 0, finish = 0;
            var optionsBlock = target.querySelector('.col_options');
            var id = optionsBlock.dataset.id;
            numberBlock.innerHTML = '';
            numberBlock.append(createEl('input', {
                type: 'text',
                id: 'input_number',
                value: number,
                placeholder: '1 или Первый'
            }));
            startBlock.innerHTML = '<input type="number" min="0" max="23" value="' + startStr[0] + '" id="shours">' +
                '<span>:</span>' + '<input type="number" min="0" max="59" value="' + startStr[1] + '" id="sminutes">';
            finishBlock.innerHTML = '<input type="number" min="0" max="23" value="' + finishStr[0] + '" id="fhours">' +
                '<span>:</span>' + '<input type="number" min="0" max="59" value="' + finishStr[1] + '" id="fminutes">';
            optionsBlock.innerHTML = '';
            optionsBlock.append(createEl('button', {
                text: 'Сохранить',
                events: {
                    click: function () {
                        var start = parseInt(document.getElementById('shours').value) * 60 + parseInt(document.getElementById('sminutes').value);
                        var finish = parseInt(document.getElementById('fhours').value) * 60 + parseInt(document.getElementById('fminutes').value);
                        var number = document.getElementById('input_number').value;
                        if(number.length > 0) {
                            var checkResult = syncRequest(PATH+'/ajax.php', {request: 'check_bell', number: number.replaceAll('"', '∞'), start: start, finish: finish, id: id});
                            checkResult = JSON.parse(checkResult);
                            console.log(checkResult);
                            switch(checkResult.status) {
                                case 'collision':
                                    var errors = '';
                                    checkResult.ar_errors.forEach( e => {
                                        switch(e) {
                                            case 'start':
                                                errors += 'Наложение начала урока \n';
                                                break;
                                            case 'finish':
                                                errors += 'Наложение конца урока \n';
                                                break;
                                            case 'number':
                                                errors += 'Такой урок уже существует \n';
                                                break;
                                            case 'length':
                                                errors += 'Ошибка продолжительности урока \n';
                                                break;
                                        }
                                    });
                                    alert(errors);
                                    break;
                                case 'ok':
                                    postData(PATH+'/ajax.php', {request: 'update_item', table: 'bells', data: { number: '"'+number.replaceAll('"', '∞')+'"', start: start, finish: finish}, id: id}).then(data => {
                                        var answer = JSON.parse(data);
                                        console.log(answer);
                                        if(answer.status == 'ok') {
                                            var hs = Math.floor(start / 60), ms = start % 60, hf = Math.floor(finish / 60), mf = finish % 60;
                                            numberBlock.innerHTML = number.replaceAll('∞', '"');
                                            startBlock.innerHTML = (hs < 10 ? '0':'')+ hs + ':' + (ms < 10 ? '0':'') + ms;
                                            finishBlock.innerHTML = (hf < 10 ? '0':'')+ hf + ':' + (mf < 10 ? '0':'') + mf;
                                            optionsBlock.innerHTML = '';
                                            optionsBlock.append(createEl('button', {
                                                text: 'Изменить',
                                                events: {
                                                    click: changeItem
                                                }
                                            }));
                                        }
                                    });
                                    break;
                            }
                        }
                        else document.getElementById('input_number').style.backgroundColor = "#f00";
                    }
                }
            }));
            optionsBlock.append(createEl('button', {
                text: 'Удалить',
                events: {
                    click: function () {
                        postData(PATH+'/ajax.php', {request: 'delete_bell', id: id}).then(data => {
                            console.log(data);
                            var answer = JSON.parse(data);
                            if(answer.status == 'ok') {
                                target.parentElement.removeChild(target);
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
            }));
            document.getElementById('input_number').focus();
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