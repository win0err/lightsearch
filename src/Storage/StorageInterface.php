<?php declare(strict_types=1);
/**
 * Copyright (c) 2017 Sergei Kolesnikov
 */

namespace win0err\LightSearch\Storage;

use win0err\LightSearch\Entity\Indexable;

interface StorageInterface
{
    public function clear();

    public function addIndexable(Indexable $indexable);

    public function getIndexableByExternalId(string $externalId);

    public function removeIndexableByExternalId(string $externalId);

    public function getWordIds(array $preparedWords): array;

    public function getIndexablesByWords(array $preparedWord);

    public function addFulltextIndex(Indexable $indexable, array $wordsIds, array $wordsPositions);
}
