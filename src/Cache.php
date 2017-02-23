<?php
namespace UEA\SlimCache;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

/**
 * Class FileCache
 *
 * @package UEA\SlimCache
 */
class Cache
{
    const HOUR=3600;

    const DAY=86400;

    const WEEK=604800;

    const NEVER=-1;

    private $directory;

    private $uniqueFileName=NULL;

    public $callFuncName=NULL;

    private $app;

    /**
     * Stores all the options passed to the rule
     */
    private $options=[
        "filePath"=>__DIR__.'/../../../cache/',
        "app"     =>NULL,
    ];


    /**
     * Create a new middleware instance
     * Takes the slim app and then the directory where the cache is to be saved
     *
     * @param string[] $options
     */
    public function __construct (array $options=[]){
        $this->hydrate($options);
        if(NULL !== ($this->options["filePath"])){
            $cacheDirectory=$this->options['filePath'];
        }
        if(NULL !== ($this->options["app"])){
            $this->app=$this->options["app"];
        }
        // if(is_null($cacheDirectory)){
        //     $cacheDirectory=__DIR__.'/../cache/';
        // }
        $this->directory=$cacheDirectory;
        $this->fileHandler=new FileHandler($this->directory);
    }


    /**
     * Slim required __invoke magic method for middleware
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface $response     PSR7 response
     * @param  callable $next                                    Next middleware
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke (ServerRequestInterface $request, ResponseInterface $response, $next){
        $requestPath=$request->getUri()->getPath();
        /** @var File $cache */
        $cache=$this->get($requestPath);
        dd($cache);
        if($cache instanceof File){
            $response=$response->withStatus($cache->getStatus());
            foreach($cache->getHeaders() as $header=>$value){
                $response=$response->withHeader($header, $value);
            }
            $response->getBody()->write($cache->getContent());
            return $response;
        }
        $response=$next($request, $response);
        return $response;
    }


    public function get ($cacheKey){
        return $this->fileHandler->read($cacheKey);
    }
    /**
     * Returns the cached string for the given cacheKey
     *
     * @param $cacheKey
     * @return bool|File
     */
    // public function get ($x=NULL, $y=NULL, $z=NULL){
    //     $fileName=$this->getFileName();
    //     if(is_callable($x)){
    //         $functionVal=$x;
    //         is_integer($y) ? $this->_cacheTime=$y : 0;
    //     }else{
    //         if(is_integer($x)){
    //             $this->_cacheTime=$x;
    //         }else{
    //             $fileName=$x;
    //             !is_null($z) ? $this->_cacheTime=$z : 0;
    //         }
    //         $functionVal=$y;
    //     }
    //     //pre($this->_cache_path.$fileName);
    //     $return=$this->ci->cache->file->get($fileName);
    //     //pre($return);
    //     if($this->_debug && $return){
    //         pre([
    //             'cacheData',
    //             $return,
    //         ]);
    //     }
    //     if(!$return){
    //         $functionReturnResult=call_user_func($functionVal);
    //         //echo 'Saving to the cache!<br />'.$fileName;
    //         //echo 'saving cache '.$this->_cacheTime .' times';
    //         $this->returnDataIScache=FALSE;
    //         $this->ci->cache->file->save($fileName, $functionReturnResult, $this->_cacheTime);
    //         return $functionReturnResult;
    //     }else{
    //         $this->returnDataIScache=TRUE;
    //         $fileChecksum=md5_file($this->_cache_path.$fileName);
    //     }
    //     //pre($this->ci->cache->cache_info());
    //     return $return;
    // }
    protected function getFileName (){
        //execache icerisinde file Name detect etmeye calsitigimiz icin 1 yerine 2 yazdik.
        $extension=(!is_null($this->uniqueFileName)) ? '+'.(string)$this->uniqueFileName : '';
        $fileName=debug_backtrace()[2]['class'].'+'.debug_backtrace()[2]['function'].$extension;
        $this->callFuncName=debug_backtrace()[2]['function'];
        return $fileName;
    }


    /***/
    public function delete ($property=NULL){
        is_null($property) ? $property=debug_backtrace()[1]['class'].'+'.debug_backtrace()[1]['function'] : 0;
        return $this->ci->cache->file->delete($property);
    }


    /**
     * Removes all cache entries in the given directory
     */
    public function flush (){
        $this->fileHandler->deleteAll();
    }


    /***/
    public function destroy (){
        return $this->ci->cache->file->clean();
    }


    public function callback (){
        return new callback($this);
    }


    /**
     * Adds a cache entry with a given key, content and for a set amount of time
     * The time by default for the cache is an hour
     *
     * @param       $cacheKey
     * @param       $content
     * @param int $status
     * @param array $headers
     * @param int $expires
     * @throws CacheFileSystemException
     */
    public function add ($cacheKey, $content, $status=200, $headers=[], $expires=Cache::HOUR){
        $file=File::create();
        $file->setStatus($status);
        $file->setContent($content);
        $file->setRoute($cacheKey);
        $file->setHeaders($headers);
        if($expires > 0){
            $file->setExpires(time() + $expires);
        }else{
            $file->setExpires($expires);
        }
        $this->fileHandler->write($file);
    }


    /**
     * Removes the cache entry for the given key
     *
     * @param $cacheKey
     */
    public function remove ($cacheKey){
        $this->fileHandler->delete($cacheKey);
    }


    /**
     * Returns the directory the cache is set to save into
     *
     * @return mixed
     */
    public function getDirectory (){
        return $this->directory;
    }


    /**
     * Hydate options from given array
     *
     * @param array $data Array of options.
     * @return self
     */
    private function hydrate (array $data=[]){
        foreach($data as $key=>$value){
            $method="set".ucfirst($key);
            if(method_exists($this, $method)){
                call_user_func([$this, $method], $value);
            }
        }
        return $this;
    }
}


/**
 * @author    Uğur Ethem AYDIN
 *            me@ugur.me
 *            Date: 04.10.2016 / Time: 18:24
 * @version   0.0.1
 */
class callback
{
    public $getCacheDetails=[];

    /* tepeden gelen execache class */
    protected $execache;


    /***/
    public function __construct (Execache $execache){
        $this->execache=$execache;
        $this->getCacheDetails=($this->execache->returnDataIScache) ? $this->execache->_callFuncName : NULL;
    }
}