function createEl(tag, p) {
    let el = document.createElement(tag);
    if (typeof p === "object") {

        // href для ссылок
        if (p.hasOwnProperty('href') && tag === 'a') {
            el.href = p.href;
        }

        // src и alt для картинок
        if (tag === 'img') {
            if (p.hasOwnProperty('src')) {
                el.src = p.src;
            }
            if (p.hasOwnProperty('srcset')) {
                el.srcset = p.srcset.img + " " + p.srcset.size;
            }
            if (p.hasOwnProperty('alt')) {
                el.alt = p.alt;
            }
        }

        // method для форм
        if (tag === 'form') {
            if (p.hasOwnProperty('method')) {
                el.setAttribute('method', p.method);
            }

            // Имя
            if (p.hasOwnProperty('name')) {
                el.name = p.name;
            }
            if (p.hasOwnProperty('autocomplete')) {
                el.setAttribute('autocomplete', (p.autocomplete === 'on') ? 'on' : 'off');
            }
        }

        // for для лейблов
        if (p.hasOwnProperty('for') && tag === 'label') {
            el.setAttribute('for', p.for);
        }

        // Инпуты
        if (tag === 'input') {

            // Тип
            if (p.hasOwnProperty('type')) {
                el.type = p.type;
                if (['text', 'email', 'tel', 'number'].includes(p.type)) {

                    // placeholder
                    if (p.hasOwnProperty('placeholder')) {
                        el.setAttribute('placeholder', p.placeholder);
                    }

                    // required
                    if (p.hasOwnProperty('autofocus') && p.autofocus) {
                        setTimeout(() => {
                            el.focus();
                            //el.setAttribute('autofocus', 'autofocus');
                        }, 400);
                    }
                }


                if (['checkbox', 'radio'].includes(p.type)) {

                    // checked
                    if (p.hasOwnProperty('checked') && p.checked) {
                        el.setAttribute('checked', 'checked');
                    }
                }
            }

            // Имя
            if (p.hasOwnProperty('name')) {
                el.name = p.name;
            }

            // Значение
            if (p.hasOwnProperty('value')) {
                el.value = p.value;
            }

            // required
            if (p.hasOwnProperty('required') && p.required) {
                el.setAttribute('required', 'required');
            }
        }


        if (tag === 'textarea') {
            if (p.hasOwnProperty('name')) {
                el.name = p.name;
            }
        }

        // disabled
        if (p.hasOwnProperty('disabled') && p.disabled) {
            el.setAttribute('disabled', 'disabled');
        }


        if (p.hasOwnProperty('tabindex')) {
            el.setAttribute('tabindex', p.tabindex);
        }

        // option для select
        if (tag === 'option') {
            if (p.hasOwnProperty('selected') && p.selected) {
                el.setAttribute('selected', 'selected');
            }
        }


        if (tag === 'select') {
            if (p.hasOwnProperty('name'))
                el.setAttribute('name', p.name);
        }


        if (p.hasOwnProperty('editable') && p.editable) {
            el.contentEditable = p.editable;
        }

        // ID элемента
        if (p.hasOwnProperty('id')) {
            el.id = p.id;
        }

        // Класс(ы) элемента
        if (p.hasOwnProperty('class')) {
            el.className = p.class;
        }

        // Title элемента
        if (p.hasOwnProperty('title')) {
            el.title = p.title;
        }

        // data элемента
        if (p.hasOwnProperty('data')) {

            // Если передан массив с объектами
            if (Array.isArray(p.data)) {
                p.data.forEach(function(oData) {
                    Object.keys(oData).map(function(objectKey) {
                        el.dataset[objectKey] = oData[objectKey];
                    });
                });
            }
        }


        if (p.hasOwnProperty('html')) {
            el.innerHTML = p.html;
        }


        if (p.hasOwnProperty('text')) {
            el.innerText = p.text;
        }


        if (p.hasOwnProperty('events') && typeof p.events === "object") {
            Object.keys(p.events).map(function(event) {
                el.addEventListener(event, eval(p.events[event]));
            });
        }


        if (p.hasOwnProperty('children') && Array.isArray(p.children)) {
            p.children.forEach(function(item) {
                el.appendChild(item);
            });
        }
    }
    return el;
}

function syncRequest(url, data) {
    let xmlhttp;
    try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (E) {
            xmlhttp = false;
        }
    }
    if (!xmlhttp && typeof XMLHttpRequest !== 'undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    xmlhttp.open( "POST", url, false); //переключатель синхронный/асинхронный
    xmlhttp.setRequestHeader("Content-type", "application/json");
    xmlhttp.setRequestHeader("Accept", "application/json");
    let test = false;
    try {
        xmlhttp.send(JSON.stringify(data));
    } catch (e) {
        test = true;
    }
    if(test) return {'status': 'no'};
// return xmlhttp.responseText; //здесь возвращаем значения без обработки
    return xmlhttp.responseText; //здесь возвращаем значения в виде объекта
}

String.prototype.replaceAll = function(search, replace){
    return this.split(search).join(replace);
}

async function postData(url = '', data = {}) {
    // Default options are marked with *
    let response;
    try {
        response = await fetch(url, {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            credentials: 'same-origin', // include, *same-origin, omit
            headers: {
                'Content-Type': 'application/json'
                // 'Content-Type': 'application/x-www-form-urlencoded',
            },
            redirect: 'follow', // manual, *follow, error
            referrerPolicy: 'no-referrer', // no-referrer, *client
            body: JSON.stringify(data) // body data type must match "Content-Type" header
        });
    } catch (error) {
        console.error('error:'+error);
    }
    return await response.text(); // parses JSON response into native JavaScript objects
}

function animatedScroll(target, delay, margin = 0) {
    var scroll = target.getBoundingClientRect().top - margin;
    var start = null;
    var startScroll = pageYOffset;
    function step(timestamp) {
        if (!start) start = timestamp;
        var progress = timestamp - start;
        window.scrollTo(0, startScroll + scroll / delay * progress);
        if (progress < delay) {
            window.requestAnimationFrame(step);
        }
    }
    window.requestAnimationFrame(step);
}