<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch;


use win0err\LightSearch\Entity\Config;
use win0err\LightSearch\Entity\Indexable;
use win0err\LightSearch\MorphologyProcessor\MorphologyProcessorInterface;
use win0err\LightSearch\Storage\StorageInterface;

class Indexer {

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


	public function index(Indexable $indexable) {

		$indexableInStorage = $this->storage->getIndexableByExternalId( $indexable->getExternalId() );
		$indexable->setText( self::clearHTML( $indexable->getText() . ' ' . $indexable->getTitle() ) );

		if (!$indexableInStorage) {// || $indexableInStorage->getHash() !== $indexable->getCalculatedHash()) {

			if ($indexableInStorage)
				$this->storage->removeIndexableByExternalId( $indexableInStorage->getExternalId() );

			$this->storage->addIndexable( $indexable );

			$wordPos = self::explode( $indexable->getText() );
			foreach( $wordPos as $position => $word )
				$wordPos[$position] = $this->morphologyProcessor::apply( (string)$word );

			$wordIds = $this->storage->getWordIds( $wordPos );
			foreach( $wordPos as $position => $word )
				$wordPos[$position] = $wordIds[$word];

			// wordPos: position => word_id

			$this->storage->addFulltextIndex( $indexable, $wordPos, array_keys( $wordPos ) );

			unset( $wordPos );
		}
	}

	public function removeByExternalId(string $externalId) {

		$this->storage->removeIndexableByExternalId( $externalId );
	}

	/**
	 * Очищает HTML-код
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

	/**
	 * Разбивает текст на слова
	 * Сделан лучше, чем встроенный explode()
	 *
	 * @param $contents
	 *
	 * @return array
	 */
	protected static function explode($contents) {

		return mb_split( '[\s-]+', $contents );
	}
}