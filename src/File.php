<?php

namespace UEA\SlimCache;

class File
{

    private $route;
    private $expires;
    private $content;
    private $headers = [];
    private $status = 200;

    public static function create()
    {
        return new File();
    }

    public static function fromString($content)
    {
        $file = new File();
        $fileContents = json_decode($content, true);
        if (!isset($fileContents['route'])) {
            throw new \InvalidArgumentException("No route was set in cache file");
        }
        $file->setRoute($fileContents['route']);
        if (!isset($fileContents['status'])) {
            throw new \InvalidArgumentException("No status was set in cache file");
        }
        $file->setStatus($fileContents['status']);
        if (!isset($fileContents['content'])) {
            throw new \InvalidArgumentException("No content was set in cache file");
        }
        $file->setContent($fileContents['content']);
        if (!isset($fileContents['headers'])) {
            throw new \InvalidArgumentException("No headers was set in cache file");
        }
        $file->setHeaders($fileContents['headers']);
        if (!isset($fileContents['expires'])) {
            throw new \InvalidArgumentException("No expires was set in cache file");
        }
        $file->setExpires($fileContents['expires']);
        if ($fileContents['expires'] < time() && $fileContents['expires'] !== -1) {
            throw new CacheExpiredException("Cache had expired");
        }
        return $file;
    }

    private function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param mixed $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function toString()
    {
        return json_encode(
            [
                'route'   => $this->getRoute(),
                'status'  => $this->getStatus(),
                'content' => $this->getContent(),
                'headers' => $this->getHeaders(),
                'expires' => $this->getExpires()
            ]
        );
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
}