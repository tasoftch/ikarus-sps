<?php

namespace Ikarus\SPS\Plugin\Cyclic;


use Ikarus\SPS\Plugin\Management\CyclicPluginManagementInterface;

/**
 * To interact with a cyclic engine, you must write to files.
 * This plugin checks, if a file exists. If so, it terminates the sps and removes the file.
 *
 * @package Ikarus\SPS\Plugin\Cyclic
 */
class StopEngineIfFileExistsPlugin extends AbstractCyclicPlugin
{
    /** @var string */
    private $filename;

    /**
     * StopEngineIfFileExistsPlugin constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    public function update(CyclicPluginManagementInterface $pluginManagement)
    {
        if(file_exists( $this->getFilename() )) {
            $pluginManagement->stopEngine();
            unlink($this->getFilename());
        }
    }
}