<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\MorphologyProcessor;


/**
 * Interface MorphologyProcessorInterface
 */
interface MorphologyProcessorInterface {

	/**
	 * @param string $word
	 *
	 * @return string
	 */
	public static function apply(string $word): string;
}