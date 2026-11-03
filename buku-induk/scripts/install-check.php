<?php
echo 'PHP ' . PHP_VERSION . PHP_EOL;
foreach (['pdo','pdo_sqlite','pdo_mysql','mbstring','xml','gd','zip'] as $ext) {
    echo $ext . ': ' . (extension_loaded($ext) ? 'ok' : 'MISSING') . PHP_EOL;
}
echo 'vendor: ' . (is_file(__DIR__ . '/../vendor/autoload.php') ? 'ok' : 'missing') . PHP_EOL;
