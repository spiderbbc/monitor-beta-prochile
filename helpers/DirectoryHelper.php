<?php
namespace app\helpers;

use yii;
use yii\helpers\FileHelper;



/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * DirectoryHelper wrapper for Directories
 *
 */
class DirectoryHelper{



	/**
     * @return $path string or boolean
     * create or return the  valid path for save images
     */
    public function setFolderPath($folderOptions)
    {
    	// path to folder flat archives
		$s = DIRECTORY_SEPARATOR;

        $path = \Yii::getAlias($folderOptions['path']).$s. $folderOptions['name']. $s;
        
        
        if (!is_dir($path)) {
           $folder = FileHelper::createDirectory($path, $mode = 0775,$recursive = true);
           return ($folder) ? $path : false; 
        }

        return $path;
    }


}