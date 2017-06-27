<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch;

use win0err\LightSearch\Entity\Config;
use win0err\LightSearch\Entity\Query;
use win0err\LightSearch\MorphologyProcessor\MorphologyProcessorInterface;
use win0err\LightSearch\Storage\StorageInterface;

class Finder {

	/**
	 * @var MorphologyProcessorInterface
	 */
	protected $morphologyProcessor = null;
	/**
	 * @var StorageInterface
	 */
	protected $storage = null;


	public function __construct(Config $config) {

		$this->morphologyProcessor = $config->getMorphologyProcessor();
		$this->storage = $config->getStorage();
	}

	public function find(Query $query) {

		return $this->storage->getIndexablesByWords( self::explode( addslashes( $query->getText() ) ) );
	}

	/**
	 * Разбивает текст на слова
	 * Сделан лучше, чем встроенный explode()
	 *
	 * ToDo: Убрать дублирование в Indexer
	 *
	 * @param $contents
	 *
	 * @return array
	 */
	protected static function explode($contents) {

		return mb_split( '[\s-]+', $contents );
	}
}