<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rupetitor - Регистрация</title>
    <link rel="stylesheet" href="css/signup.css">
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/jquery.mask.js"></script>
</head>
    <!-- КОД ТАБЛИЦЫ --> 
    <style>
        .left1 {
            width: 40%;
            float: left;
        }

        .left {
            width: 120px;
            float: left;
        }

        .left table {
            background: #E0ECFF;
        }

        .left td {
            background: #eee;
        }

        .right {
            float: right;
            width: 100%;
            overflow: auto;
        }

        .right table {
            background: #E0ECFF;
            width: 100%;
        }

        .right td {
            background: #fafafa;
            color: #444;
            text-align: center;
            padding: 2px;
        }

        .right td {
            background: #E0ECFF;
        }

        .right td.drop {
            background: #fafafa;
            width: 100px;
        }

        .right td.over {
            background: #FBEC88;
        }

        .item {
            text-align: center;
            border: 1px solid #499B33;
            background: #fafafa;
            color: #444;
            width: 100px;
        }

        .assigned {
            border: 1px solid #BC2A4D;
        }

        .trash {
            background-color: red;
        }

        #lessonsApp td, th {
            text-align: center;
            border: 1px solid grey;
            height: 35px;
        }

        #lessonsApp tr {
            max-height: 40px;
            height: 45px;
        }

        #lessonsApp {
            border-collapse: collapse;
        }

        .del-lesson {
            cursor: pointer;
        }

        .save-lesson {
            cursor: pointer;
        }

    </style>
<style>

    .form-block__first-stage {
        display: block;
        padding: 60px 30px;
    }

    .form-block__second-stage {
        display: none;
        padding: 60px 30px;
    }

    .form-block__schedule {
        display: none;
        padding: 60px 30px;
    }
	
	.form-block__fourth-stage {
        display: none;
        padding: 60px 30px;
    }	

    /*.back-button-schedule img {
        height: 25px;
        width: auto;
    }

    .back-button-schedule {
        background: #86C9FE;
        display: inline-flex;
        height: 50px;
        width: 50px;
        border-radius: 5px;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }*/
    .schedule-buttons {
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
    }

    .submit-button {
        margin-left: 15px;
        cursor: pointer;
    }

    .first-submit, .to-first, .back-button-schedule, .second-submit {
        cursor: pointer;
    }
    .red-placeholder::placeholder {
        color: darkred;
    }

    .form-block__input-block {
        position: relative;
    }
    .error-message {
        display: none;
        position: absolute;
        color: darkred;
        left: 0;
        top: 100%;
    }

    @media (max-width: 1280px) {

        .form-block__schedule {
            padding: 45px 30px;
        }

        .form-block__second-stage {
            padding: 45px 30px;
        }

        .form-block__first-stage {
            padding: 45px 30px;
        }
        .form-block__stage-back {
            font-size: 14px;
            font-weight: 600;
            width: 100px;
            height: 40px;
        }
        .form-block__stage-submit {
            width: 120px;
            height: 40px;
            font-size: 14px;
            font-weight: 600;
        }
        /*.back-button-schedule img {
            height: 15px;
            width: auto;
        }
        .back-button-schedule {
            height: 40px;
            width: 40px;
        }*/
    }

    @media (max-width: 920px) {
        .form-block__schedule {
            padding: 45px 30px;
        }
        .form-block__second-stage {
            padding: 30px 20px;
        }
        .form-block__first-stage {
            padding: 45px 30px;
        }
        /*.back-button-schedule img {
            height: 25px;
            width: auto;
        }
        .back-button-schedule {
            height: 50px;
            width: 50px;
        }*/
        .form-block__stage-back {
            font-size: 20px;
            font-weight: 700;
            width: 150px;
            height: 50px;
        }
        .form-block__stage-submit {
            width: 210px;
            height: 50px;
            font-size: 20px;
            font-weight: 700;
        }
    }

    .popup-cont {
        position: fixed;
        width: 100vw;
        height: 100vh;
        left: 0;
        top: 0;
        display: none;
        justify-content: center;
        align-items: center;
    }
    .popup-over {
        position: absolute;
        left: 0;
        top: 0;
        width: 100vw;
        height: 100vh;
        z-index: 1;
    }
    .popup-content {
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
        padding: 40px 40px;
        /*height: 300px;*/
        background: white;
        z-index: 10;
        border-radius: 6px;

    }

    .popup-message {
        margin-bottom: 15px;
    }

    .popup-content span.close-popup {
        font-size: 18px;
        padding: 8px 20px;
        background: #9cd3ff;
        cursor: pointer;
        box-shadow: 0 0 5px rgba(89, 181, 255, 0.4);
        border-radius: 6px;
        font-weight: 600;
        color: white;
    }

</style>

<!--<div class="popup-cont">
    <div class="popup-over"></div>
    <div class="popup-content">
        <span>Информация добавлена в нашу базу. Скоро с вами свяжется наш специалист.</span>
        <span>ОК</span>
    </div>
</div>-->

<body class="page">

<div class="page__container">
    <a href="/" class="page__logo">
        <img src="img/logo.png" alt="" class="page__logo-image">
        <img src="img/mobile-logo.png" alt="" class="page__logo-mobile-image">
    </a>
    <div class="form-block page__form-block">
        <div class="form-block__offer-block">
            <h1 class="form-block__offer-title">Пройдите бесплатно вводный урок</h1>
            <p class="form-block__offer-description">Ваш ребенок уже запомнит на уроке в 2-4 раза лучше!</p>
            <p class="form-block__offer-item form-block__offer-item_first">Познакомимся</p>
            <p class="form-block__offer-item form-block__offer-item_second">Покажем как проходит урок</p>
            <p class="form-block__offer-item form-block__offer-item_third">Узнаете секрет суперпамяти</p>
        </div>


        <div class="form-block__first-stage">
            <h2 class="form-block__stage-title">Шаг 1 из 2. Ученики</h2>
            <div class="form-block__input-block form-block__child-name-input-block">
                <label class="form-block__label" for="">Имя ученика*</label>
                <input type="text" class="form-block__input form-block__child-name-input">
                <span class="error-message error-message-child-name">Введите имя ученика</span>
            </div>
            <div class="form-block__select-block">
                <label class="form-block__label" for="">Класс</label>
                <div class="form-block__select form-block__child-class-select">
                    <select name="" id="childClass">
                        <option value="дошкольник">дошкольники</option>
                        <option value="1-3">1 - 3 классы</option>
                        <option value="4-11">4 - 11 классы</option>
                    </select>
                </div>
            </div>
            <input class="form-block__stage-submit first-submit" type="submit" value="Далее →">
        </div>


        <div class="form-block__second-stage">
            <h2 class="form-block__stage-title">Шаг 2 из 2. Родители</h2>
            <div class="form-block__input-block ">
                <label class="form-block__label" for="">Имя родителя</label>
                <input type="text" class="form-block__input form-block__parent-name">
                <span class="error-message error-message-parent-name">Введите имя родителя</span>
            </div>
            <div class="form-block__input-block form-block__parent-phone-input-block">
                <label class="form-block__label" for="">Телефон</label>
                <input type="tel" class="form-block__input form-block__parent-phone-input">
                <span class="error-message error-message-phone">Введите телефон</span>
                <span class="error-message error-message-correct-phone">Введите корректный номер телефона</span>
            </div>
            <div class="form-block__button-block">
                <input class="form-block__stage-submit second-submit" type="submit" value="Далее →">
                <button class="form-block__stage-back to-first">Назад</button>
            </div>
            <p class="form-block__description">Оставляя заявку, вы принимаете <a href="privacy.html" target="_blank">оферту</a> и <a href="agreement.html" target="_blank">соглашение</a>
                о конфиденциальности</p>
        </div>
		
		
		
        <div class="form-block__schedule">

            <?php

            $days = [
                1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье'
            ];
            $months = [
                1 => 'Января', 2 => 'Февраля', 3 => 'Марта', 4 => 'Апреля', 5 => 'Мая', 6 => 'Июня', 7 => 'Июля',
                8 => 'Августа', 9 => 'Сентября', 10 => 'Октября', 11 => 'Ноября', 12 => 'Декабря'
            ];

            $monthsOfYear = [
                1, 2, 3, 4, 5, 6, 7,
                8, 9, 10, 11, 12
            ];

            $daysOfWeek = [
                7, 1, 2, 3,
                4, 5, 6
            ];

            $today = date("Y-m-d");

            ?>


            <h2 class="form-block__stage-title">Когда вам удобно заниматься?</h2>
            <div class="form-block__select-block">
                <label class="form-block__label" for="">Выберите день</label>
                <div class="right">
                    <table id=grafic_grid>
                        <tr>
                            <td class="blank"></td>
                            <?php for ($j = 0; $j < 7; $j++) { ?>
                                <td class="title"><?=$days[$daysOfWeek[date("w", (time() + ($j * 3600 * 24)))]]?></td>
                            <?php } ?>
                        </tr>
                        <?php
                        for ($i = 7; $i <= 21; $i++) { ?>
                            <tr>
                                <td class="time"><?= "{$i}:00"; ?></td>
                                <?php for ($j = 1; $j <= 7; $j++) { ?>
                                    <td class="drop" hour="<?= $i; ?>" day="<?= $j; ?>"
                                        onclick="<?= "click_grafic_cell(this,{$i},{$j})" ?>"></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </table>
                    <a href="#" onclick="app.grafic.apply2()" class="easyui-linkbutton"
                       data-options="iconCls:'icon-ok'">Применить</a>
                </div>
            </div>

            <div class="schedule-buttons">
                <!--<span class="back-button-schedule"><img src="img/back-arrow.svg" alt=""></span>-->
                <button class="form-block__stage-back to-first back-button-schedule">Назад</button>
                <input class="form-block__stage-submit submit-button shbutton" type="submit" value="Записаться">
            </div>


            <p class="form-block__description">*Время расписания по Москве</p>
        </div>
		
		
		
        <div class="form-block__fourth-stage form-block__first-stage">
            <h2 class="form-block__stage-title">Ждем Вас на пробном занятии!</h2>
			<p>Мы отправили Вам разовое смс-уведомление с доступами в личный кабинет</p>
			<p>Зарегистрируйтесь за 10-15 минут до начала урока и проверьте работу видеосвязи</p>
            <div class="form-block__button-block">
                <button class="form-block__stage-back to-first" onclick="window.location.href = '/';">На главную</button>
            </div>

        </div>	
		
		
    </div>
</div>

<div class="popup-cont">
    <div class="popup-over"></div>
    <div class="popup-content">
        <span class="popup-message">Мы оптравили разовое смс-уведомление с доступами в личный кабинет</span>
        <span class="close-popup">ОК</span>
    </div>
</div>

<script>

    let popClose =  document.querySelector('.close-popup');

    let popCloseOver =  document.querySelector('.popup-over');

    popClose.onclick = function () {
        $('.popup-cont').fadeOut('slow');
    }

    popCloseOver.onclick = function () {
        $('.popup-cont').fadeOut('slow');
    }

    $(function() {
        $('.form-block__parent-phone-input').mask('8(000)000-00-00');
    });


    $( ".form-block__parent-phone-input" ).keypress(function() {
        document.querySelector('.error-message-phone').style.display = 'none';
        document.querySelector('.error-message-correct-phone').style.display = 'none';
    });

    $( ".form-block__child-name-input" ).keypress(function() {
        document.querySelector('.error-message-child-name').style.display = 'none';
    });

    $( ".form-block__parent-name" ).keypress(function() {
        document.querySelector('.error-message-parent-name').style.display = 'none';
    });


    let toSecondStage = document.querySelector('.first-submit');

    let backToFirstStage = document.querySelector('.to-first');

    let backToSecondStage = document.querySelector('.back-button-schedule');

    let toScheduleStage = document.querySelector('.second-submit');
	
	let toFourthStage = document.querySelector('.shbutton');


    backToFirstStage.onclick = function () {
        document.querySelector('.form-block__first-stage').style.display = 'block';
        document.querySelector('.form-block__second-stage').style.display = 'none';
    }

    toSecondStage.onclick = function () {

        let inputNameChild = document.querySelector('.form-block__child-name-input');

        if(inputNameChild.value != ''){
            document.querySelector('.form-block__first-stage').style.display = 'none';
            document.querySelector('.form-block__second-stage').style.display = 'block';


        } else {
            document.querySelector('.error-message-child-name').style.display = 'block';
        }

    }
	
    toScheduleStage.onclick = function () {
        let inputNameParent = document.querySelector('.form-block__parent-name');

        let inputPhone = document.querySelector('.form-block__parent-phone-input');

        if(inputNameParent.value != '' && inputPhone.value.length == 15){
            document.querySelector('.form-block__schedule').style.display = 'block';
            document.querySelector('.form-block__second-stage').style.display = 'none';

        } else {
            if(inputNameParent.value == ''){
                document.querySelector('.error-message-parent-name').style.display = 'block';
            }
            if(inputPhone.value.length != '' && inputPhone.value.length != 15 ){
                document.querySelector('.error-message-phone').style.display = 'none';
                document.querySelector('.error-message-correct-phone').style.display = 'block';
            }
            if(inputPhone.value.length == '' ){
                document.querySelector('.error-message-phone').style.display = 'block';
                document.querySelector('.error-message-correct-phone').style.display = 'none';
            }
        }
    }

    backToSecondStage.onclick = function () {
        document.querySelector('.form-block__schedule').style.display = 'none';
        document.querySelector('.form-block__second-stage').style.display = 'block';
    }


    let lastSubmit = document.querySelector('.submit-button');

    lastSubmit.onclick = function (){
        let nameChild = document.querySelector('.form-block__child-name-input').value;

        let selectClass = document.getElementById("childClass");

        let classChild = selectClass.value;


        let nameParent = document.querySelector('.form-block__parent-name').value;

        let phone = document.querySelector('.form-block__parent-phone-input').value;

        phone = phone.replace('(', '');
        phone = phone.replace(')', '');
        phone = phone.replace('-', '');
        phone = phone.replace('-', '');
        phone = phone.replace(' ', '');


        let selectDate = document.getElementById("lessonDate");

        let schedule = JSON.stringify(app.grafic.cells);

        let selectTime = document.getElementById("lessonTime");

        $.ajax({
            url: '/ajax-requests/booking-submit.php',
            type: 'POST',
            data: {
                nameChild: nameChild,
                classChild: classChild,
                nameParent: nameParent,
                phone: phone,
                schedule: schedule
            }
        }).done(function (data) {
            console.log(data);
        });


        // $( ".popup-cont" ).css('display', 'flex');

        // $( ".popup-cont" ).fadeIn('slow');


        /*console.log(nameChild, classChild, nameParent,  phone, dateLesson, timeLesson);*/
        
        let inputNameChild = document.querySelector('.form-block__child-name-input');

        if(inputNameChild.value != ''){
            //document.querySelector('.form-block__schedule').style.display = 'none';
            //document.querySelector('.form-block__fourth-stage').style.display = 'block';


        } else {
            document.querySelector('.error-message-child-name').style.display = 'block';
        }
        
    }

    app.grafic.apply2()
    /*
    //Set hours for monday (the first day)
    setHours('<?=$today?>');

    //Update hours
    document.getElementById('lessonDate').addEventListener('change', function() {
        setHours(this.value);
    });

    function setHours($date) {
        //Prepare request
        let xhr = new XMLHttpRequest();
        xhr.open('post', '/ajax-requests/schedule.php');
        xhr.responseType = 'json';

        xhr.onload = function(){
            if(this.status != 200){
                return false;
            }
            //Get json response
            let response = this.response;
            //Sort ASC
            response.sort(sortNums);
            //Get select-input
            let lessonTime = document.getElementById('lessonTime');
            //Remove all options
            lessonTime.innerHTML = '';
            //Check if response is empty
            if(response.length == 0){
                let html = 'Нет свободного времени на этот день';
                let option = new Option(html);
                lessonTime.append(option);
                return true;
            }

            for (var i = 0; i < response.length; i++) {
                //Prepare an hour
                let hour = response[i].toString();
                if(hour.length < 2){
                    hour = '0' + hour;
                }

                //Prepare values
                let html = hour + ':00 - ' + hour + ':45';
                let value = response[i];

                //Create option
                let option = new Option(html, value);

                //Add option
                lessonTime.append(option);
            }
        };
        //Send request
        xhr.send($date);
    }
    //Sort ASC function
    function sortNums(a, b) {
        if (a > b) {
            return 1;
        } else if (b > a) {
            return -1;
        } else {
            return 0;
        }
    }*/
</script>
<script src="js/editmain.js"></script>
</body>
</html>