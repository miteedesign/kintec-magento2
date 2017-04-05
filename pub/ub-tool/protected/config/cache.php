<?php
/**
 * Cache settings
 */
//Option 1: Use file cached
return array(
    'class' => 'CFileCache'
);

//Option 2 - use php memcached
/*return array(
    'class'=>'CMemCache',
    'servers'=>array(
        array(
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 60,
        ),
    )
);*/
