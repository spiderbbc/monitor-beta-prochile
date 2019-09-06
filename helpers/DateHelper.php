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
    /**
     * [diffForHumans get difference between two date formatter as human read_only]
     * @param  [string / int] $date_1 [unix date format]
     * @param  [string / int] $date_2 [unix date format]
     * @return [string]               [1 day before and two hours]
     */
    public static function diffForHumans($date_1,$date_2){
        $date_1 = new Date((int)$date_1); // date-searched
        $date_2 = new Date((int)$date_2); // end date
        $diff = Date::parse($date_1)->diffForHumans($date_2);
        return $diff;
    }
    /**
     * [asTimestamp get time in unix date]
     * @param  [type] $date [11 julio de 2019 or 11/07/2019 yes is my birthday]
     * @return [string]       [21101054511210 yes is not unix is example]
     */
    public static function asTimestamp($date){
        $date = new \DateTime($date, new \DateTimeZone('America/Santiago'));;
        return $date->getTimestamp();
    }
    /**
     * [isToday take a date and well his function name isToday rigth]
     * @param  [type]  $date [description]
     * @return boolean       [description]
     */
    public static function isToday($date){
        $date = new Date($date);
        return $date->isToday();
    }
}