<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\Entity;


class Query {

	/**
	 * @var int
	 */
	protected $limit = 10;
	/**
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * @var string
	 */
	protected $text = "";

	/**
	 * Query constructor.
	 *
	 * @param string $text
	 */
	public function __construct($text) { $this->text = $text; }

	/**
	 * @return string|null
	 */
	public function getText() {

		return (string)$this->text;
	}

	/**
	 * @param string $text
	 */
	public function setText(string $text) {

		$this->text = $text;
	}


	/**
	 * @return int
	 */
	public function getLimit(): int {

		return $this->limit;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit(int $limit) {

		$this->limit = $limit;
	}

	/**
	 * @return int
	 */
	public function getOffset(): int {

		return $this->offset;
	}

	/**
	 * @param int $offset
	 */
	public function setOffset(int $offset) {

		$this->offset = $offset;
	}


}
