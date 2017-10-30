<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\Tests\Storage;

use win0err\LightSearch\Storage\SQLite;
use PHPUnit\Framework\TestCase;

class SQLiteTest extends TestCase
{

    /**
     * @var SQLite
     */
    protected static $storage;
    /**
     * @var \PDO
     */
    protected static $pdo;

    public static function setUpBeforeClass()
    {
        self::$pdo = new \PDO('sqlite::memory:');
        //self::$pdo = new \PDO( 'sqlite:' . realpath( dirname( __FILE__ ) . '/../../data' ) . '/fulltext_index.sqlite' );
        self::$storage = new SQLite(self::$pdo);
    }

    public static function tearDownAfterClass()
    {
        self::$storage = null;
    }


    public function testClear()
    {

        //self::$storage->create();
        self::$storage->clear();

        self::$pdo->exec('INSERT INTO `indexables`(`id`,`external_id`,`title`,`date`,`url`,`hash`) VALUES (NULL,NULL,NULL,NULL,NULL,NULL);');
        self::$pdo->exec('INSERT INTO `words`(`id`,`word`) VALUES (NULL, "");');

        self::$storage->clear();

        $tablesCount = self::$pdo->query('
			SELECT SUM("COUNT(*)") FROM (
				SELECT COUNT(*) FROM indexables
					UNION 
				SELECT COUNT(*) FROM words
					UNION 
				SELECT COUNT(*) FROM fulltext_index
			) AS tables_count;
		')->fetchColumn();

        $this->assertEquals(0, $tablesCount);
    }

    public function testCreate()
    {
        self::$storage->create();

        $tablesCount = self::$pdo->query(
            'SELECT count(name) 
						FROM sqlite_master 
						WHERE type="table" AND name IN ("fulltext_index", "words", "indexables");'
        )->fetchColumn();

        $this->assertEquals(3, $tablesCount);
    }

    public function testGetWordIds()
    {
        $words1 = ['foo', 'bar'];
        $words2 = ['bar', 'baz'];

        $firstIteration = self::$storage->getWordIds($words1);
        $secondIteration = self::$storage->getWordIds($words1);

        $this->assertEquals($firstIteration, $secondIteration);

        $thirdIteration = self::$storage->getWordIds($words2);

        $this->assertEquals($secondIteration['bar'], $thirdIteration['bar']);
    }
}
