<?php

return [
    'id' => 'yii2-rest-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'loginUrl' => null,
            'enableSession' => false,
        ],
        'jwt' => [
            'class' => \bizley\jwt\Jwt::class,
            'key' => 'YpudQZvhA53TyBiVChVBLlPyt5Xkv2pOofwvfQz9Vy70BSCL1JqV77NAjuHL'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require 'db.php',
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
];
