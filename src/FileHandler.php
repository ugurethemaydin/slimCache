<?php
namespace UEA\SlimCache;
class FileHandler
{
    private $directory;


    public function __construct ($baseDirectory){
        $this->directory=$baseDirectory;
    }


    /**
     * Saves the File $file to disk in the caches base directory, using a sha has of the route name
     *
     * @param File $file
     * @throws CacheFileSystemException
     */
    public function write (FileStructure $file){
        $success=file_put_contents($this->getNameCachePath($file->getName()), $file->toString());
        if($success === FALSE){
            throw new CacheFileSystemException("Unable to save Cache file to disk in file ".$this->getNameCachePath($file->getName()));
        }
    }


    /**
     * See's if the route provided has a cached version, if it does it returns a file object representing the cache.
     * If no file is present it returns false
     *
     * @param $route
     * @return bool|File
     */
    public function read ($name){
        $name=forceMD5($name);
        $routePath=$this->getNameCachePath($name);
        if(!file_exists($routePath)){
            return FALSE;
        }
        try{
            return FileStructure::fromString(file_get_contents($routePath));
        }catch(\Exception $e){
            //Delete the cache file
            unlink($routePath);
            return FALSE;
        }
    }


    /**
     * Removes the cache for a given route
     *
     * @param $name
     */
    public function delete ($name){
        if(file_exists($this->getNameCachePath($name))){
            unlink($this->getNameCachePath($name));
            return true;
        }
        return false;
    }


    /**
     * Removes all cache entries
     */
    public function deleteAll (){
        foreach(scandir($this->directory) as $file){
            if(!in_array($file, ['.', '..'])){
                unlink($this->directory.'/'.$file);
            }
        }
    }


    /**
     * Provides the file path for a cache file based on the name to the resource
     *
     * @param $name
     * @return string
     */
    public function getNameCachePath ($name){
        return $this->directory.'/'.$name;
    }
}