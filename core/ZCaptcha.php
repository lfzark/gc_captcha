<?php
class ZCaptcha {

	private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';//随机因子
	private $code;//验证码
	private $codelen = 4;//验证码长度
	private $width = 150;//宽度
	private $height = 60;//高度
	private $img;        //图形资源句柄
	private $font;       //指定的字体
	private $fontsize = 21;//指定字体大小
	private $fontcolor;    //指定字体颜色
	private $char_position ;
	private $cn_or_en ;
	private $bg_color ;
	function unicode_decode($name) {

		$pattern = '/([\w]+)|(\\\u([\w]{4}))/i';

		preg_match_all($pattern, $name, $matches);
		if (!empty($matches)) {
			$name = '';
			for ($j = 0; $j < count($matches[0]); $j++) {
				$str = $matches[0][$j];
				if (strpos($str, '\\u') === 0) {
					$code = base_convert(substr($str, 2, 2), 16, 10);
					$code2 = base_convert(substr($str, 4), 16, 10);
					$c = chr($code) . chr($code2);
					$c = iconv('UCS-2', 'GBK', $c);
					$name.= $c;
				} else {
					$name.= $str;
				}
			}
		}
		return $name;
	}
	//构造方法初始化
	public function __construct() {//dirname(__FILE__).
		$this->font = '../fonts/MSYH.TTF';//注意字体路径要写对，否则显示不了图片

	}
	//生成随机码
	private function createCode() {

		$_len = strlen($this->charset);
		$str ='';


		for ($i=0;$i<$this->codelen;$i++) {
			$this->char_position[$i] = strlen($this->code);
			if (mt_rand(0,1) == 1){
				$this->code .= $this->charset[mt_rand(0,$_len-1)];
				$this->cn_or_en[$i] = 1;
			}else{
				$this->cn_or_en[$i] = 2;;
				$this->code .= $this->unicode_decode("\\u" . dechex(rand(0x4e00, 0x9fa5)));
			}
		}

		//$this->code = $this->unicode_decode($this->code);
		//echo $this->code.strlen($this->code);
		//print_r($this->char_position);
	}
	//生成背景
	private function createBg() {

		$this->img = imagecreatetruecolor($this->width, $this->height);
		$this->bg_color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
		imagefilledrectangle($this->img,0,$this->height,$this->width,0,$this->bg_color );
	}

	//生成文字
	private function createFont() {
		$_x = $this->width / $this->codelen;


		for ($i=0;$i<$this->codelen;$i++) {
			if ($this->cn_or_en[$i]==2){
				$codex=iconv("GBK","UTF-8",substr($this->code,$this->char_position[$i],$this->cn_or_en[$i]));
			}
			else{
				$codex = substr($this->code,$this->char_position[$i],$this->cn_or_en[$i]);
			}

			$this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$codex);

			//echo substr($this->code,$this->char_position[$i],$this->cn_or_en[$i]);
		}

	}
	private function distort(){

		$distortion_im = imagecreatetruecolor($this->width, $this->height);

		imagefill($distortion_im, $this->height,$this->width, $this->bg_color );
		for ( $i=0; $i<$this->width; $i++) {
			for ( $j=0; $j<$this->height; $j++) {
				$rgb = imagecolorat($this->img, $i , $j);
				if( (int)($i+20+sin($j/$this->height*2*M_PI)*10) <= imagesx($distortion_im) && (int)($i+20+sin($j/$this->height*2*M_PI)*10) >=0 ) {
					imagesetpixel ($distortion_im, (int)($i+10+sin($j/$this->height*2*M_PI-M_PI*0.5)*3) , $j , $rgb);
				}

			}
		}
		$this->img = $distortion_im;
	}
	//生成线条、雪花
	private function createLine() {
		//线条
		for ($i=0;$i<6;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
		}
		//雪花
		for ($i=0;$i<100;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
			imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
		}
	}


	//输出
	private function outPut() {
		header('Content-type:image/png');
		imagepng($this->img);
		imagedestroy($this->img);
	}
	//对外生成
	public function doimg() {
		$this->createBg();
		$this->createCode();

		$this->createLine();
		$this->createFont();
		//$this->distort();

		$this->outPut();
	}
	//获取验证码
	public function getCode() {
		return strtolower($this->code);
	}
}

?>