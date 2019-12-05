<?php

namespace Ikarus\SPS\Exception;


use Ikarus\SPS\Pipe;

class PipeException extends SPSException
{
    /** @var Pipe|null */
    private $pipe;

    /**
     * @return Pipe|null
     */
    public function getPipe(): Pipe
    {
        return $this->pipe;
    }

    /**
     * @param Pipe|null $pipe
     */
    public function setPipe(Pipe $pipe)
    {
        $this->pipe = $pipe;
    }
}