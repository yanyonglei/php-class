<?php
class Page
{
	//超链接
	protected $url;
	//总条数
	protected $total;
	//总页数
	protected $count;
	//每页显示数
	protected $num;
	//上一页数
	protected $prevNum;
	//下一页数
	protected $nextNum;
	//开始记录数
	protected $startNum;
	//结束记录数
	protected $endNum;
	//当前页
	protected $page;
	//尾页
	protected $last = '尾页';
	//上一页
	protected $up = '上一页';
	//下一页
	protected $down = '下一页';
	//首页
	protected $first = '首页';
	
	//初始化一批成员属性
	public function __construct($url , $total , $num = 3)
	{
		$this->url = $url;
		$this->total = $total;
		$this->num = $num;
		//当前页
		$this->page = isset($_GET['page']) ? $_GET['page'] : 1;
		
		//求出来总页数
		$this->count = $this->getCount();
		
		//求出来上一页数
		$this->prevNum = $this->getPrev();
		
		//求出来下一页数
		$this->nextNum = $this->getNext();
		
		//开始记录数
		$this->startNum = $this->getStart();
		
		//结束记录数
		$this->endNum = $this->getEnd();
		
	}
	
	//处理开始记录数
	protected function getStart()
	{
		return ($this->page - 1) * $this->num + 1;
	}
	//处理结束记录数
	protected function getEnd()
	{
		return min($this->page * $this->num , $this->total);  //  
	}
	
	//首页 上一页 下一页 尾页 从X条记录到X条记录
	
	
	//处理下一页数
	protected function getNext()
	{
		if ($this->page >= $this->count) {
			return false;
		} else {
			return $this->page + 1;
		}
	}
	
	//处理上一页数
	protected function getPrev()
	{
		if ($this->page < 1) {
			 return false;
		} else {
			return $this->page - 1;
		}
	}
	
	
	


	//处理总页数
	protected function getCount()
	{
		return ceil($this->total / $this->num);
	}
	
	//获取偏移量
	public function getOffset()
	{
		return ($this->page - 1) * $this->num;
	}
	
	
	//获取分页
	
	//当前是第X页，共X页，从X条记录到第X条记录，首页 上一页 下一页 尾页
	
	public function getPage(){

			$string='';
			$string.='当前页是第'.$this->page.'页&nbsp;&nbsp;共'.$this->count.'页&nbsp;&nbsp;从'.$this->startNum.'条记录到第'.$this->endNum.'条记录&nbsp;&nbsp;<a href='.$this->url.'page=1>'.$this->first.'</a>';

			//上一页，下一页，
			//
			if($this->prevNum){
				$string.='<a href='.$this->url.'page='.$this->prevNum.'>'.$this->up.'</a>&nbsp;';
			}

			if($this->nextNum){
				$string.='<a href='.$this->url.'page='.$this->nextNum.'>'.$this->down.'</a>&nbsp;';
			}		

			//尾页
			$string.='<a href='.$this->url.'page='.$this->count.'>'.$this->last.'</a>&nbsp;';
			return $string;
	}


}


$p=new Page("http://localhost/php/class/Page2.php?",50);
echo $p->getPage();
