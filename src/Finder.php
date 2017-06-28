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

	// ToDo: Вынести блэклист в отдельный класс
	public static $blacklist = ['и', 'на', 'в', 'для', 'под', 'из', 'за', 'к', 'с', 'над', 'у', 'или'];

	public function __construct(Config $config) {

		$this->morphologyProcessor = $config->getMorphologyProcessor();
		$this->storage = $config->getStorage();
	}

	public function find(Query $query) {

		$words = self::explode( addslashes( $query->getText() ) );

		// ToDo: Вынести блэклист в отдельный класс
		$words = array_diff( $words, static::$blacklist );

		$mp = $this->morphologyProcessor;
		$words = array_map(
			function ($word) use ($mp) {

				return $mp::apply( (string)$word );
			}, $words );

		return $this->storage->getIndexablesByWords( $words );
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
