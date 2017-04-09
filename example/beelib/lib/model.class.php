<?php

	class Bee_Db_Adapter{
		private $select_sql;
		public $is_prepare;
		private $prepare_data;
		public function jointString($where){
			$whereData =  array();
			$whereVal = NULL;
			foreach ($where as $key => $val) {
				if (is_string($val)){						
					$val  = '\''.$val.'\'';
				}
				$whereData[] = $val;
				$whereVal .= $key.' = ?'.' AND ';
			}
			$whereVal = rtrim($whereVal,' AND ');
			return $whereVal;
		}

		//处理查询时的的转义。防sql注入
		public function quoteInto($where,$value){      //把输入的值进行转义
			if (is_string($value)){
				$str = preg_replace('/([\\|\/|\'|\"])/','\\\\$1',$value);
			}else {
				$str = $value;
			}
			if (is_string($str)){
					$str = '\''.$str.'\'';
			}
			return str_replace('?',$str,$where);
		}
		//提供查新拼接语句
		public function select($sel){
			if (is_array($sel)){
				$sel =  implode(',',$sel);
			}
			$this->select_sql .= 'SELECT '.$sel;
			return $this;
		}

		public function from($fro){
			if (is_array($fro)){
				$fro =  implode(',',$fro);
			}
			$this->select_sql .= ' FROM '.$fro;
			return $this;
		}
		public function where($whe){
			if (is_array($whe)){
				$this->prepare_data = $whe;
				$whe = $this->jointString($whe);
				$this->is_prepare = true;
				$this->select_sql .= ' WHERE '.$whe;
			}else if(is_string($whe)){
				$this->is_prepare = false;
				$this->select_sql .= ' WHERE '.$whe;
			}else{
				$this->is_prepare = false;
			}
			
			return $this;
		}
		public function group($gro){
			$this->select_sql .= ' GROUP BY '.$gro;
			return $this;
		}
		public function having($hav){
			$this->select_sql .= ' HAVING '.$hav;
			return $this;
		}
		public function order($ord){
			if (is_array($ord)){
				$temp = NULL;
				foreach ($ord as $key => $value) {
					$temp .= $key.' '.$value.',';
				}
				$temp = rtrim(',',$temp);
				$ord = $temp;
			}
			$this->select_sql .= ' ORDER BY '.$ord;
			return $this;
		}
		public function limit($count = 1,$offset = 0){
			$this->select_sql .= ' LIMIT ';
			if ($offset > 0){
				$this->select_sql .= $offset.','.$offset+$count;
			}else {
				$this->select_sql .= $count;
			}
			return $this;
		}
		public function get_sql(){
			return $this->select_sql;
		}
		public function sql_NULL(){
			$this->select_sql = NULL;
		}
		public function get_prepare_data(){
			return $this->prepare_data;
		}
		public function prepare_data_NULL(){
			$this->prepare_data = NULL;
		}
	}

	//使用medoo操作数据库
	//需composer加载medoo库
	class Bee_Db_Medoo extends Medoo\Medoo
	{
		public function __construct(){
			$conf = conf::instance();
			parent::__construct([
			    'database_type' => $conf['Db_type'],
			    'database_name' => $conf['Db_Name'],
			    'server' => $conf['Db_Host'],
			    'username' => $conf['Db_User'],
			    'password' => $conf['Db_pwd'],
			    'charset' => $conf['Db_set']
			]);
		}
	}
	

	//表模型，对表进行操作
	class Bee_Db_Table {
		protected $_name;  //表名
		protected $_primary = 'id'; //主键默认是id
		private $display_error;
		private $pdo;
		private $adapter;
		public function __construct(){
			$this->pdo = Bee_Db_PDO::instance();
			$this->display_error = conf::instance()['Display_Error'];
			$this->adapter = new Bee_Db_Adapter();
			$this->adapter->is_prepare = false;
			
		}
		/*
			PDO的exec操作
			返回数不为0或错误提示则表示操作失败
		*/
		private function pdoExec($sql){
			$stmt = $this->pdo->exec($sql);
			//如果生发sql语法错误。
			if ($this->display_error && $this->pdo->errorInfo()[0] !='00000'){
				 throw new RuntimeException($this->pdo->errorInfo()[2]);
			}
			return $stmt;
		}
		//获取适配器类
		public function getAdapter(){
			if (!isset($this->adapter)){
				$this->adapter = new Bee_Db_Adapter();
			}
			return $this->adapter;
		}
		/*
		返回adapter中生成sql语句的结果
		*/
		public function get_select_res(){
			$sql = $this->adapter->get_sql();
			if ($this->adapter->is_prepare){
				$where = $this->adapter->get_prepare_data();
				$stmt = $this->pdo->prepare($sql);
				$i = 1;
				foreach ($where as $value) {
					$stmt->bindValue($i++, $value);
				}
				$stmt->execute();
            	$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            	$this->adapter->sql_NULL() ;
            	$this->adapter->prepare_data_NULL();
            	$this->adapter->is_prepare = false;
          		return $rs;
			}else{
				$query = $this->pdo->query($sql);
				$rs = $query->fetchAll(PDO::FETCH_ASSOC);
				return $rs;
			}
		}
		//在表中插入数据
		/*$data可为二维数组，进行多条记录插入
			若为多条插入，则返回一个结果状态码数组
			状态码；
			0：插入失败
			-1：传入$data不是数组
			1：操作成功;
		*/
		public function insert($data){
			if (!is_array($data)){
				return -1;
			}
			if (!isset($data[0])){
				$insert_data = array();
				$pdoKey = NULL;
				$pdoVal = NULL;
				foreach ($data as $key => $val) {
					if (is_string($val)){
						$val = '\''.$val.'\'';
					}
					$pdoKey.=$key.',';
					$pdoVal.=$val.',';
				}
				$pdoKey = rtrim($pdoKey,',');
				$pdoVal = rtrim($pdoVal,',');
				$sql = 'INSERT into '.$this->_name.'('.$pdoKey.') VALUE('.$pdoVal.')';
				return  $this->pdoExec($sql);
			}else {
				$res = array();
				foreach ($data as $key =>$value) {
					$res[] = $this->insert($value);
				}
				return $res;
			}
		}
		//更新表中数据
		/*
			默认where已经用quoteInto转义过了。
			此函数更新数据只能更新一条书记；
			想要多条更新可以在继承此类的子类中重写此方法；
			状态码
			-1：传入参数数据类型错误

		*/
		public function update($set,$where){
			if (!is_array($set) || !is_string($where)){
				return -1;
			}if(!isset($set[0])){
					$pdoSet = NULL;
					foreach ($set as $key => $val) {
						if (is_string($val)){
							$val = '\''.$val.'\'';
						}
						if (is_array($val)){
							return -1;
						}
						$pdoSet .= $key.'='.$val.',';
					}
					$pdoSet = rtrim($pdoSet,',');
					$sql = 'UPDATE '.$this->_name.' SET '.$pdoSet.' WHERE '.$where;
					return $this->pdoExec($sql);
			}else return -1;
		}

		//删除表中数据
		/*
			默认where已经用quoteInto转义过了
		*/
		public function delete($where){
			$sql = 'DELETE FROM '.$this->_name.' WHERE '.$where;
			return $this->pdoExec($sql);
		}

		//根据主键单词或多次查询数据
		/*
			如果只查询一次，则返回一维数组，数组类型为关联数组
			若为多次查询，则返回二维数组
		*/
		public function find($primary){
			if(is_array($primary)){
				$res = array();
				foreach ($primary as $value) {
					$res[] = $this->find($value);
				}
				return $res;
			}else {
				$sql = 'SELECT * FROM '.$this->_name.' WHERE '.$this->_primary.'='.$primary;
				$query = $this->pdo->query($sql); 
                //设置结果集返回格式,此处为关联数组,即不包含index下标
          	   	$rs = $query->fetchAll(PDO::FETCH_ASSOC);
             	return $rs[0];
			}
		}

		//取回一条数据 $order为想要查询的字段
		/*
			查询不需要用quoteInto进行字符转义
		*/
		public function fetchRow($where = NULL,$order = '*'){
			return $this->fetchAll($where,$order,1,0)[0];
		}

		//取回多条数据 $count为取回的数据条数，offset是偏移量，原型是limit $offset,$offset+$count
		/*
			返回-1表示参数传入错误
		*/
		public function fetchAll($where,$order,$count = 1,$offset = 0){
			if (!is_array($where) && !is_null($where)){
				return -1;
			}
			if (is_array($order)){
				$order =  implode(',',$order);
			}
			$sql = 'SELECT '.$order.' FROM '.$this->_name;
			if (!is_null($where)){
				$result = $this->getAdapter()->jointString($where);
				$sql .= ' where '.$result;
			}
			if ($offset > 0){
				$sql .=' LIMIT '.$offset.','.$offset+$count;
			}else{
				$sql .=' LIMIT '.$count;
			}
			$stmt = $this->pdo->prepare($sql);
			$i = 1;
			foreach ($where as $value) {
				$stmt->bindValue($i++, $value);
			}
			$stmt->execute();
            $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
          	return $rs;
		}

	}

	class Bee_Db_PDO {
		private $DSN;
	  	private $Db_User;   //用户
	  	private $Db_pwd;	//密码
	  	private $PDO_islong;//长短链接	
	  	private $Db_set;	//数据库编码
	  	private $pdo;	  //pdo实例对象
	  	//私有构造函数 防止被直接实例化
	  	private function __construct() {
	    	$conf = conf::instance();

	    	$this->Db_User = $conf['Db_User'];
	    	$this->Db_pwd = $conf['Db_pwd'];
	    	$this->PDO_islong = $conf['PDO_islong'];
	    	$this->Db_set = $conf['Db_set'];
	    	$this->DSN = 'mysql:host='.$conf['Db_Host'].';dbname='.$conf['Db_Name'];
	    	$this->connect();
	  	}

	  	//私有 空克隆函数 防止被克隆
	  	private function __clone(){}

	  	//静态 实例化函数 返回一个pdo对象
	  	static public function instance(){
	    	static $singleton = NULL;//静态变量 用于存储实例化对象
		    if (is_null($singleton)) {
		      $singleton = new self();
		    }
	    	return $singleton->pdo;
	  	}
	  	private function connect(){
	  		try{
	      		if($this->PDO_islong){
       				$this->pdo = new PDO($this->DSN, $this->Db_User, $this->Db_pwd, array(PDO::ATTR_PERSISTENT => true));
      			}else{
        			$this->pdo = new PDO($this->DSN, $this->Db_User, $this->Db_pwd);
      			}
      			$this->pdo->query('SET NAMES '.$this->Db_set);
	       	} catch(PDOException $e) {
	      		 throw new RuntimeException($e->getMessage());
	    	}
	  	}
	}