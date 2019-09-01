<?php
namespace app\helpers;

use yii;
use Jenssegers\Date\Date;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * DateHelper wrapper for time function.
 *
 */
class DateHelper
{
	/**
     * [add plus a date depending in number]
     * @param  [string] $date   [date]
     * @return [string] $number [ej +1 day]
     */
    public static function add($date, $number)
    {
    	$date_formateer = Yii::$app->formatter->asDatetime($date,'yyyy-MM-dd');
		$date_obj = new Date($date_formateer);
		$date_change = $date_obj->add($number);
		$date = (array) $date_change;
		return explode(" ",$date['date'])[0];
    }

    /**
     * [sub plus a date depending in number]
     * @param  [string] $date   [date]
     * @return [string] $number [ej +1 day]
     */
    public static function sub($date, $number)
    {
        $date_formateer = Yii::$app->formatter->asDatetime($date,'yyyy-MM-dd');
        $date_obj = new Date($date_formateer);
        $date_change = $date_obj->sub($number);
        $date = (array) $date_change;
        return explode(" ",$date['date'])[0];
    }

    /**
     * [diffInDays get diffInDays between two date]
     * @param  [string] $date_1   [date ej unix date]
     * @return [string] $date_21  [date ej "Sat Aug 24 14:29:51 +0000 2019"]
     */
    public static function diffInDays($date_1,$date_2){
    	
    	$date_format_1 = Yii::$app->formatter->asDatetime($date_1,'yyyy-MM-dd');
		$date_format_2 = Yii::$app->formatter->asDatetime($date_2,'yyyy-MM-dd');
    	$diff = Date::parse($date_format_1)->floatDiffInDays($date_format_2);
    	
    	return round($diff);
    }

    public static function asTimestamp($date){
        return (int) \Yii::$app->formatter->asTimestamp($date);
    }
}