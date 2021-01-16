<?php

namespace Ikarus\SPS\Plugin\StopEngine;


use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Register\MemoryRegisterInterface;

/**
 * To interact with a cyclic engine, you must write to files.
 * This plugin checks, if a file exists. If so, it terminates the sps and removes the file.
 *
 * @package Ikarus\SPS\Plugin\Cyclic
 */
class StopEngineIfFileExistsPlugin extends AbstractPlugin
{
    /** @var string */
    private $filename;

    /**
     * StopEngineIfFileExistsPlugin constructor.
     * @param string $filename
     * @param string|null $identifier
     */
    public function __construct(string $filename, string $identifier = NULL)
    {
        parent::__construct($identifier !== NULL ? $identifier : $filename);
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function update(MemoryRegisterInterface $memoryRegister)
    {
        if(file_exists( $this->getFilename() )) {
            $memoryRegister->stopEngine(-9, 'Stop engine file exists service');
            unlink($this->getFilename());
        }
    }
}