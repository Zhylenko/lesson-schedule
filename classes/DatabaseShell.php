<?php
	namespace Classes;

	class DatabaseShell
	{
		private $link;
		
		public function __construct($host, $user, $password, $database)
		{
			$this->link = mysqli_connect($host, $user, $password, $database);
			mysqli_query($this->link, "SET NAMES 'utf8'"); // устанавливаем кодировку
		}
		
		public function save($table, $data)
		{
			// сохраняет запись в базу
			$columns = [];
			$values = [];

			foreach ($data as $column => $value) {
				$columns[] = "`$column`";
				$values[] = "'$value'";
			}

			$columns = implode(", ", $columns);
			$values = implode(", ", $values);

			$query = "INSERT INTO `{$table}`({$columns}) VALUES ({$values})";

			$this->link->query($query);
		}
		
		public function del($table, $id)
		{
			// удаляет запись по ее id
			$query = "DELETE FROM `{$table}` WHERE `{$table}`.`id` = '{$id}'";

			$this->link->query($query);
		}
		
		public function delAll($table, $condition)
		{
			// удаляет записи
			$query = "DELETE FROM `{$table}` WHERE {$condition}";

			$this->link->query($query);
		}
		
		public function get($table, $id)
		{
			// получает одну запись по ее id
			$query = "SELECT * FROM `{$table}` WHERE `{$table}`.`id` = '{$id}'";

			$row = $this->link->query($query);
			return mysqli_fetch_all($row, MYSQLI_ASSOC);
		}
		
		public function getAll($table, $ids)
		{
			$rows = [];
			// получает массив записей по их id
			foreach ($ids as $id) {
				$query = "SELECT * FROM `{$table}` WHERE `{$table}`.`id` = '{$id}'";

				$row = $this->link->query($query);
				$rows[] = mysqli_fetch_all($row, MYSQLI_ASSOC);
			}

			return $rows;
		}
		
		public function selectAll($table, $condition)
		{
			// получает массив записей по условию
			$query = "SELECT * FROM `{$table}` WHERE {$condition}";

			$row = $this->link->query($query);

			return mysqli_fetch_all($row, MYSQLI_ASSOC);
		}

		public function querySelect($query)
		{
			$row = $this->link->query($query);

			return mysqli_fetch_all($row, MYSQLI_ASSOC);
		}

		public function updateTable($table, $data, $condition)
		{	
			$queryData = [];
			foreach ($data as $field => $value) {
				$queryData[] = "`{$field}` = '{$value}'";
			}
			$queryData = implode(',', $queryData);

			$query = "UPDATE `{$table}` SET {$queryData} WHERE {$condition}";
			return $this->link->query($query);
		}

		public function count($table, $condition)
		{
			$query = "SELECT COUNT(*) as count FROM `{$table}` WHERE {$condition}";

			$count = $this->link->query($query);

			$result = mysqli_fetch_all($count, MYSQLI_ASSOC);

			return $result[0]['count'];
		}

		public function close()
		{
			mysql_close($this->link);
		}

		public function clearTable($table = '')
		{
			$query = "TRUNCATE TABLE `{$table}`";

			$this->link->query($query);
		}
	}
?>