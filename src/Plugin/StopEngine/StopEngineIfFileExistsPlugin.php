<?php

namespace Ikarus\SPS\Plugin\StopEngine;


use Ikarus\SPS\Exception\EngineControlException;
use Ikarus\SPS\Plugin\AbstractPlugin;
use Ikarus\SPS\Plugin\SetupPluginInterface;
use Ikarus\SPS\Register\MemoryRegisterInterface;

/**
 * To interact with a cyclic engine, you must write to files.
 * This plugin checks, if a file exists. If so, it terminates the sps and removes the file.
 *
 * @package Ikarus\SPS\Plugin\Cyclic
 */
class StopEngineIfFileExistsPlugin extends AbstractPlugin implements SetupPluginInterface
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

	public function setup()
	{
		if(file_exists($this->getFilename()))
			unlink($this->getFilename());
	}


	public function update(MemoryRegisterInterface $memoryRegister)
    {
        if(file_exists( $this->getFilename() )) {
            $memoryRegister->stopEngine(-9, 'Stop engine file exists service');
            unlink($this->getFilename());
			throw (new EngineControlException( 'Stop engine file exists service', EngineControlException::CONTROL_STOP_ENGINE ))->setControl( EngineControlException::CONTROL_STOP_ENGINE );
        }
    }
}