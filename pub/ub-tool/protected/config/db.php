<?php
return array(
    'components'=>array(
        //database of Magento1
        'db1' => array(
            'connectionString' => 'mysql:host=localhost;port=3306;dbname=magentomigrate',
            'emulatePrepare' => true,
            'username' => 'magentomigrate',
            'password' => 'dYu1ao3KluTbsVmX',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'class' => 'CDbConnection'
        ),
        //database of Magento 2 (we use this database for this tool too)
        'db' => array(
            'connectionString' => 'mysql:host=localhost;port=3306;dbname=shop3_kintec_net',
            'emulatePrepare' => true,
            'username' => 'magento2',
            'password' => 'SwVb6F3WniIjR0mG',
            'charset' => 'utf8',
            'tablePrefix' => '',
            'class' => 'CDbConnection'
        ),
    )
);
