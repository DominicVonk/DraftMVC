<?php

namespace DraftMVC;

class DraftCollection implements \Iterator
{
    private $array;
    private $position = 0;
    public function __construct($array)
    {
        $this->array = $array;
    }
    public function rewind()
    {
        $this->position = 0;
    }
    public function current()
    {
        return $this->array[$this->position];
    }
    public function key()
    {
        return $this->position;
    }
    public function next()
    {
        ++$this->position;
    }
    public function valid()
    {
        return isset($this->array[$this->position]);
    }
    public function reverse()
    {
        return new DraftCollection(array_reverse($this->array));
    }
    public function map($fn)
    {
        return new DraftCollection(array_map($fn, $this->array));
    }
    public function nth($i)
    {
        return $this->array[$i];
    }
    public function count()
    {
        return count($this->array);
    }
    public function filter($fn)
    {
        return new DraftCollection(array_filter($this->array, $fn));
    }
    public function sort($fn = SORT_ASC)
    {
        $a = clone $this->array;
        if (!is_callable($fn)) {
            sort($a, $fn);
            return $a;
        }
        usort($a, $fn);
        return new DraftCollection($a);
    }
    public function pluck($v, $k = null)
    {
        $plucked = array();
        for ($i = 0; $i < count($this->array); $i++) {
            $val = $this->array[$i]->{$v};
            if ($k === null) {
                $plucked[] = $val;
                continue;
            }
            $plucked[$this->array[$i]->{$k}] = $val;
        }
        return $plucked;
    }
    public function slice($offset, $length = null)
    {
        return new DraftCollection(array_slice($this->array, $offset, $length));
    }
    public function toArray()
    {
        return array_map(function ($v) {
            if (method_exists($v, 'toArray')) {
                return $v->toArray();
            }
            return $v;
        }, $this->array);
    }
}
