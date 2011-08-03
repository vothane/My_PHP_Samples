<?php
/**
 * Performs statistical analysis of data
 *
 * @author Thanh Vo <vothany@hotmail.com>
 */
class Statistics {
	/**
	 * Average or mean value of array
	 * @param array numeric data points
	 * @return float the mean of data
	 * @access public static
	 */
	public static function calcMean($data) {
		if (!is_array($data) || empty($data)) {
			throw new Exception('array is empty');
		}
		return (float) array_sum($data) / (float) count($data);
	}

	public static function sumdiff($data = null, $power = 2, $mean = null) {
		if ($data == null) {
			throw new Exception('data has not been set');
		}
		if (is_null($mean)) {
			throw new Exception('mean has not been set');
		}
		
		$sum_of_diff = 0.0;

		foreach ($data as $val) {
			$sum_of_diff += pow((float)($val - $mean), (float)$power);
		}
		return (float) $sum_of_diff;
	}
	/**
	 * The purpose of measures of dispersion is to find out how spread out 
	 * the data values are on the number line. Another term for these statistics is
	 *  measures of spread.
	 * @param array numeric data points
	 * @param float mean of data default null
	 * @return float variance
	 * @access public static
	 */
	public static function calcVariance($data, $mean = null) {
		if ($data == null) {
			throw new Exception('data has not been set');
		}
		
		$sum_of_diff = self::sumdiff($data, 2, $mean);
		
		$count = count($data);
		
		if ($count == 1) {
			throw new Exception('cannot calculate variance of a singe data point');
		}
		return  (float) ($sum_of_diff / ($count - 1));
	}/*}}}*/

}
?>