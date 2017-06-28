<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\Storage;


use win0err\LightSearch\Entity\Indexable;

class SQLite implements StorageInterface {

	/**
	 * @var \PDO
	 */
	private $pdo;

	function __construct(\PDO $pdo = null) {

		if (is_null( $pdo )) {

			$dbLocation = realpath( dirname( __FILE__ ) . '/../../data' ) . '/fulltext_index.sqlite';
			$this->pdo = new \PDO( 'sqlite:' . $dbLocation );
		} else $this->pdo = $pdo;

		$this->pdo->exec( 'PRAGMA foreign_keys = "1";' );
		$this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );


		$this->create();
	}

	public function clear() {

		$this->pdo->beginTransaction();

		$this->pdo->exec( 'DROP TABLE IF EXISTS "fulltext_index"' );
		$this->pdo->exec( 'DROP TABLE IF EXISTS "words"' );
		$this->pdo->exec( 'DROP TABLE IF EXISTS "indexables"' );

		$this->pdo->commit();

		$this->create();

	}

	public function create() {

		$this->pdo->beginTransaction();
		$this->pdo->exec( 'CREATE TABLE IF NOT EXISTS "fulltext_index" (
										`indexable_id`	INTEGER NOT NULL,
										`word_id`	INTEGER NOT NULL,
										`position`	INTEGER,
										FOREIGN KEY( `indexable_id` ) REFERENCES `indexables` ON DELETE CASCADE,
										FOREIGN KEY( `word_id` ) REFERENCES `words`
									);' );
		$this->pdo->exec( 'CREATE TABLE IF NOT EXISTS "words" (
										`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
										`word`	VARCHAR NOT NULL UNIQUE 
									);' );
		$this->pdo->exec( 'CREATE TABLE IF NOT EXISTS "indexables" (
										`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
										`external_id`	VARCHAR UNIQUE,
										`title`	VARCHAR,
										`date`	DATETIME,
										`url`	VARCHAR,
										`hash`	VARCHAR,
										`rating_rate`	REAL DEFAULT 1
									);' );
		$this->pdo->commit();
	}

	public function addIndexable(Indexable $indexable) {

		try {
			$query = $this->pdo->prepare( 'INSERT INTO `indexables`(`external_id`,`title`,`date`,`url`,`hash`, `rating_rate`) VALUES (?, ?, ?, ?, ?, ?);' );
			$query->execute( [
				$indexable->getExternalId(),
				$indexable->getTitle(),
				$indexable->getDate()->format( 'Y-m-d H:i:s' ),
				$indexable->getUrl(),
				$indexable->getCalculatedHash(),
				$indexable->getRating()
			] );

			$indexable->setId( (int)$this->pdo->lastInsertId() );

			return $indexable;

		} catch (\Exception $exception) {

			return false;
		}
	}

	public function getIndexableByExternalId(string $externalId) {

		try {
			$query = $this->pdo->prepare( 'SELECT * FROM "indexables" WHERE `external_id` = ?' );
			$query->execute( [$externalId] );

			$indexableData = $query->fetch();

			if ($indexableData === false)
				throw new \Exception();

			return new Indexable(
				$indexableData['id'],
				$indexableData['external_id'],
				$indexableData['title'],
				new \DateTime( $indexableData['date'] ),
				$indexableData['url'],
				$indexableData['hash'],
				$indexableData['rating_rate']
			);

		} catch (\Exception $exception) {

			return false;
		}
	}

	public function removeIndexableByExternalId(string $externalId) {

		try {
			$this->pdo
				->prepare( 'DELETE FROM "indexables" WHERE `external_id` = ?' )
				->execute( [$externalId] );

			return true;
		} catch (\Exception $exception) {
			return false;
		}
	}

	public function getIndexablesByWords(array $preparedWords) {

		$sql = '
			SELECT i.*, i.rating_rate * COUNT(fi.position) AS rating
			FROM fulltext_index AS fi, words AS w, indexables AS i
			WHERE w.id = fi.word_id AND fi.indexable_id = i.id AND w.word IN ("' . implode( '", "', $preparedWords ) . '")
			GROUP BY external_id
			ORDER BY rating DESC;
		';
		$st = $this->pdo->prepare( $sql );
		$st->execute();

		while ($indexablesFound = $st->fetch()) {

			yield new Indexable(
				$indexablesFound['id'],
				$indexablesFound['external_id'],
				$indexablesFound['title'],
				new \DateTime( $indexablesFound['date'] ),
				$indexablesFound['url'],
				$indexablesFound['hash'],
				$indexablesFound['rating']
			);
		}
	}

	public function getWordIds(array $preparedWords): array {

		$preparedWords = array_unique( $preparedWords );
		$ids = [];

		// Находим те, что есть в базе
		$sql = 'SELECT * FROM "words" WHERE `word` IN ("' . implode( '", "', $preparedWords ) . '");';

		$st = $this->pdo->prepare( $sql );
		$st->execute();

		while ($knownWord = $st->fetch())
			$ids[$knownWord['word']] = $knownWord['id'];

		// Если нашли все нужные слова, возвращаем
		if (sizeof( $preparedWords ) == sizeof( $ids ))
			return $ids;

		// Добавляем те, которых нет в базе
		$unknownWords = array_diff( $preparedWords, array_keys( $ids ) );

		$sql = 'INSERT INTO "words" (word) VALUES ("' . implode( '"), ("', $unknownWords ) . '");';
		$st = $this->pdo->prepare( $sql );
		$st->execute();

		return array_merge( $ids, $this->getWordIds( $unknownWords ) );
	}

	public function addFulltextIndex(Indexable $indexable, array $wordsIds, array $wordsPositions) {

		$this->pdo->beginTransaction();

		for( $i = 0; $i < sizeof( $wordsIds ); $i++ ) {

			$st = $this->pdo->prepare( 'INSERT INTO "fulltext_index" (indexable_id, word_id, position) VALUES (?, ?, ?)' );
if(is_null($wordsIds[$i]) || is_null($wordsPositions[$i]))
continue;
			$st->execute( [$indexable->getId(), (string)$wordsIds[$i], $wordsPositions[$i]] );
		}

		$this->pdo->commit();

	}


}
