<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\Tests\MorphologyProcessor\Stemmer;

use win0err\LightSearch\MorphologyProcessor\Stemmer\PorterRussian;
use PHPUnit\Framework\TestCase;

class PorterRussianTest extends TestCase
{
    public function testApply()
    {
        $voc = new \SplFileObject(__DIR__ . '/Russian/voc.txt');
        $output = new \SplFileObject(__DIR__ . '/Russian/output.txt');

        while (!$voc->eof()) {
            $this->assertEquals(
                trim($output->fgets()),
                trim(PorterRussian::apply($voc->fgets()))
            );
        }
    }
}
