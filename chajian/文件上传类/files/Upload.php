<?php

class  Upload{

	protected $path='./';
	//准许的mime类型
	protected $allowMime=array('image/jpeg','image/png','image/gif','image/wbmp');

	//准许的的文件后缀名
	protected $allowSub=array('jpg','png','gif','wbmp','jpeg');

	//准许上传文件的大小
	protected $allowSize=200000;

	//文件的错误号
	protected $errorNum;

	//文件的大小
	protected $size;

	//文件的新名字
	protected $newName;

	//文件的原名字
	protected $orgName;

	//随机文件名标志
	protected $isRandName=true;

	//文件的临时名字
	protected $tmpName;

	//前缀
	protected $preFix;

	//文件的后缀
	protected $subFix;
	//上传文件的mime类型
	protected $type;


	public function __construct($array=array()){

			foreach ($array as $key => $value) {
				$keys=strtolower($key);

				if(!array_key_exists($keys, get_class_vars(get_class($this)))){

					continue;
				}

				$this->setOption($keys,$value);
			}
	}


	/**
	 * 文件上传 的方法
	 * @param  [string] $fields 表单 name 值
	 * @return [type]         [description]
	 */
	public function  up($fields){

		//检测文件的路径是否存在
		if(!$this->checkPath()){
			exit('没有上传的文件');
		}

		//获取文件名
		$name=$_FILES[$fields]['name'];
		//文件的类型
		$type=$_FILES[$fields]['type'];
		//临时文件名。
		$tmpName=$_FILES[$fields]['tmp_name'];
		//错误号
		$error=$_FILES[$fields]['error'];
		//文件的大小
		$size=$_FILES[$fields]['size'];


		if($this->setFiles($name,$type,$tmpName,$error,$size)){
			$this->newName=$this->createName();

			//判断文件类型，后缀 大小是够合格
			if ($this->checkMime() && $this->checkSub() && $this->checkSize()) {
				
				//移动文件
				if ($this->move()) {
					return $this->newName;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}

	}

	public function move(){

		//判断是否是上传文件
		if (is_uploaded_file($this->tmpName)) {

			$this->path=rtrim($this->path,'/').'/'.$this->newName;

			if (move_uploaded_file($this->tmpName, $this->path)) {
				
				return true;
			}else{
				$this->setOption('errorNum',-6);
				return false;
			}
		}else{

			return false;
		}
	}

	protected function checkSize(){
		//判断上传的文件是否在范围之内
		if ($this->size>$this->allowSize) {
			
			$this->opention('errorNum',-5);
			return false;
		}else{
			return true;
		}

	}

	protected function checkSub(){

		if(in_array($this->subFix,$this->allowSub)){
			return true;
		}else{
			$this->setOption('errorNum',-4);
			return false;
		}
	}

	protected function checkMime(){

		if(in_array($this->type,$this->allowMime)){
			return true;
		}else{
			$this->setOption('errorNum',-3);
			return false;
		}
	}



	/**
	 * 创建文件名
	 * @return [type] [description]
	 */
	protected function createName(){
		if ($this->isRandName) {
			return $this->preFix.$this->randName();
		}else{
			return $this->preFix.$this->orgName;
		}
	}


	/**
	 * 设置随机文件名
	 * @return [type] [description]
	 */
	protected function randName(){
		return uniqid().'.'.$this->subFix;
	}
	//设置文件信息
	protected function setFiles($name,$type,$tmpName,$error,$size){
		if ($error) {
			$this->setOption('errorNum',$error);
		}else


		$this->orgName=$name;
		$this->type=$type;
		$this->size=$size;
		$this->tmpName=$tmpName;

		//获取文件的后缀
		$arr=explode('.',$name);

		$this->subFix=array_pop($arr);
		return true;
	}
	protected function checkPath(){
		//判断文件的路径是否为空
		if (empty($this->path)) {
			//设置错误号
			$this->setOption('errorNum','-1');
			return false;	
		}else{
			//文件存在并且可写
			if (file_exists($this->path) && is_writable($this->path)) {
				return true;

			}else{

				//文件目录不存在
				$this->path=rtrim($this->path,'/').'/';

				//创建目录，并设置权限
				if(mkdir($this->path, 0777,true)){
					return true;
				}else{
					$this->setOption('errorNum','-2');
					return false;
				}
			}
		}
	}

	protected function getErrorNum(){

		$str = '';
		switch ($this->errorNum) {
			case -1:
				$str = '没有上传文件';
				break;
			case -2:
				$str = '文件夹创建失败';
				break;
			case -3:
				$str = '不准许的mime类型';
				break;
			case -4:
				$str = '不准许的文件的后缀';
				break;
			case -5:
				$str = '不准许的文件的大小';
				break;
			case -6:
				$str = '上传失败';
				break;
			case 1:
				$str = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。 ';
				break;
			case 2:
				$str = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
				break;
			case 3:
				$str = '文件只有部分被上传';
				break;
			case 4:
				$str = '没有文件被上传';
				break;
			case 6:
				$str = '找不到临时文件夹';
				break;
			case 7:
				$str = '文件写入失败';
				break;
			
		}
		return $str;
	}
	protected function setOption($keys,$value){
		$this->$keys=$value;

		//var_dump($keys,$value);
	}

	public function __get($name){
		if ($name=='errorInfo') {
			return $this->getErrorNum();
		}
	}

}

