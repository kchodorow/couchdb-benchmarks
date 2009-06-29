<?php

$config = array(
	'method' => 'bulk_insert',
	'insertCounts' => array(
		0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
		10,
		50,
		100,
		500,
		1000,
		2500,
		5000,
		7500,
		10000,
		25000,
		50000,
		100000,
		250000,
		500000,
		750000,
		1000000,
	)
);

$m = new Mongo();
$c = $m->selectCollection('test', 'benchmark');

foreach ($config['insertCounts'] as $docCount) {
  // Re-create the database for each attempt
  $c->drop();

  echo sprintf("-> %s %d docs:\n", $config['method'], $docCount);

  switch ($config['method']) {
  case 'bulk_insert':
    $insertStart = microtime(true);
    $docsWritten = 0;
    while ($docsWritten < $docCount) {
      $insertAtOnce = ($docCount - $docsWritten > 1000)
        ? 1000
        : $docCount - $docsWritten;
      
      $docs = array();
      for ($i = 0; $i < $insertAtOnce; $i++) {
        $docs[] = array(
                        '_id' => new MongoId(),
                        'foo' => 'bar'
                        );
      }
      $c->batchInsert($docs);
      $docsWritten = $docsWritten + $insertAtOnce;
      echo '.';
    }
    $insertEnd = microtime(true);
    break;
  case 'single_insert':
    $insertStart = microtime(true);
    for ($i = 0; $i < $docCount; $i++) {
      $c->insert(array('_id' => new MongoId(), 'foo' => 'bar'));
    }
    $insertEnd = microtime(true);
    echo '.';
    break;
  }
  
  $beforeCompact = array('stats' => array('doc_count' => $docCount, 'disk_size' => 0), 'fileSize' => 0);
  $afterCompact = array('stats' => array('doc_count' => $docCount, 'disk_size' => 0), 'fileSize' => 0);
  $compactStart = 0;
  $compactEnd = 0;

  echo "\n\n";
  echo sprintf(
               "doc count (before compact): %s\n".
               "insert time: %s sec\n".
               "insert time / doc: %s ms\n",

               $beforeCompact['stats']['doc_count'],
               round($insertEnd - $insertStart, 4),
               ($beforeCompact['stats']['doc_count'])
               ? ((($insertEnd - $insertStart) * 1000) / $beforeCompact['stats']['doc_count'])
               : 'n/a',
               round($compactEnd - $compactStart, 4),
               ($beforeCompact['stats']['doc_count'])
               ? ((($compactEnd - $compactStart) * 1000) / $beforeCompact['stats']['doc_count'])
               : 'n/a' 
               );
}


?>