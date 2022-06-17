<?php

    error_reporting(E_ALL & ~E_NOTICE);

    require_once 'autoClass.php';
    require_once '../config/config.php';

    use Classes\Schedule;
    use Classes\DatabaseShell;

//-----------------------------------
    //Если тело запроса пустое, то ошибка 403
    if(empty($_POST)){
        header("HTTP/1.1 403 Forbidden");
        exit;
    }

    //Создаем объект для удобной работы с бд
    $database = new DatabaseShell(CONFIG['database']['host'], CONFIG['database']['username'], CONFIG['database']['password'], CONFIG['database']['database']);

    //Получаем данные из тела запроса
    $nameChild = $_POST['nameChild'];
    $classChild = $_POST['classChild'];
    $nameParent = $_POST['nameParent'];
    $phone = $_POST['phone'];
    $schedule = $_POST['schedule'];
    $time;
    $date;

    //Текущая дата (для выборки уроков, у которых дата больше этой)
    $startDate = date("Y-m-d", time());
    
    //График пользователя
    $userSchedule = json_decode($schedule, 1);
    $userSchedule = Schedule::convertSchedule($userSchedule); //Конвертируем график
    /*
    {"8":{"1":1,"2":1,"3":1},"9":{"2":1},"11":{"7":1}}
                    
                    ||
                    \/

    {"1":{"8":{}},"2":{"8":{},"9":{}},"3":{"8":{}},"4":{},"5":{},"6":{},"7":{"11":{}}}
    */

    //Получаем массив данных учителей с их графиком и уроками
    $schedule = new Schedule($database);
    $teachersData = $schedule->getTeachersData();
    $teacherID;

    //Присваиваем учителей пользовательскому графику
    $userSchedule = $schedule->setTeachersToUS($userSchedule);

    //Выбираем свободное и с таким же классом время

    //Свободное время для записи
    $freeHours = $schedule->getFreeHoursUS($userSchedule);
    //Время с таким же классом
    $freeClassHours = $schedule->getFreeClassHoursUS($userSchedule, $classChild);

    //print_r($freeClassHours);
    //print_r($freeHours);
    //print_r($teachersData);
    //exit;

    //Выбираем подходящее время для записи (учитываем запись к уже существующим, с таким же классом)
    if (!empty($freeClassHours) || !empty($freeHours)) {
        $shift = (count($freeClassHours) != 0)? array_key_first($freeClassHours): array_key_first($freeHours);
        //Получаем день пользователя, сдвинув текущую дату
        $date = date("Y-m-d", strtotime($startDate . "+ " . ($shift - 1) . " days"));
        //Присваиваем время записи
        $time = (count($freeClassHours) != 0)? array_key_first($freeClassHours[$shift]): array_key_first($freeHours[$shift]);
        //Присваиваем учителя на это время
        $teacherID = (count($freeClassHours) != 0)? $freeClassHours[$shift][$time][0]: $freeHours[$shift][$time][0];
    }else{
        exit("Time not found");
    }

    $unixTime = strtotime($date . " " . $time . ":00:00");

    //echo($teacherID . ' - ' . $time . ':00 ' . $date);
    //exit;


    //Сохраняем данные в бд

    //Добавляем урок в бд
    $data = [
        'parent_name' => $nameParent,
        'time' => $time,
        'date' => $date,
        'teacher_id' => null,
        'status' => 0,
        'phone' => $phone,
        'child_name' => $nameChild,
        'child_class' => $classChild,
        'unix' => $unixTime,
    ];
    $data['status'] = (!empty($teacherID))? 1: 0;
    $data['teacher_id'] = (!empty($teacherID))? $schedule->getTeacher($teacherID)['user_id']: null;

    $schedule->addNewLesson($data);

    //Получаем id урока
    $lesson = $schedule->getLastAddedRow('lesson_application');
    $lesson_application_id = $lesson['id'];


    //Добавляем урок учителю
    if(!empty($teacherID)){
        $teacher = $schedule->getTeacher($teacherID);

        $teacher_Lessons = explode(',', $teacher['lessons']);
        //Чистим массив от пустых элементов
        foreach ($teacher_Lessons as $key => $value) {
            if(empty($value)){
                unset($teacher_Lessons[$key]);
            }
        }
        $teacher_Lessons[] = $lesson_application_id;
        $teacher_Lessons = implode(',', $teacher_Lessons);

        $schedule->updateTeacherData($teacherID, ['lessons' => $teacher_Lessons]);
    }


    //Добавляем нового пользователя
    $password = random_string(6, 'numbers');

    $data = [
        'parent_name' => $nameParent,
        'phone' => $phone,
        'teacher_id' => 0,
        'leadtype' => 2,
        'classChild' => $classChild,
        'regdate' => date("Y-m-d", (time() + (3 * 3600))),
        'passwd' => md5(md5($password)),
        'copypass' => $password,
        'lesson_application_id' => $lesson_application_id,
    ];
    $data['teacher_id'] = (!empty($teacherID))? $schedule->getTeacher($teacherID)['user_id']: 0;

    $schedule->addNewUser($data);

    //Устанавливаем логин пользователю
    $user = $schedule->getLastAddedRow('users');
    $login = 'u' . $user['id'];
    $schedule->updateUserData($user['id'], ['login' => $login]);

    exit($time . ':00 ' . $date);
//-----------------------------------

    //Функции

    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }

    function adopt($text) {
        return '=?UTF-8?B?'.Base64_encode($text).'?=';
    }

    /* 19.11.2020 ДОБАВИЛИ ФУНКЦИЮ RANDOM STRING */
    function random_string($length, $chartypes) 
    {
        $chartypes_array=explode(",", $chartypes);
        // задаем строки символов. 
        //Здесь вы можете редактировать наборы символов при необходимости
        $lower = 'abcdefghijklmnopqrstuvwxyz'; // lowercase
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // uppercase
        $numbers = '1234567890'; // numbers
        $special = ''; //special characters
        $chars = "";
        // определяем на основе полученных параметров, 
        //из чего будет сгенерирована наша строка.
        if (in_array('all', $chartypes_array)) {
            $chars = $lower . $upper. $numbers . $special;
        } else {
            if(in_array('lower', $chartypes_array))
                $chars = $lower;
            if(in_array('upper', $chartypes_array))
                $chars .= $upper;
            if(in_array('numbers', $chartypes_array))
                $chars .= $numbers;
            if(in_array('special', $chartypes_array))
                $chars .= $special;
        }
        // длина строки с символами
        $chars_length = strlen($chars) - 1;
        // создаем нашу строку,
        //извлекаем из строки $chars символ со случайным 
        //номером от 0 до длины самой строки
        $string = $chars{rand(0, $chars_length)};
        // генерируем нашу строку
        for ($i = 1; $i < $length; $i = strlen($string)) {
            // выбираем случайный элемент из строки с допустимыми символами
            $random = $chars{rand(0, $chars_length)};
            // убеждаемся в том, что два символа не будут идти подряд
            if ($random != $string{$i - 1}) $string .= $random;
        }
        // возвращаем результат
        return $string;
    }

    die();