<?php


class my_db
{
    static function Query($query) {
        $return_data = [];
        if(strlen($query)) {
            $host = 'localhost';
            $user = 'root';
            $password ='';
            $database = 'school';
            // подключаемся к серверу
            $link = mysqli_connect($host, $user, $password, $database);
            $result = mysqli_query($link, $query) or die("Ошибка " . mysqli_error($link));
            mysqli_close($link);
            while($res = mysqli_fetch_array($result))
                $return_data[] = $res;
        }
        return $return_data;
    }
}