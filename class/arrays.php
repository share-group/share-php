<?php

/**
 * 数组类
 */
class arrays {
	/**
	 * 合并数组(解决php的array_merge函数使用数字索引时合并后不能使用原来索引的问题)
	 * @param $array(无限参数)
	 * @return 合并后的数组
	 */
	public static function merge($array){
		$params = func_get_args();
		foreach ($params as $p){
			if (!is_array($p)){
				continue;
			}
			foreach ($p as $key => $pp){
				if (!$key || !$pp){
					continue;
				}
				$rs[$key] = $pp;
			}
		}
		return $rs;
	}

	/**
	 * 根据数组的值删除该键
	 * @param $array 数组
	 * @param $value 值(无限参数)
	 */
	public static function delete_by_value($array, $value = ''){
		$value = func_get_args();
		unset ($value[0]);
		foreach ($value as $key => $v){
			if (!$v){
				continue;
			}
			$i = array_search($v, $array);
			if ($i === false){
				continue;
			}
			unset ($array[$i]);
		}
		return $array;
	}

	/**
	 * 二维数组排序
	 * @param $array 要排序数组
	 * @param $keys 基于哪个键来排序
	 * @param $type 升序还是降序(默认升序)
	 */
	private function two_dimension_sort($array, $keys, $type = 'asc'){
		$keysvalue = $new_array = array ();
		foreach ($array as $k => $v){
			$keysvalue[$k] = $v[$keys];
		}
		if ($type == 'asc'){
			asort($keysvalue);
		} else {
			arsort($keysvalue);
		}
		foreach ($keysvalue as $k => $v){
			$new_array[$k] = $array[$k];
		}
		return $new_array;
	}
}
?>