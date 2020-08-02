<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\filters\auth\HttpBasicAuth;

class User extends ActiveRecord implements IdentityInterface
{

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['password'], $fields['auth_key'], $fields['access_token']);

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // required attributes
            [['username', 'password'], 'required'],

            // the username attribute should be a valid email address
            ['username', 'email'],

            // the username attribute should be unique
            ['username', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // check if password is not hashed and hash it
        if (!preg_match('/^\$2[axy]\$(\d\d)\$[\.\/0-9A-Za-z]{22}/', $this->password, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeAttributes()
    {
        // make all attributes available for loading into database via requests
        return $this->attributes();
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }
}
