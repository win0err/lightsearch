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

		$words = self::explode( self::clearHTML( $query->getText() ) );

		$mp = $this->morphologyProcessor;
		$applyMp = function ($word) use ($mp) {

			return $mp::apply( (string)$word );
		};
		$words = array_map( $applyMp, $words );

		$queryWordsCount = sizeof( $words );

		// ToDo: Вынести блэклист в отдельный класс
		//$words = array_diff( $words, static::$blacklist );

		foreach ($this->storage->getIndexablesByWords( $words ) as $indexable) {

			$titleByWords = array_map( $applyMp, self::explode( self::clearHTML( $indexable->getTitle() ) ) );
			$newRating = $indexable->getRating() * (1 + 4 * sizeof( array_intersect( $words, $titleByWords ) ) / $queryWordsCount);

			$indexable->setRating($newRating);

			yield $indexable;
		}
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


	/**
	 * Очищает HTML-код
	 *
	 * ToDo: Убрать дублирование в Indexer
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	protected static function clearHTML(string $html): string {

		// Чистим от HTML-тегов и переводим в нижний регистр
		$html = strip_tags( mb_strtolower( $html ) );

		// Символ неразрывного пробела
		$html = str_replace( ['&nbsp;', "\xc2\xa0"], ' ', $html );

		// ё → е
		$html = str_replace( 'ё', 'е', $html );

		// HTML-коды: &lt; or &#60;
		$html = mb_ereg_replace( '&[^;]{1,20};', '', $html );

		// Оставляем разрешённые символы
		$html = mb_ereg_replace( '[^\\-а-яё0-9a-z\\^_]+', ' ', $html );

		$html = trim( $html );

		return $html;
	}
}
