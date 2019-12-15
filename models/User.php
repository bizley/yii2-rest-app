<?php

namespace app\models;

use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $name
 * @property string $password
 * @property int $auth_key
 * @property string $created_at
 * @property string $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()')
            ]
        ];
    }

    public static function findIdentity($id): ?self
    {
        return static::findOne($id);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function validatePassword($rawPassword): bool
    {
        return \Yii::$app->getSecurity()->validatePassword($rawPassword, $this->password);
    }

    public function fields(): array
    {
        return [
            'id',
            'name',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];
    }

    public function getAuthKey(): int
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === (int)$authKey;
    }

    /**
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('Method findIdentityByAccessToken is not supported.');
    }
}
