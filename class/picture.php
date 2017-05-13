<?php
/**
 * 图片类
 */
class picture{
	/**
	 * 把大图缩略到缩略图指定的范围内,可能有留白（原图细节不丢失）
	 * @param $f 源图
	 * @param $t 生成目标文件名
	 * @param $tw 目标图宽度
	 * @param $th 目标图高度
	 */
	public static function thumb_stand($f, $t, $tw, $th){
		$temp = array(1=>'gif', 2=>'jpeg', 3=>'png');
		list($fw, $fh, $tmp) = getimagesize($f);
		if(!$temp[$tmp]){
			return false;
		}
		$tmp = $temp[$tmp];
		$infunc = "imagecreatefrom$tmp";
		$outfunc = "image$tmp";
		$fimg = $infunc($f);
		if($fw/$tw > $fh/$th){
			$th = $tw*($fh/$fw);
		} else {
			$tw = $th*($fw/$fh);
		}
		$timg = imagecreatetruecolor($tw, $th);
		imagecopyresampled($timg, $fimg, 0,0, 0,0, $tw,$th, $fw,$fh);
		if($outfunc($timg, $t)){
			return true;
		}
		return false;
	}

	/**
	 * 把大图缩略到缩略图指定的范围内，不留白（原图会居中缩放，把超出的部分裁剪掉）
	 * @param $f 源图
	 * @param $t 生成目标文件名
	 * @param $tw 目标图宽度
	 * @param $th 目标图高度
	 */
	public static function thumb_cut($f, $t, $tw, $th){
		$temp = array(1=>'gif', 2=>'jpeg', 3=>'png');
		list($fw, $fh, $tmp) = getimagesize($f);
		if(!$temp[$tmp]){
			return false;
		}
		$tmp = $temp[$tmp];
		$infunc = "imagecreatefrom$tmp";
		$outfunc = "image$tmp";
		$fimg = $infunc($f);
		if($fw/$tw > $fh/$th){
			$zh = $th;
			$zw = $zh*($fw/$fh);
			$_zw = ($zw-$tw)/2;
		}else{
			$zw = $tw;
			$zh = $zw*($fh/$fw);
			$_zh = ($zh-$th)/2;
		}
		$zimg = imagecreatetruecolor($zw, $zh);
		imagecopyresampled($zimg, $fimg, 0,0, 0,0, $zw,$zh, $fw,$fh);
		$timg = imagecreatetruecolor($tw, $th);
		imagecopyresampled($timg, $zimg, 0,0, 0+$_zw,0+$_zh, $tw,$th, $zw-$_zw*2,$zh-$_zh*2);
		if($outfunc($timg, $t)){
			return true;
		}
		return false;
	}
	
	/**
	 * 把大图缩略到缩略图指定的范围内，不留白（原图会剪切掉不符合比例的右边和下边）
	 * @param $f 源图
	 * @param $t 生成目标文件名
	 * @param $tw 目标图宽度
	 * @param $th 目标图高度
	 */
	public static function thumb_strict($f, $t, $tw, $th){
		$temp = array(1=>'gif', 2=>'jpeg', 3=>'png');
		list($fw, $fh, $tmp) = getimagesize($f);
		if(!$temp[$tmp]){
			return false;
		}
		$tmp = $temp[$tmp];
		$infunc = "imagecreatefrom$tmp";
		$outfunc = "image$tmp";
		$fimg = $infunc($f);
		if($fw/$tw > $fh/$th){
			$fw = $tw * ($fh/$th);
		}else{
			$fh = $th * ($fw/$tw);
		}
		$timg = imagecreatetruecolor($tw, $th);
		imagecopyresampled($timg, $fimg, 0,0, 0,0, $tw,$th, $fw,$fh);
		if($outfunc($timg, $t)){
			return true;
		}
		return false;
	}
}
?>