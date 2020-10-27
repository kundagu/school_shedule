<?
error_reporting(0);
require_once('my_db.php');
$arData = json_decode(file_get_contents('php://input'),1);
$result = [];
switch ($arData['request']) {
    case 'check_class_title':
        $title = $arData['title'];
        if(strlen($title)>0) {
            $answer = my_db::Query('select * from classes where title = "'.$title.'";');
            if($answer) {
                $result = [
                    'status' => 'busy',
                    'id' => $answer[0]['ID']
                ];
            }
            else $result = ['status' => 'free'];
        }
        break;
    case 'check_classroom_title':
        $title = $arData['title'];
        if(strlen($title)>0) {
            $answer = my_db::Query('select * from classrooms where title = "'.$title.'";');
            if($answer) {
                $result = [
                    'status' => 'busy',
                    'id' => $answer[0]['ID']
                ];
            }
            else $result = ['status' => 'free'];
        }
        break;
    case 'check_teacher_name':
        $full_name = $arData['full_name'];
        if(strlen($full_name)>0) {
            $answer = my_db::Query('select * from teachers where full_name = "'.$full_name.'";');
            if($answer) {
                $result = [
                    'status' => 'busy',
                    'id' => $answer[0]['ID']
                ];
            }
            else $result = ['status' => 'free'];
        }
        break;
    case 'add_class':
        $title = $arData['title'];
        if(strlen($title)>0) {
            my_db::Query('insert into classes(title, active) values("'.$title.'", true);');
            $answer = my_db::Query('select * from classes where title = "'.$title.'";');
            if($answer[0]["ID"])
                $result = [
                    'status' => 'ok',
                    'id' => $answer[0]['ID'],

                ];
            else $result = ['status' => 'error'];
        }
        break;
    case 'add_classroom':
        $title = $arData['title'];
        if(strlen($title)>0) {
            my_db::Query('insert into classrooms(title, active) values("'.$title.'", true);');
            $answer = my_db::Query('select * from classrooms where title = "'.$title.'";');
            if($answer[0]["ID"])
                $result = [
                    'status' => 'ok',
                    'id' => $answer[0]['ID'],

                ];
            else $result = ['status' => 'error'];
        }
        break;
    case 'add_teacher':
        $full_name = $arData['full_name'];
        $classroom_id = $arData['classroom_id'];
        if(strlen($full_name)>0) {
            my_db::Query('insert into teachers(full_name, classroom_id, active) values("'.$full_name.'", '.$classroom_id.', true);');
            $answer = my_db::Query('select * from teachers where full_name = "'.$full_name.'";');

            if($answer[0]["ID"])
                $result = [
                    'status' => 'ok',
                    'id' => $answer[0]['ID'],

                ];
            else $result = ['status' => 'error'];
        }
        break;
    case 'update_item':
        $table = $arData['table'];
        $id = $arData['id'];
        $data = $arData['data'];
        $validate_column = $arData['validate'];
        $setString = '';
        $c = count($data) - 1;
        $i = 0;
        $obj_values = ['ID' => $id];
        foreach ($data as $key => $value) {
            $setString .= $key.' = '.$value.($i < $c ? ', ': '');
            $obj_values[$key] = $value;
            $i++;
        }
        //проверка

        if($validate_column)
            $check = my_db::Query('select * from '.$table.' where '.$validate_column.' = '.$data[$validate_column].' and id <> '.$id.';');
        else
            $check = my_db::Query('select * from '.$table.' where '.str_replace(',', ' and', $setString).' and id <> '.$id.';');
        //если нет совпадений
        if($table == 'subject') {
            $old = my_db::Query('select * from subject where ID = '.$id)[0];
            if(intval($old['connection']) > 0 && $old['connection'] != $data['connection'])
                my_db::Query('update subject set connection = 0 where ID = '.$old['connection']);
            if(intval($data['connection'] > 0)) {
                $b = my_db::Query('select * from subject where ID = '.$data['connection'])[0];
                if(intval($b['connection'] > 0))
                    my_db::Query('update subject set connection = 0 where ID = '.$b['connection']);
                my_db::Query('update subject set connection = '.$id.' where ID ='.$data['connection']);
            }
        }
        
        if($table == 'lessons') {
            $old_subject = my_db::Query('select subject_id from lessons where ID = '.$id)[0]['subject_id'];
            if($old_subject != $data['subject_id']) {
                $old = my_db::Query('select * from subject where ID = '.$old_subject)[0];
                if(intval($old['connection']) > 0)
                    my_db::Query('delete from lessons where date = '.$data['date'].' and class_id = '.
                        $data['class_id'].' and bell_id = '.$data['bell_id'].' and subject_id = '.$old['connection']);
                $new = my_db::Query('select * from subject where ID = '.$data['subject_id'])[0];
                if(intval($new['connection']) > 0) {
                    $date = $data['date'];
                    $weekday = date("w", strtotime($date));
                    $s = 'insert into lessons(date, class_id, bell_id, subject_id, weekday) values('.$data['date'].
                        ', '.$data['class_id'].', '.$data['bell_id'].', '.$new['connection'].', '.$weekday.');';
                    $result['req'] = $s;
                    my_db::Query($s);
                }
            }

        }
        if(!$check) {
            my_db::Query('update '.$table.' set '.$setString.' where ID = '.$id);
            $result['query'] = 'update '.$table.' set '.$setString.' where ID = '.$id;
            $result['obj'] = $obj_values;
            $result['status'] = 'ok';
        }
        else
            if($check[0]['active']) {
                $result['id'] = $check[0]['ID'];
                $result['status'] = 'double';
            }
            else {
                my_db::Query('update '.$table.' set active = true where ID = '.$check[0]['ID'].';');
                $obj_values['ID'] =  $check[0]['ID'];
                $result['obj'] = $obj_values;
                $result['status'] = 'reset';
            }
        break;
    case 'delete_class':
        $id = $arData['id'];
        //проверка
        $check = my_db::Query('select * from lessons where class_id = '.$id.';');
        if($check) my_db::Query('update classes set active = false where ID = '.$id.';');
        else my_db::Query('delete from classes where ID = '.$id.';');
        $result['status'] = 'ok';
        break;
    case 'delete_classroom':
        $id = $arData['id'];
        //проверка
        $check = my_db::Query('select * from teachers where classroom_id = '.$id.';');
        if($check) my_db::Query('update classrooms set active = false where ID = '.$id.';');
        else my_db::Query('delete from classrooms where ID = '.$id.';');
        $result['status'] = 'ok';
        break;
    case 'delete_teacher':
        $id = $arData['id'];
        //проверка
        $check = my_db::Query('select * from subject where teacher_id = '.$id.';');
        if($check) my_db::Query('update teachers set active = false where ID = '.$id.';');
        else my_db::Query('delete from teachers where ID = '.$id.';');
        $result['status'] = 'ok';
        break;
    case 'add_bell':
        $number = $arData['number'];
        $start = $arData['start'];
        $finish = $arData['finish'];
        my_db::Query('insert into bells(number, start, finish, active) values("'.$number.'", '.$start.', '.$finish.', true);');
        $answer = my_db::Query('select * from bells where active = true and number = '.$number.';');
        if($answer[0]["ID"])
            $result = [
                'status' => 'ok',
                'id' => $answer[0]['ID'],

            ];
        else $result = ['status' => 'error'];
        break;
    case 'check_bell':
        $id = $arData['id'];
        $number = $arData['number'];
        $start = $arData['start'];
        $finish = $arData['finish'];
        if($start >= $finish) $result['ar_errors'][] = 'length';
        $answer = my_db::Query('select * from bells where active = true'.($id ? ' and ID <> '.$id: '').';');
        $result['status'] = 'ok';
        if($answer)
            foreach ($answer as $item) {
                if($item['number'] == $number) $result['ar_errors'][] = 'number';
                if($item['start'] == $finish || $item['start'] == $start || ($item['start'] < $start && $item['finish'] > $start)) $result['ar_errors'][] = 'start';
                if($item['fifish'] == $finish || $item['finish'] == $start || ($item['finish'] > $start && $item['start'] < $finish)) $result['ar_errors'][] = 'finish';
            }
        if($result['ar_errors']) $result['status'] = 'collision';
        break;
    case 'delete_bell':
        $id = $arData['id'];
        //проверка
        $check = my_db::Query('select * from lessons where bell_id = '.$id.';');
        if($check) my_db::Query('update bells set active = false where ID = '.$id.';');
        else my_db::Query('delete from bells where ID = '.$id.';');
        $result['status'] = 'ok';
        break;
    case 'get_free_classrooms':
        $id = $arData['id'];
        $answer = my_db::Query('select * from classrooms where ID not in (select classroom_id from teachers where active = true) or ID = '.$id.';');
        if($answer) {
            $result['classrooms_list'] = $answer;
            $result['status'] = 'ok';
        }
        else $result['status'] = 'empty';
        break;
    case 'get_teachers':
        $answer = my_db::Query('select * from teachers where active = true;');
        $result['status'] = 'ok';
        $result['teachers_list'] = $answer;
        break;
    case 'check_subject':
        $title = $arData['title'];
        if(strlen($title)>0) {
            $answer = my_db::Query('select * from subject where title = "'.$title.'";');
            if($answer) {
                $result = [
                    'status' => 'busy',
                    'id' => $answer[0]['ID']
                ];
            }
            else $result = ['status' => 'free'];
        }
        break;
    case 'add_subject':
        $connection = $arData['connection'];
        $connection = ($connection > 0 ? $connection: "0");
        $title = $arData['title'];
        $teacher_id = $arData['teacher_id'];
        $coverage = $arData['coverage'];
        if(strlen($title)>0) {

            $r = 'insert into subject(title, coverage, teacher_id, connection, active) values("'.$title.'", '.$coverage.', '.$teacher_id.', '.$connection.', true);';
                my_db::Query($r);
            $answer = my_db::Query('select * from subject where title = "'.$title.'";');
            if($answer[0]["ID"]) {
                $result = [
                    'status' => 'ok',
                    'id' => $answer[0]['ID']
                ];
                if($connection != "0" && $coverage < 1) {
                    $s = 'update subject set connection = '.$result['id'].' where ID = '.$connection;
                    my_db::Query($s);
                }
            }
            else $result = ['status' => 'error'];
        }
        break;
    case 'delete_subject':
        $id = $arData['id'];
        $r = my_db::Query('select * from subject where ID = '.$id);
        //проверка
        $check = my_db::Query('select * from lessons where subject_id = '.$id.' or connection = '.$id.';');
        if($check) my_db::Query('update subject set active = false where ID = '.$id.';');
        else {
            my_db::Query('delete from subject where ID = '.$id.';');
        }
        if(intval($r[0]['connection']) > 0) {
            $s ='update subject set connection = 0 where ID = '.$r[0]['connection'].';';
            $result['b'] = $s;
            my_db::Query($s);
        }
        $result['a'] = $r[0];
        $result['status'] = 'ok';
        break;
    case 'get_bells':
        $lesson = $arData['lesson'];
        $lesson = ($lesson ? $lesson : 0);
        $date = $arData['date'];
        $class = $arData['class'];
        $sum_classes = '1';
        if($class < 1) {
            $s = my_db::Query('select count(*) as c from classes where active = true;')[0];
            $sum_classes = $s['c'];
        }
        $answer = my_db::Query(
            'select ID as bell_id, number as title from bells where active = true and ID not in '.
            '(select bell_id from (select lessons.bell_id as bell_id, sum(subject.coverage) as coverage '.
            'from (select * from lessons where ID <> '.$lesson.' and date = "'.$date.'"'.($class > 0 ? ' and class_id = '.$class:'').
            ') as lessons join subject as subject on lessons.subject_id = subject.ID group by lessons.bell_id '.
            ') as list where coverage >= '.$sum_classes.');'
        );
        $result['bells'] = $answer;
        $result['status'] = 'ok';
        break;
    case 'get_classes':
        $lesson = $arData['lesson'];
        $lesson = ($lesson ? $lesson : 0);
        $date = $arData['date'];
        $bell = $arData['bell'];
        $sum_bells = '1';
        if($bell < 1) {
            $s = my_db::Query('select count(*) as c from bells where active = true;')[0];
            $sum_bells = $s['c'];
        }
        $answer = my_db::Query(
            'select ID as class_id, classes.title as title from classes where  active = true and ID not in '.
            '(select class_id from (select lessons.class_id as class_id, sum(subject.coverage) as coverage from '.
            '(select * from lessons where ID <> '.$lesson.' and date = "'.$date.'"'.($bell > 0 ? ' and bell_id = '.$bell:'').') as lessons '.
            'join (select * from subject) as subject on lessons.subject_id = subject.ID '.
            'where coverage >= '.$sum_bells.' group by lessons.class_id) as list);'
        );
        $result['classes'] = $answer;
        $result['status'] = 'ok';
        break;
    case 'get_subjects':
        $lesson = $arData['lesson'];
        $lesson = ($lesson ? $lesson : 0);
        $date = $arData['date'];
        $bell = $arData['bell'];
        $class = $arData['class'];
        $sum_bells = 1;
        if($bell < 1) {
            $s = my_db::Query('select count(*) as c from bells where active = true;')[0];
            $sum_bells = $s['c'];
        }
        $coverage_limit = 1;
        if($bell > 0 && $class > 0) {
            $s = my_db::Query('select sum(sub.coverage) as cov from lessons '.
                'join subject as sub on lessons.subject_id = sub.ID where lessons.ID <> '.$lesson.
                ' and lessons.date = "'.$date.'" and lessons.bell_id = '.$bell.' and lessons.class_id = '.$class.';')[0];
            $coverage_limit = 1 - $s['cov'];
//            $sum_bells = $coverage_limit;
        }
        $s = 'select subject.ID as subject_id, subject.title as title from subject '.
            'where active = true and subject.coverage <= '.$coverage_limit.' and teacher_id not in ( select ID from '.
            '( select subject.teacher_id as ID, sum(subject.coverage) as coverage from '.
            '(select * from lessons where ID <> '.$lesson.' and date = "'.$date.'"'.($bell > 0 ? ' and bell_id = '.$bell:'').
            ') as lessons join (select * from subject) as subject on subject.ID = lessons.subject_id group by ID) as list'.
            ($sum_bells > 1 ? ' where coverage >= '.$sum_bells: '').');';

        $answer = my_db::Query($s);
        $result['request'] = $s;
        $result['subjects'] = $answer;
        $result['status'] = 'ok';
        break;
    case  'add_lesson':
        $date = $arData['date'];
        $weekday = date("w", strtotime($date));
        $bell = $arData['bell'];
        $class = $arData['class'];
        $subject = $arData['subject'];
        $a = my_db::Query('select * from subject where ID = '.$subject)[0];
        if(intval($a['connection']) > 0)
            my_db::Query('insert into lessons(date, class_id, bell_id, subject_id, weekday) values("'.$date.'", '.$class.', '.$bell.', '.$a['connection'].', '.$weekday.');');
        my_db::Query('insert into lessons(date, class_id, bell_id, subject_id, weekday) values("'.$date.'", '.$class.', '.$bell.', '.$subject.', '.$weekday.');');
        $answer = my_db::Query('select * from lessons where class_id = '.$class.' and bell_id = '.$bell.';');
        if($answer[0]["ID"])
            $result = [
                'status' => 'ok',
                'id' => $answer[0]['ID'],
            ];
        else $result = ['status' => 'error'];
        break;
    case 'delete_lesson':
        $id = $arData['id'];
        $b = my_db::Query('select * from lessons where ID = '.$id)[0];
        $s = 'delete from lessons where date = "'.$b['date'].'" and class_id = '.$b['class_id'].' and bell_id = '.$b['bell_id'];
        $result['query'] = $s;
        my_db::Query($s);
        $result['status'] = 'ok';
        break;
    case 'get_connections':
        $r = my_db::Query('select sub.ID as ID, sub.title as title from subject as sub where sub.connection = 0 and coverage < 1');
        $result['status'] = 'ok';
        $result['connection_list'] = $r;
        break;
    case 'get_teachers_stat':
        $s = 'select teacher.full_name, sub.sub_sum from teachers as teacher '.
            'join (select teacher_id, count(*) as sub_sum from subject group by teacher_id) as sub '.
            'on teacher.ID = sub.teacher_id order by sub.sub_sum desc, teacher.full_name asc';
        $result['teachers_stat'] = $s;
        $result['status'] = 'ok';
        break;
    case 'get_bell_stat':
        $start = $arData['start'];
        $end = $arData['end'];
        $s = 'select les.class_title as class_title, les.lesson_sum as lesson_sum, teach.teacher_count as teacher_count 
                from (
                    select classes.ID as class_id, classes.title as class_title, count(lessons.ID) as lesson_sum 
                    from classes as classes 
                    join (
                        select * 
                        from lessons 
                        where date between "'.$start.'" and "'.$end.'" 
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
                on les.class_id = teach.class_id';
        $answer = my_db::Query($s);
        $result['answer'] = $answer;
        $result['status'] = 'ok';
        break;
    case 'classroom_stat':
        $s = '';
        foreach ($arData['data'] as $item) {
            $s .= (strlen($s) > 0 ? ' or ' : '') . 'weekday = ' . $item;
        }
            $query = 'select classrooms.ID as ID, classrooms.title as title, count(*) as ssum '.
                'from (select * from lessons'.(strlen($s) > 0 ? ' where '.$s:'').
                 ') as lessons join (select * from subject) as subject on lessons.subject_id = subject.ID '.
                'join (select * from teachers) as teachers on subject.teacher_id = teachers.ID join (select * from classrooms) '.
                'as classrooms on classrooms.ID = teachers.classroom_id group by classrooms.ID order by ssum asc';
            $answer = my_db::Query($query);
            $result['status'] = 'ok';
            $result['notes'] = $answer;
        break;
    case 'get_shedule':
        $start = $arData['start'];
        $end = $arData['end'];
        $classes = $arData['classes'];
        $s = '';
        foreach ($classes as $class)
            $s .= (strlen($s) > 0 ? ' or ': ' and (').'lessons.class_id = '.$class;
        $s .= (strlen($s) > 0 ? ')': '');
        $query = 'select lessons.date as date, classes.ID as class_id, lessons.bell_id as bell_id, subject.title as subject_title, teachers.full_name as teacher_name '.
            'from (select * from lessons where date between "'.$start.'" and "'.$end.'"'.$s.') as lessons '.
            'join classes as classes on lessons.class_id = classes.ID join subject as subject '.
            'on lessons.subject_id = subject.ID join teachers as teachers on subject.teacher_id = teachers.ID '.
            'order by lessons.date, classes.title, lessons.bell_id';
        $answer = my_db::Query($query);
        $object = [];
        $assoc = [];
        $w = my_db::Query('select * from bells order by start');
        $i = 0;
        foreach ($w as $value)
            $assoc[$value['ID']] = $i++;
        foreach ($answer as $item) {
            $object[$item['date']][$item['class_id']][$assoc[$item['bell_id']]][] = ['lesson' => $item['subject_title'], 'teacher' => $item['teacher_name']];
        }
        $result['obj'] =$answer;
        $result['query'] =$query;
        $result['timetable'] = $object;
        $result['status'] = 'ok';
}
echo json_encode($result, 1);

