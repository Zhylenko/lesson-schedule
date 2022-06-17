<?php
	namespace Classes;

	use Classes\DatabaseShell;

	interface iSchedule{
		public function __construct(DatabaseShell $link);
		public function getTable($table, $condition);
		public function getLastAddedRow($table = '');

		public function getFreeHours($day);

		public function getTeachersData();
		public function getTeacher($id = '');
		public function updateTeacherData($id = '', $data);

		//US - User Schedule
		public function setTeachersToUS($schedule);
		public function getFreeHoursUS($schedule);
		public function getFreeClassHoursUS($schedule, $classChild);

		public function getSchedule($id = '');
		public function getSchedules();
		public static function convertSchedule($schedule);

		public function getLesson($id = '', $startWeekDay);
		public function addNewLesson($data);

		public function addNewUser($data);
		public function updateUserData($id = '', $data);
	}

	/**
	 * Schedule class
	 */
	class Schedule implements iSchedule
	{
		private $link;

		public function __construct(DatabaseShell $link)
		{
			$this->link = $link;

			return $this;
		}
		
		public function getTable($table, $condition)
		{
			return $this->link->selectAll($table, $condition);
		}

		public function getLastAddedRow($table = '')
		{
			$row = $this->getTable($table, "`id` = LAST_INSERT_ID()");

			return $row[0];
		}

		public function getFreeHours($date)
		{
			$teachers = $this->getTeachersData();
			$freeHours = [];
			$teacher_Schedules = [];
			$weekDay = (date('w', strtotime($date)) != 0)? date('w', strtotime($date)) : '7';

			//Get schedules
			foreach ($teachers as $teacher) {
				$teacher_Schedules[] = $teacher['schedule'][$weekDay];
			}
			//Get free hours
			foreach ($teacher_Schedules as $schedule) {
				foreach ($schedule as $hour => $lessons) {
					if(count($lessons) < 4 && !in_array($hour, $freeHours)){
						$freeHours[] = $hour;
					}
				}
			}
			sort($freeHours);
			return $freeHours;
		}

		public function getTeachersData()
		{
			$teachers = $this->getTable('teachers', '`id`');

			foreach ($teachers as $key => $teacher) {
				//Prepare fields to arrays
				$schedule = $this->getSchedule($teacher['grafic_id']);
				$teachers[$key]['schedule'] = self::convertSchedule($schedule);
				$teachers[$key]['lessons'] = explode(',', $teacher['lessons']);

				//Insert lessons in schedule
				foreach ($teachers[$key]['lessons'] as $lessonId) {

					//$startWeekDay = date("Y-m-d", strtotime("Monday this week"));	//Get last monday date to except old lessons
					$startWeekDay = date("Y-m-d", time());
					
					//Get lesson by id on current week
					$lesson = $this->getLesson($lessonId, $startWeekDay);

					if(!empty($lesson)){
						$date = $lesson['date'];
						$lessonWeekDay = (date('w', strtotime($date)) != 0)? date('w', strtotime($date)) : '7';
						$lessonHour = $lesson['time'];

						if(isset($teachers[$key]['schedule'][$lessonWeekDay][$lessonHour])){
							$teachers[$key]['schedule'][$lessonWeekDay][$lessonHour][] = $lessonId;
						}		
					}
				}
			}

			return $teachers;
		}

		public function getTeacher($id = '')
		{
			return $this->getTable("teachers", "`id` = {$id}")[0];
		}

		public function updateTeacherData($id = '', $data)
		{
			return $this->link->updateTable('teachers', $data, "`id` = {$id}");
		}

		public function setTeachersToUS($schedule){
			//Текущая дата (для выборки уроков, у которых дата больше этой)
    		$startDate = date("Y-m-d", time());

			$teachersData = $this->getTeachersData();
			//Присваиваем учителей пользовательскому графику
		    foreach ($schedule as $shift => $userHours) {
		        //Проверяем, что пользователь указал время в этот день
		        if(count($userHours) != 0){
		            //Получаем день пользователя, сдвинув текущую дату
		            $userDay = date("w", strtotime($startDate . "+ " . ($shift - 1) . " days"));
		            $userDay = ($userDay != 0)? $userDay : '7';

		            //Часы выбранные пользователем в этот день
		            $userHours = array_keys($userHours); 

		            foreach ($userHours as $userHour) {
		                //Ищем учителей на это время 
		                foreach ($teachersData as $key => $teacher) {
		                    //Сверяем время и день, количество уроков в учителя на это время
		                    if(in_array($userHour, array_keys($teacher['schedule'][$userDay])) && count($teacher['schedule'][$userDay][$userHour]) < 4){
		                    	//Устанавливаем ключ учителя в массиве учителей, а не его id!!! (Для более удобной выборки учителей)
		                        $schedule[$shift][$userHour][] = $key;
		                    }
		                }
		            }
		        }
		    }
		    return $schedule;
		}

		public function getFreeHoursUS($schedule){
			//Текущая дата (для выборки уроков, у которых дата больше этой)
    		$startDate = date("Y-m-d", time());

			$teachersData = $this->getTeachersData();
			//Свободное время для записи
    		$freeHours = [];
			foreach ($schedule as $shift => $userHours) {
		        //Проверяем, что пользователь указал время в этот день
		        if(count($userHours) != 0){
		            //Получаем день пользователя, сдвинув текущую дату
		            $userDay = date("w", strtotime($startDate . "+ " . ($shift - 1) . " days"));
		            $userDay = ($userDay != 0)? $userDay : '7';

		            //Просматриваем пользовательское время, чтобы найти подходящее для записи
		            foreach ($userHours as $userHour => $teachers) {
		                //Если пользователь выбрал сегодняшний день, то время записи должно быть больше текущего
		                $unixTimeDate = strtotime($startDate . "+ " . ($shift - 1) . " days") + ($userHour * 3600);

		                if(!empty($teachers) && ($unixTimeDate > (time() + 3600))){
		                    foreach($teachers as $teacherNum){
		                        $teacher = $teachersData[$teacherNum];
		                        $lessonIDs = $teacher['schedule'][$userDay][$userHour];

		                        //Если больше 4 уроков, то пропускаем
		                        if(count($lessonIDs) >= 4){
		                            continue;
		                        }
		                        //Если нет уроков на это время, то добавляем в массив свободных часов
		                        if(count($lessonIDs) == 0){
		                            $freeHours[$shift][$userHour][] = $teacher['id'];
		                            continue;
		                        }
		                    }
		                }
		            }
		        }
		    }
		    return $freeHours;
		}

		public function getFreeClassHoursUS($schedule, $classChild){
			//Текущая дата (для выборки уроков, у которых дата больше этой)
    		$startDate = date("Y-m-d", time());

			$teachersData = $this->getTeachersData();
			//Время с таким же классом
    		$freeClassHours = []; 		
		    foreach ($schedule as $shift => $userHours) {
		        //Проверяем, что пользователь указал время в этот день
		        if(count($userHours) != 0){
		            //Получаем день пользователя, сдвинув текущую дату
		            $userDay = date("w", strtotime($startDate . "+ " . ($shift - 1) . " days"));
		            $userDay = ($userDay != 0)? $userDay : '7';

		            //Просматриваем пользовательское время, чтобы найти подходящее для записи
		            foreach ($userHours as $userHour => $teachers) {
		                //Если пользователь выбрал сегодняшний день, то время записи должно быть больше текущего
		                $unixTimeDate = strtotime($startDate . "+ " . ($shift - 1) . " days") + ($userHour * 3600);

		                if(!empty($teachers) && ($unixTimeDate > (time() + 3600))){
		                    foreach($teachers as $teacherNum){
		                        $teacher = $teachersData[$teacherNum];
		                        $lessonIDs = $teacher['schedule'][$userDay][$userHour];

		                        //Если больше 4 уроков, то пропускаем
		                        //Если нет уроков на это время (свободное время), то пропускаем
		                        if(count($lessonIDs) >= 4 || count($lessonIDs) == 0){
		                            continue;
		                        }

		                        //Сверяем классы, чтобы на один час они были одинаковы
		                        foreach ($lessonIDs as $num => $lessonID) {

		                            //Получаем урок по id, чтобы сверить его класс
		                            $lesson = $this->getLesson($lessonID, $startDate);

		                            //Сверяем класс
		                            if((!empty($lesson)) && ($classChild == $lesson['child_class'])){
		                                $freeClassHours[$shift][$userHour][] = $teacher['id'];
		                            }
		                        }
		                    }
		                }
		            }
		        }
		    }
		    return $freeClassHours;
		}

		public function getSchedule($id = '')
		{
			$schedule = $this->getTable('grafics', "`id` = '{$id}' LIMIT 1");

			$schedule = json_decode($schedule[0]['content'], 1);

			return $schedule;
		}

		public function getSchedules()
		{
			$schedules = $this->getTable('grafics', "`id`");

			foreach ($schedules as $key => $schedule) {
				$schedules[$key] = json_decode($schedule['content'], 1);
			}

			return $schedules;
		}

		//Change days and hours
		public static function convertSchedule($schedule)
		{
			$conSchedule = array_fill(1, 7, []);

			foreach ($schedule as $hour => $days) {
				foreach ($days as $day => $value) {				//Add hour to days
					if(($value != 0) && !in_array($hour, $conSchedule[$day])){
						$conSchedule[$day][$hour] = [];
						ksort($conSchedule[$day]);
					}
				}
			}

			return $conSchedule;
		}

		public function getLesson($id = '', $startWeekDay)
		{
			$lessons = $this->getTable('lesson_application', "(`id` = '{$id}' && `status` = '1') && `date` >= '{$startWeekDay}' LIMIT 1");

			return $lessons[0];
		}

		public function addNewLesson($data)
		{
			return $this->link->save('lesson_application', $data);
		}

		public function addNewUser($data)
		{
			return $this->link->save('users', $data);
		}

		public function updateUserData($id = '', $data)
		{
			return $this->link->updateTable('users', $data, "`id` = {$id}");
		}
	}
?>