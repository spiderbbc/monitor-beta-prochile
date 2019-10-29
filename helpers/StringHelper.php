<?php
namespace app\helpers;

use yii;


use Stringy\Stringy as S;
use function Stringy\create as s;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * StringHelper wrapper for string function.
 *
 */
class StringHelper
{
	
    /**
     * [convert a product string in to array ,delete / and () if exclude words less to 3]
     * @param  [string] $product   [product to convert]
     * @return [array] $data [ej: ['ThinQ','Aurora','Black']]
     */
    public static function structure_product_to_search($product)
    {
        // eliminamos / y -
        $s = trim(preg_replace('/[\W]+/', ' ', $product));
        // convertimos en array 
        $product_exploded = explode(' ',$s);

        $data = [];
        // recorremos el array
        foreach($product_exploded as $product){
            $stringy = S::create($product);
            // contamos si el product a buscar es mayor a 3 palabras o si es un valor numeric
            if(count($stringy) >= 4){
                if(!is_numeric($stringy)){
                    // si no esta en el array para evitar repetidos
                    if(!in_array($product,$data)){
                        $data[] = $product;
                    }
                }
            }
            
        }

        return $data;

    }

    public static function containsAny($sentence,$data){
        return s($sentence)->containsAny($data,false);
    }

}