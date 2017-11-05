<?php

session_start();

class Verify{

	//宽
	protected $width;

	//高
	protected $height;

	//图片的类型
	protected $imgType;
	//字体的个数
	protected $num;
	//资源
	protected $img;

	protected $type;

	//获取画布上的字符串
	protected $getCode;

	public function __construct($width=100,$height=40,$imgType='png',$num=4,$type=3){

		$this->width=$width;
		$this->height=$height;
		$this->num=$num;
		$this->imgType=$imgType;
		$this->type=$type;
		$this->getCode=$this->getCode();

	}


	public function getCode(){
		$string='';
		switch($this->type){
			case 1:
				$string=join('',array_rand(range(0,9),$this->num));
				break;
			case 2:
				$string=implode('',array_rand(array_flip(range('a','z')),$this->num));
				break;
			case 3:
				for($i=0;$i<$this->num;$i++){
					$rand=mt_rand(0,2);

					switch ($rand) {
						case 0:
							$char=mt_rand(48,57);
							break;
						case 1:
							$char=mt_rand(65,90);
							break;
						case 2:
							$char=mt_rand(97,122);
							break;
					}
					$string.=sprintf("%c",$char);
				}
				break;
		}

		//将验证码的信息存放在sessoion 里面用户验证码的验证
		$_SESSION['yzm']=$string;
		return $string;
	}

	protected function createImg(){
		$this->img=imagecreatetruecolor($this->width, $this->height);
	}

	protected function bgColor(){
		return imagecolorallocate($this->img, mt_rand(130,255), mt_rand(130,255), mt_rand(130,255));
	}


	protected function fontColor(){
		return imagecolorallocate($this->img, mt_rand(0,120), mt_rand(0,120), mt_rand(0,120));

	}


	protected function fill(){
		return imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $this->bgColor());
	}

	protected function pixed(){
		for($i=0;$i<100;$i++){
			imagesetpixel(
				$this->img, 
				mt_rand(0,$this->width),
				 mt_rand(0,$this->height), 
				 $this->fontColor());
		}
	}

	protected function arc(){
		for($i=0;$i<3;$i++){
			imagearc($this->img,
			 mt_rand(10,$this->width), mt_rand(10,$this->height),
			 mt_rand(10,$this->width), mt_rand(10,$this->height),
			 mt_rand(90,270), mt_rand(90,270),

			   $this->fontColor());
		}
	}

	protected function  write(){
		for($i=0;$i<$this->num;$i++){

			$x = ceil($this->width/$this->num) * $i;
			$y = mt_rand(10 , $this->height - 10);

			imagechar($this->img, 5, $x, $y, $this->getCode()[$i], $this->fontColor());

		}
	}

	 public function out(){
	 	$func='image'.$this->imgType;
	 	$header='Content-type:image/'.$this->imgType;


	 	if (function_exists($func)) {
	 		$func($this->img);
	 		header($header);
	 	}else{
	 		exit('不支持图片格式');
	 	}
	 }


	 public function getImg(){

	 	$this->createImg();
	 	$this->fill();
	 	$this->arc();
	 	$this->pixed();
	 	$this->write();
	 	$this->out();
	 }

	 public function __destruct(){
	 	imagedestroy($this->img);
	 }
}


