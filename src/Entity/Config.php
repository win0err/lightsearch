<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\Entity;

use win0err\LightSearch\MorphologyProcessor\MorphologyProcessorInterface;
use win0err\LightSearch\Storage\StorageInterface;

class Config
{


    /**
     * @var MorphologyProcessorInterface
     */
    protected $morphologyProcessor = null;
    /**
     * @var StorageInterface
     */
    protected $storage = null;


    /**
     * @return MorphologyProcessorInterface
     */
    public function getMorphologyProcessor(): MorphologyProcessorInterface
    {
        if (is_null($this->morphologyProcessor)) {
            throw new \Exception('Morphology Processor not defined');
        }

        return $this->morphologyProcessor;
    }

    /**
     * @param MorphologyProcessorInterface $morphologyProcessor
     *
     * @return Config
     */
    public function setMorphologyProcessor(MorphologyProcessorInterface $morphologyProcessor): Config
    {
        $this->morphologyProcessor = $morphologyProcessor;

        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        if (is_null($this->storage)) {
            throw new \Exception('Storage not defined');
        }

        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     *
     * @return Config
     */
    public function setStorage(StorageInterface $storage): Config
    {
        $this->storage = $storage;

        return $this;
    }
}
