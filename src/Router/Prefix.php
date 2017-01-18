<?php

namespace Deimos\Router;

use Deimos\Route\Route as ClassRoute;
use Deimos\Router\Exceptions\PathNotFound;
use Deimos\Router\Exceptions\TypeNotFound;

class Prefix
{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $resolver;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * Prefix constructor.
     *
     * @param array $resolver
     * @param array $defaults
     */
    public function __construct(array $resolver, array $defaults = [])
    {
        $this->resolver = $resolver;
        $this->defaults = $defaults;
    }

    /**
     * @param string $value
     */
    public function setPath($value)
    {
        $this->path = $value;
    }

    /**
     * @param array  $array
     * @param string $key
     *
     * @return array
     */
    protected function getArray(array $array, $key)
    {
        return isset($array[$key]) ? $array[$key] : [];
    }

    /**
     * @return array
     *
     * @throws \Deimos\Route\Exceptions\PathNotFound
     * @throws PathNotFound
     * @throws TypeNotFound
     */
    public function getResolver()
    {
        return $this->resolver($this->resolver);
    }

    /**
     * @return string
     */
    protected function path()
    {
        return ($this->path ?: '');
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    public function defaults(array $rows)
    {
        return array_merge($this->defaults, $rows);
    }

    /**
     * @param array $value
     *
     * @throws PathNotFound
     * @throws TypeNotFound
     */
    protected function checkItem(array $value, $key)
    {

        if (!isset($value['type']))
        {
            throw new TypeNotFound($key);
        }

        if (!isset($value['path']))
        {
            throw new PathNotFound($key);
        }

    }

    /**
     * @param array $resolver
     *
     * @return array
     *
     * @throws \Deimos\Route\Exceptions\PathNotFound
     * @throws PathNotFound
     * @throws TypeNotFound
     */
    protected function resolver(array $resolver)
    {
        $rows = [];

        foreach ($resolver as $key => $item)
        {
            $this->checkItem($item, $key);

            if ($this->isPrefix($item, $rows))
            {
                continue;
            }

            $this->route($item, $rows);
        }

        return $rows;
    }

    /**
     * @param array $item
     * @param array $rows
     *
     * @throws \Deimos\Route\Exceptions\PathNotFound
     */
    protected function route(array $item, array &$rows)
    {
        $path    = (array)$item['path'];
        $path[0] = $this->path() . $path[0];

        $rows[] = new ClassRoute(
            $path,
            $this->defaults(
                $this->getArray($item, 'defaults')
            ),
            $this->getArray($item, 'methods')
        );
    }

    /**
     * @param array $item
     * @param array $rows
     *
     * @return bool
     *
     * @throws \Deimos\Route\Exceptions\PathNotFound
     * @throws PathNotFound
     * @throws TypeNotFound
     */
    protected function isPrefix(array $item, array &$rows)
    {
        if ($item['type'] === 'prefix')
        {
            $prefix = new Prefix(
                $this->getArray($item, 'resolver'),
                $this->defaults(
                    $this->getArray($item, 'defaults')
                )
            );

            $prefix->setPath($this->path() . $item['path']);

            foreach ($prefix->getResolver() as $pattern)
            {
                $rows[] = $pattern;
            }

            return true;
        }

        return false;
    }

}
