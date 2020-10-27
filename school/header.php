<?$path = 'http://localhost:8080/school'?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="<?=$path?>/css/style.css">
    <title>Школьное расписание</title>
</head>
<body>

<nav>
    <div class="content">
        <a href="/school/" class="menu_item"><p>Главная</p></a>
        <div class="menu_item">
            <p>Справочники</p>
            <div class="menu_list">
                <a href="/school/classrooms/" class="list_item">Аудитории</a>
                <a href="/school/bells/" class="list_item">Звонки</a>
                <a href="/school/classes/" class="list_item">Классы</a>
                <a href="/school/subject/" class="list_item">Предметы</a>
                <a href="/school/teachers/" class="list_item">Преподаватели</a>
            </div>
        </div>
        <div class="menu_item">
            <p>Запросы</p>
            <div class="menu_list">
                <a href="/school/teacher_stat/" class="list_item">Учителя</a>
                <a href="/school/classroom_stat/" class="list_item">Аудитории</a>
                <a href="/school/bell_stat/" class="list_item">Количество предметов</a>
                <a href="/school/lesson_stat/" class="list_item">Список предметов</a>
            </div>
        </div>
        <a href="/school/lessons/" class="menu_item"><p>Расписание</p></a>
    </div>
</nav>
<script>
    const PATH = '<?=$path?>';
</script>
