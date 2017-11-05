<?php

$config=include 'config.php';
//var_dump($config);
$model=new Model($config);

$model->fields(['user','id','realname'])->table('user')->where('id>5')->group('realname')->order('id')->limit('0,5')->having('id>6')->select();

$data['name']='张三';
$data['cc']=time();
$model->insert($data);

$model->where('id=2')->update($data);

$model->where('id=35')->del();

$model->getByName('所长');
$model->max('id');



class Model{
	//链接
	protected $link;
	//主机名
	protected $host;
	//用户名
	protected $user;
	//密码
	protected $pwd;
	//字符集
	protected $charset;
	//数据库名字
	protected $dbName;
	//表名字
	protected $table="user";
	//前缀
	protected $prefix;
	//字段
	protected $fields;
	//选项
	protected $options;
	//sql 语句
	protected $sql;

	public function __construct($config){

		$this->host=$config['DB_HOST'];
		$this->user=$config['DB_USER'];
		$this->pwd=$config['DB_PWD'];
		$this->charset=$config['DB_CHARSET'];
		$this->dbName=$config['DB_NAME'];
		$this->prefix=$config['DB_PREFIX'];

		//数据库链接
		$this->link=$this->connect();

		//获取表名
		$this->table=$this->getTable();

		//var_dump($this->table);
		//
		$this->fields=$this->getFields();

	}


	public function max($fields){

		if(empty($fields)){
			$fields=$this->fields['_pk'];
		}

		$sql="select MAX($fields) as max from $this->table";
		//var_dump($sql);
		$data=$this->query($sql);

		return $data[0]['max'];
	}



	public function min($fields){

		if(empty($fields)){
			$fields=$this->fields['_pk'];
		}

		$sql="select MIN($fields) as min from $this->table";
		//var_dump($sql);
		$data=$this->query($sql);

		return $data[0]['min'];
	}


	public function sum($fields){

		if(empty($fields)){
			$fields=$this->fields['_pk'];
		}

		$sql="select SUM($fields) as sum from $this->table";
		//var_dump($sql);
		$data=$this->query($sql);

		return $data[0]['sum'];
	}



	public function total($fields){

		if(empty($fields)){
			$fields=$this->fields['_pk'];
		}

		$sql="select COUNT($fields) as c from $this->table";
	//	var_dump($sql);
		$data=$this->query($sql);

		return $data[0]['c'];
	}


	/**
	 * 删除表数据
	 */

	public function del(){
		
		$sql="delete from %TABLE% %WHERE%";
		$sql=str_replace(
			array('%TABLE%','%WHERE%'),
			array(
				$this->parseTable(),
				$this->parseWhere()
				),
			$sql
			);
		//var_dump($sql);

		return $this->exec($sql);
	}

	public function update($data){

		//var_dump($data);
		if(!is_array($data)){
			return false;
		}

		$sql="UPDATE %TABLE% SET %SET% %WHERE%";

		$sql=str_replace(
			array('%TABLE%','%SET%','%WHERE%'),
			array(
				$this->parseTable(),
				$this->parseSet($data),
				$this->parseWhere()

				),
			$sql
			);
		//var_dump($sql);
		return $this->exec($sql,true);
	}

	protected function parseSet($data){
		$string='';
		foreach ($data as $key => $value) {
			$string.=$key.'='."'$value',";
		}
		return rtrim($string,',');
	}

	/**
	 * 插入语句操作封装
	 * @return [type] [description]
	 */
	public function insert ($data){

		//var_dump($data);
		if(!is_array($data)){
			return false;
		}

		$sql="INSERT INTO %TABLE% (%FIELDS%) values(%VALUES%)";

		$sql=str_replace(
			array('%TABLE%','%FIELDS%','%VALUES%'),

			array(
				$this->parseTable(),
				$this->parseAddFields(array_keys($data)),
				$this->parseAddValues(array_values($data))
				),
			$sql
			);

		//var_dump($sql);
		return $this->exec($sql);

	}
	protected function exec($sql,$bool=null){
		if($bool){
			$result=mysqli_query($this->link,$sql);
			if($result){
				return mysqli_affected_rows($this->link);
			}else{
				return false;
			}
		}else{
			$result=mysqli_query($this->link,$sql);

			if ($result) {
				return mysqli_insert_id($this->link);
			}else{
				return false;
			}
		}
	}

	protected function parseAddValues($data){
		$string='';
		foreach ($data as $key => $value) {
				$string.='\''.$value.'\',';
		}

		return rtrim($string,',');

	}
	protected function parseAddFields($data){
		return join(',',$data);
	}


	//查询语句的是的封装	
	public function select(){


		//var_dump($this->options);
		$sql='SELECT %FIELDS% FROM %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%';


		//字符串的替换
		$sql=str_replace(

			array('%FIELDS%','%TABLE%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%'),
			array(
					$this->parseFields(isset($this->options['fields'])?$this->options['fields']:null),
					$this->parseTable(),
					$this->parseWhere(),
					$this->parseGroup(),
					$this->parseHaving(),
					$this->parseOrder(),
					$this->parseLimit()

				),
			$sql

			);
	//	var_dump($sql);
		$data=$this->query($sql);
		//var_dump($data);
		return $data;

	}

	


	protected function parseLimit(){
		$limit='';

		if (empty($this->options['limit'])) {
			$limt='';
		}else{
			if(is_string($this->options['limit'][0])){
				$limit='LIMIT '.$this->options['limit'][0];
			}

			if (is_array($this->options['limit'][0])) {
				$limit='LIMIT '.join(',',$this->options['limit'][0]);
			}
		}

		return $limit;
	}
	protected function parseOrder(){
		$order='';
		if (empty($this->options['order'])) {
			$order='';
		}
		else{
			$order='ORDER BY '.$this->options['order'][0];
		}

		return $order;
	}
	protected function parseHaving(){
		$having='';
		if (empty($this->options['having'])) {
			$having='';
		}
		else{
			$having='HAVING '.$this->options['having'][0];
		}

		return $having;
	}
	protected function parseGroup(){
		$group="";
		if (empty($this->options['group'])) {
			$group='';
		}else{
			$group='GROUP BY '.$this->options['group'][0];
		}

		return $group;
	}
	protected function parseWhere(){
		$where="";
		if (empty($this->options['where'])) {
			$where='';
		}else{
			$where='where '.$this->options['where'][0];
		}

		return $where;
	}

	/**
	 * 处理表名
	 * @return [type] [description]
	 */
	protected function parseTable(){

		$table ='';
		
		if (isset($this->options['table'])) {
			$table=$this->prefix.$this->options['table'][0];
		}else {
			$table=$this->table;
		}
	//	var_dump($table);
		return $table;
	}

	  /**
		 * 处理字段
		 */
	protected function parseFields($options){
		$fields='';
		if(empty($options)){
			$fields='*';
		}else{
			if (is_string($options[0])) {
				$fields=explode(',',$options[0]);
				$tmpArr=array_intersect($fields, $this->fields);

				$fields=join(',',$tmpArr);
			}

			if (is_array($options[0])) {
				$fields=join(',',array_intersect($options[0],$this->fields));
			}
		}

		return $fields;
	}


	public function __call($func,$args){

		//var_dump($func,$args);
		//
		if (in_array($func,['fields',
			'table','where','group','order','limit','having'])) {
			$this->options[$func]=$args;
			return $this;
		}else if(strtolower(substr($func,0,5))){
			$fields=strtolower(substr($func,5));

			return $this->getBy($fields,$args[0]);
		}else{
			exit('格式错误');
		}
	}

	 protected function getBy($fields,$args){
	 	$sql="select * from $this->table where $fields='$args'";
	 	//var_dump($sql);
	 	return $this->query($sql);
	 }

	/**
	 * 处理字段
	 * @return [type] [description]
	 */
	protected function getFields(){
		//缓存文件
		$cacheFile='cache/'.$this->table.'.php';

		if(file_exists($cacheFile)){
			return include $cacheFile;
		}else{
			//查询表结构
			$sql='DESC '.$this->table;
			var_dump($sql);
			$data=$this->query($sql);

		//	var_dump($data);
			$fields=[];

			foreach ($data as $key => $value) {
				//var_dump($value['Field']);
				$fields[]=$value['Field'];
				if($value['Key']=="PRI"){
					$fields['_pk']=$value['Key'];
				}
			}
			$string="<?php \n return ".var_export($fields,true).";?>";
			//将缓存字段文件
			file_put_contents("cache/".$this->table.".php", $string);

			return $fields;
		}
	}

	protected function query($sql){
		$result=mysqli_query($this->link,$sql);
		$data=[];
		if ($result) {
			while($rows=mysqli_fetch_assoc($result)){
				$data[]=$rows;
			}
		}else {
			return false;
		}

		return $data;
	}

	protected function getTable(){

		$table="";

		if(isset($this->table)){
			$table=$this->prefix.$this->table;
		}else{
			
			$table=$this->prefix.strtolower(substr(get_class($this), 0,-5));
		}

		return $table;
	}

	/**
	 * 数据库链接
	 * @return [type] [description]
	 */
	protected function connect(){

		$link=mysqli_connect($this->host,$this->user,$this->pwd);

		if(!$link){
			exit('数据库链接失败');
		}

		mysqli_set_charset($link,$this->charset);

		mysqli_select_db($link,$this->dbName);

		return $link;
	}

	public function __destruct(){

		mysqli_close($this->link);

	}
}

