<?php
/**
 * 验证码类
 */
class yzm
{
	/**
	 * 验证码图片默认宽度
	 */
	private static $width = 60;
	/**
	 * 验证码图片默认高度
	 */
	private static $height = 20;
	/**
	 * 验证码库
	 */
	private static $lib = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	/**
	 * 当前验证码
	 */
	private static $code = null;
	/**
	 * 图片资源
	 */
	private static $im = null;

	/**
	 * 创建一张图片
	 */
	private static final function create(){
		self::$im = imagecreatetruecolor(self::$width, self::$height);
		imagefill(self::$im, 0, 0, self::create_color());
	}

	/**
	 * 生成一个颜色值
	 */
	private static final function create_color(){
		return imagecolorallocate(self::$im, mt_rand(1, 255), mt_rand(1, 255), mt_rand(1, 255));
	}

	/**
	 * 生成干扰点
	 */
	private static final function create_disturb_points(){
		for ($i = 0; $i < 1; $i++) {
			imagesetpixel(self::$im, mt_rand(1, self::$width - 2), mt_rand(1, self::$height - 2), self::create_color());
		}
	}

	/**
	 * 生成干扰线
	 */
	private static final function create_disturb_lines(){
		for ($i = 0; $i < 2; $i++) {
			imagearc(self::$im, mt_rand(0, self::$width), mt_rand(0, self::$height), mt_rand(30, 300), mt_rand(20, 200), 50, 30, self::create_color());
		}
	}

	/**
	 * 生成验证码
	 * @param $num 字符数量(默认是4)
	 */
	private static final function create_code($num = 4){
		$len = strlen(self::$lib);
		for ($i = 0; $i < $num; $i++) {
			self::$code .= self::$lib{mt_rand(0, $len - 1)};
		}
	}

	/**
	 * 生成图片
	 * @param $string 字符串
	 */
	private static final function create_picture($string){
		$x = self::$width / 2 - 20;
		$y = self::$height / 2 - 8;
		imagestring(self::$im, mt_rand(1, 489), $x, $y, $string, self::create_color());
	}

	/**
	 * 获取png格式的验证码图片
	 */
	private static final function get_png(){
		header('Content-type:image/png');
		imagepng(self::$im);
	}

	/**
	 * 获取jpg格式的验证码图片
	 */
	private static final function get_jpg(){
		header('Content-type:image/jpeg');
		imagejpeg(self::$im);
	}

	/**
	 * 获取gif格式的验证码图片
	 */
	private static final function get_gif(){
		header('Content-type:image/gif');
		imagegif(self::$im);
	}

	/**
	 * 显示验证码图片
	 * @param $width 宽
	 * @param $height 高
	 * @param $length 字符串长度
	 * @param $format 图片格式(默认png)
	 * <p>可选项：
	 * <br>PNG
	 * <br>JPG
	 * <br>GIF
	 */
	public static final function show($width = null, $height = null, $length = 4, $format = PNG){
		$width = intval($width);
		$height = intval($height);
		if($width > 0){
			self::$width = $width;
		}
		if($height > 0){
			self::$height = $height;
		}
		self::create();
		self::create_disturb_points();
		self::create_disturb_lines();
		self::create_code($length);
		self::create_picture(self::$code);
		$method = 'get_'.$format;
		self::$method();
		imagedestroy(self::$im);
	}

	/**
	 * 获取验证码
	 * @param $flag 大小写是否敏感(默认否)
	 */
	public static final function get_code($flag = false){
		if(!$flag === true){
			self::$code = strtolower(self::$code);
		}
		return self::$code;
	}
}

//验证码类型常量
define('PNG', 'png');
define('JPG', 'jpg');
define('GIF', 'gif');
?>