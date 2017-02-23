<?php
/**
 * Created by Metromedya
 * http://metromedya.com
 * User: ugurethemaydin
 * Date: 23/02/2017
 * Time: 11:40
 * Class FileCache
 *
 * @package UEA\SlimCache
 */
namespace UEA\SlimCache;

use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

define('DEFAULT_CACHE_TIME', 3600);

class FileCache
{
    const NEVER = -1;

    /**
     * Stores all the options passed to the rule
     */
    private $options = [
        "filePath"    => __DIR__ . '/../../../cache/',
        'cache'       => true,
        'defaultTime' => 3666,
    ];

    private $lastFileName;

    private $directory;

    private $uniqueFileName = null;

    public $callFuncName = null;

    private $request;

    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getLastFileName($flag = 0)
    {
        return $this->lastFileName[$flag];
    }

    /**
     * Create a new middleware instance
     * Takes the slim app and then the directory where the cache is to be saved
     *
     * @param string[] $options
     */
    public function __construct(array $options = [])
    {
        // $this->hydrate($options);
        if (null !== (@$options["filePath"])) {
            $this->options['filePath'] = $options['filePath'];
            $cacheDirectory            = $this->options['filePath'];
        }
        if (null !== (@$options["cache"])) {
            $this->options['cache'] = $options['cache'];
            $cacheDirectory         = $this->options['filePath'];
        }
        // if(is_null($cacheDirectory)){
        //     $cacheDirectory=__DIR__.'/../cache/';
        // }
        $this->directory   = $cacheDirectory;
        $this->fileHandler = new FileHandler($this->directory);
    }

    protected function getFileName()
    {
        //execache icerisinde file Name detect etmeye calsitigimiz icin 1 yerine 2 yazdik.
        $requestPath        = $this->request->getUri()->getPath();
        $requestPathToName  = md5($requestPath);
        $this->lastFileName = [$requestPathToName, $requestPath];
        return ($requestPathToName);
        // $extension=(!is_null($this->uniqueFileName)) ? '+'.(string)$this->uniqueFileName : '';
        // $fileName=debug_backtrace()[4]['class'].'+'.debug_backtrace()[2]['function'].$extension;
        // $this->callFuncName=debug_backtrace()[2]['function'];
        // return $fileName;
    }

    private function callCapsule($calledFunc)
    {
        if (is_callable($calledFunc)) {
            return call_user_func($calledFunc);
        }
        return null;
    }

    public function capsule($x = null, $y = null, $z = null)
    {
        $fileName = $this->getFileName();
        $payload  = ($this->options['cache']) ? $this->fileHandler->read($fileName) : null;
        if (!$payload) {
            if (is_callable($x)) {
                $calleble        = $x;
                $this->cacheTime = is_integer($y) ? $y : DEFAULT_CACHE_TIME;
            }
            if (is_callable($y)) {
                $calleble         = $y;
                $this->uniqueName = is_integer($x) ? $z : $x;
                $this->cacheTime  = is_integer($x) ? $x : $z;
            }
            $calledReturn = $this->callCapsule($calleble);
            if ($this->options['cache'] === true) {
                $this->createFile($fileName, $calledReturn);
            }

        } else {
            $calledReturn = $payload;
        }
        return $calledReturn;
    }

    private function createFile($name, $content)
    {
        $file = FileStructure::create();
        $file->setName($name);
        $file->setContent($content);
        $this->fileHandler->write($file);
    }

    /**
     * Removes the cache entry for the given key
     *
     * @param $cacheKey
     */
    public function remove($name)
    {
        $name = forceMD5($name);
        return $this->fileHandler->delete($name);
    }
}
