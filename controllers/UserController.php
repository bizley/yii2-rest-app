<?php

namespace app\controllers;

use yii\rest\Controller;
use yii\rest\OptionsAction;

class UserController extends Controller
{
    public function actions(): array
    {
        return [
            'options' => OptionsAction::class,
        ];
    }
}
