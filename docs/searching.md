# Поиск по индексу
Для поиска по индексу используется классы Finder и Query. Query — специальная обёртка для запроса, позволяющая отдавать результаты поиска по частям.

Для поиска нужно инициализировать объект Finder, передав ему конфигурацию хранилища и морфологического анализатора.  
```php
<?php
use win0err\LightSearch\Entity\Query;
use win0err\LightSearch\Finder;
$finder = new Finder( $config );
```

Пример поиска по запросу:
```php
foreach( $finder->find( new Query( $text ) ) as $item ) 
	echo $item->getTitle() . ‘—‘ . $item->getRating() . PHP_EOL;
```

Для порционной выдачи результатов можно модифицировать объект Query:
```php
 $query = new Query(‘content');
$query->setLimit(10); // 10 материалов за раз
$query->setOffset(20); // третья страница
$results = $finder->find($query); // ищем
```
