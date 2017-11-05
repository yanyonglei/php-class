<?php
class Image{


	public $path='./';

	//初始化路径
	public function __construct($path='./'){
		$this->path=rtrim($path,'./').'./';
	}

	/**
	 * 水印图
	 * @param  string  $dst        大图路径
	 * @param  string  $src        小图路径
	 * @param  string  $prefix     图片前缀
	 * @param  int $position   图片的位置
	 * @param  integer $opacity    透明度
	 * @param  boolean $isRandName 是否随机命名
	 * @return [type]              [description]
	 */
	public function water($dst,$src,$prefix='water_',$position=9,$opacity=100,$isRandName=true){

		//大图路径
		$dst=$this->path.$dst;
		//小图的路径
		$src=$this->path.$src;

		//判断大图与小图的路径
		if(!file_exists($dst)){
			exit('大图路径不存在');
		}

		if(!file_exists($src)){
			exit('小图的路径不存在');
		}

		//获取文件的信息
		$dstInfo=self::getImageInfo($dst);
		$srcInfo=self::getImageInfo($src);

		if(!$this->checkSize($dstInfo,$srcInfo)){

			exit('小图大于大图的宽高');
		}

		//获取图片的位置
		//
		$position=self::getPosition($dstInfo,$srcInfo,$position);

		var_dump($position);
		//打开图片
		//
		$dstRes=self::openImg($dst,$dstInfo);
		var_dump($dstRes);

		$srcRes=self::openImg($src,$srcInfo);
		var_dump($srcRes);

		//图片的合并
		$newRes=self::mergeImg($dstRes,$srcRes,$srcInfo,$position,$opacity);

		var_dump($newRes);

		if ($isRandName) {
			$path=$this->path.$prefix.uniqid().$dstInfo['name'];
		}else{
			$path=$this->path.$prefix.$dstInfo['name'];
		}


		self::saveImg($path,$newRes,$dstInfo);

		//销毁图片
		imagedestroy($dstRes);
		imagedestroy($srcRes);

	}

	public static function saveImg($path,$newRes,$dstInfo){

		switch ($dstInfo['mime']) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				imagejpeg($newRes,$path);
				break;
			case 'image/png':
			case 'image/x-png':
				imagepng($newRes,$path);
				break;
			case 'image/wbmp':
			case 'image/bmp':
				imagewbmp($newRes,$path);
				break;
			case 'image/gif':
				$res=imagegif($path);
			break;
			
		}
	}
	public  static function mergeImg($dstRes,$srcRes,$srcInfo,$position,$opacity){

		imagecopymerge($dstRes,$srcRes,$position['x'],$position['y'],0,0,$srcInfo['width'],$srcInfo['height'],$opacity);
		return $dstRes;
	}
	public static function openImg($path,$info){
		switch ($info['mime']) {
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$res=imagecreatefromjpeg($path);
				break;
			case 'image/png':
			case 'image/x-png':
				$res=imagecreatefrompng($path);
				break;
			case 'image/wbmp':
			case 'image/bmp':
				$res=imagecreatefromwbmp($path);
				break;
			case 'image/gif':
				$res=imagecreatefromgif($path);
				break;
		}
		return $res;
	}


	public  static function getPosition($dstInfo,$srcInfo,$position){
		switch ($position) {
			case 1:
				$x=0;
				$y=0;

				break;
			case 2:
				$x=($dstInfo['width']-$srcInfo['width'])/2;
				$y=0;
				break;
			case 3:
				$x=$dstInfo['width']-$srcInfo['width'];
				$y=0;
				break;
			case 4:
				$x=0;
				$y=($dstInfo['height']-$srcInfo['height'])/2;
				break;
				
			case 5:
				$x=($dstInfo['width']-$srcInfo['width'])/2;
				$y=($dstInfo['height']-$srcInfo['height'])/2;
				break;
			case 6:
				$x=($dstInfo['width']-$srcInfo['width']);
				$y=($dstInfo['height']-$srcInfo['height'])/2;
				break;
			case 7:
				$x=0;
				$y=$dstInfo['height']-$srcInfo['height'];
				break;
			case 8:
				$x=($dstInfo['width']-$srcInfo['width'])/2;
				$y=$dstInfo['height']-$srcInfo['height'];
				break;
			case 9:
				$x=$dstInfo['width']-$srcInfo['width'];
				$y=$dstInfo['height']-$srcInfo['height'];
				break;

			default:
				$x=mt_rand(0,$dstInfo['width']-$srcInfo['width']);
				$y=mt_rand(0,$dstInfo['height']-$srcInfo['height']);
				break;
		}

		return [
			'x'=>$x,
			'y'=>$y
		];
	}
	public function checkSize($dstInfo,$srcInfo){

		if ($dstInfo['width']<$srcInfo['width']) {
			return false;
		}

		if ($dstInfo['height']<$srcInfo['height']) {
			return false;
		}

		return true;

	}

	public static  function getImageInfo($path){

		$data=getimagesize($path);

		$info['width']=$data[0];
		$info['height']=$data[1];

		$info['mime']=$data['mime'];
		$info['name']=basename($path);
		return $info;
	}


	public function thumb($img,$width,$height,$prefix="thumb_"){
		if (!file_exists($img)) {
			exit('文件路径不存在');
		}

		$info=self::getImageInfo($img);
		$newSize=self::getNewSize($width,$height,$info);
		$res=self::openImg($img,$info);
		
		
		$newRes=self::kidOfImage($res,$newSize,$info);
		$newPath = $this->path.$prefix.$info['name'];
		self::saveImg($newPath,$newRes,$info);

		imagedestroy($newRes);
		return $newPath;
	}

	private static function kidOfImage($srcImg, $size, $imgInfo)
	{
		$newImg = imagecreatetruecolor($size["width"], $size["height"]);		
		$otsc = imagecolortransparent($srcImg);
		if ( $otsc >= 0 && $otsc < imagecolorstotal($srcImg)) {
			 $transparentcolor = imagecolorsforindex( $srcImg, $otsc );
				 $newtransparentcolor = imagecolorallocate(
				 $newImg,
				 $transparentcolor['red'],
					 $transparentcolor['green'],
				 $transparentcolor['blue']
			 );

			 imagefill( $newImg, 0, 0, $newtransparentcolor );
			 imagecolortransparent( $newImg, $newtransparentcolor );
		}

	
		imagecopyresized( $newImg, $srcImg, 0, 0, 0, 0, $size["width"], $size["height"], $imgInfo["width"], $imgInfo["height"] );
		imagedestroy($srcImg);
		return $newImg;
	}
	
	private static function getNewSize($width, $height, $imgInfo)
	{	
		//将原图片的宽度给数组中的$size["width"]
		$size["width"] = $imgInfo["width"];   
		//将原图片的高度给数组中的$size["height"]
		$size["height"] = $imgInfo["height"];  
		
		if($width < $imgInfo["width"]) {
			//缩放的宽度如果比原图小才重新设置宽度
			$size["width"] = $width;             
		}

		if ($width < $imgInfo["height"]) {
			//缩放的高度如果比原图小才重新设置高度
			$size["height"] = $height;       
		}

		if($imgInfo["width"]*$size["width"] > $imgInfo["height"] * $size["height"]) {
			$size["height"] = round($imgInfo["height"] * $size["width"] / $imgInfo["width"]);
		} else {
			$size["width"] = round($imgInfo["width"] * $size["height"] / $imgInfo["height"]);
		}

		return $size;
	}
}

$img=new Image();

$img->water('luhan.jpg','yx.jpg','888_',10,50);

$img->thumb('nwt.jpeg' , 100 , 100 , 'thumb1_');
$img->thumb('nwt.jpeg' , 200 , 200 , 'thumb2_');
$img->thumb('nwt.jpeg' , 300 , 300 , 'thumb3_');
$img->thumb('nwt.jpeg' , 500 , 500 , 'thumb4_');