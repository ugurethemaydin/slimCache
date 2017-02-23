<?php
/**
 * Created by Metromedya
 * http://metromedya.com
 * User: ugurethemaydin
 * Date: 23/02/2017
 * Time: 00:22
 * FileStructure.php in uea/slimcache
 */
namespace UEA\SlimCache;

use Illuminate\Support\Facades\File;

class FileStructure
{
    private $name;

    private $content;


    public static function create (){
        return new FileStructure();
    }


    /**
     * @param mixed $content
     */
    public function setContent ($content){
        $this->content=$content;
    }


    /**
     * @return mixed
     */
    public function getContent (){
        return $this->content;
    }


    /***/
    public function toString (){
        return json_encode($this->getContent());
    }


    public static function fromString ($content){
        return json_decode($content);
    }


    /**
     * @return mixed
     */
    public function getName (){
        return $this->name;
    }


    /**
     * @param mixed $name
     */
    public function setName ($name){
        $this->name=$name;
    }
}
