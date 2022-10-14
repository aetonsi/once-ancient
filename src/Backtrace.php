<?php

namespace Aetonsi\OnceAncient;

class Backtrace
{
    /** @var array */
    protected $trace;

    /** @var array */
    protected $zeroStack;

    /**
     * @param array $trace
     */
    public function __construct($trace)
    {
        $this->trace = $trace[1];
        $this->zeroStack = $trace[0];
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->trace['args'];
    }

    /**
     * @return string
     */
    public function getFunctionName()
    {
        return $this->trace['function'];
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        if ($this->globalFunction()) {
            return $this->zeroStack['file'];
        }

        return $this->staticCall() ? $this->trace['class'] : $this->trace['object'];
    }

    public function getHash()
    {
        $normalizedArguments = array_map(function ($argument) {
            return is_object($argument) ? spl_object_hash($argument) : $argument;
        }, $this->getArguments());

        $prefix = $this->getFunctionName();
        if (strpos($prefix, '{closure}') !== false) {
            $prefix = $this->zeroStack['line'];
        }

        return md5($prefix . serialize($normalizedArguments));
    }

    protected function staticCall()
    {
        return $this->trace['type'] == '::';
    }

    protected function globalFunction()
    {
        return !isset($this->trace['type']);
    }
}
