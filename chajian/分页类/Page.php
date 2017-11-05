<?php

class Page{

	//总条数
	protected $total;

	//总页数
	protected $pageCount;

	//每页的显示数
	protected $num;
	//偏移量
	protected $offset;
	//url 超链接
	protected $url;
	//当前页
	protected $page;


	//初始化
 	public function __construct($total,$num=5){
 		//处理总条数
 		$this->total=$this->getTotal($total);
 		//每页显示的数量
 		$this->num=$num;
 		//分页的总页数
 		$this->pageCount=$this->getPageCount();

 		//求出当前页
 		$this->page=$this->getPage();
 		//求偏移量
 		$this->offset=$this->getOffset();

 		//求超链接
 		$this->url=$this->getUrl();

 	}


 	//设置url 地址
 	protected function setUrl($page){

 		if (strstr($this->url,'?') ){

 			return $this->url.'&page='.$page;
 		}else{
 			return $this->url.'?page='.$page;
 		}
		
	}

	//处理首页
	protected  function first(){
		return $this->setUrl(1);
	}


	//上一页
	protected function prev(){

		$page=(($this->page-1)<1)?1:($this->page-1);

		return $this->setUrl($page);
	}

	//下一页
	protected function next(){

		$page=(($this->page+1)>$this->pageCount)? $this->pageCount :($this->page+1);

		return $this->setUrl($page);

	}
	//尾页
	protected function last(){

		return $this->setUrl($this->pageCount);
	}
	
	protected function getUrl(){
		
		//获取文件地址
		$path=$_SERVER['SCRIPT_NAME'];

		//获取主机名
		$host=$_SERVER['HTTP_HOST'];
		//获取端口号
		$port=$_SERVER['SERVER_PORT'];

		//获取协议
		$scheme=$_SERVER['REQUEST_SCHEME'];
		//获取参数
		$queryString=$_SERVER['QUERY_STRING'];

		if (strlen($queryString)) {
			//解析字符串返回数组
			parse_str($queryString,$array);
			unset($array['page']);

			$path=$path.'?'.http_build_query($array);
		}

		//拼接url地址
		$url=$scheme.'://'.$host.':'.$port.$path;
		return $url;

	}

	//处理当前页
	/**
	 * url内获取参数信息
	 * @return [type] [description]
	 */
	protected function getPage(){
		return isset($_GET['page'])?$_GET['page']:1;
	}
	/**
	 * 获取偏移量信息
	 * @return [type] [description]
	 */
	public function getOffset(){
		$start=($this->page-1)*$this->num;

		$limit='limit '.$start.' , '.$this->num;
		return $limit;
	}

	//处理总页数
	/**
	 * 处理分页的总页数方法
	 * @return [type] [description]
	 */
	protected function getPageCount(){
		return ceil($this->total/$this->num);
	}

	//处理总条数
	protected function getTotal($total){

		return ($total<1)?1:$total;
	}

	/**
	 * 给外部暴漏一个数据接口
	 * @return [type] [description]
	 */
	public function render(){
		return [

			'first'=> $this->first(),
			'prev'=> $this->prev(),
			'next'=>$this->next(),
			'last'=>$this->last()
		];
	}

}

/*$page=new Page(10);

$page->render();
*/
