<?php

namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\web\ForbiddenHttpException;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // use JSON Web Token for all actions except registration and auth key retrieval
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'optional' => ['create', 'key'],
        ];
        // use Basic Auth to get JSON Web Token
        $behaviors['basicAuth'] = [
            'class' => HttpBasicAuth::className(),
            'only' => ['key'],
            'auth' => function ($username, $password) {
                $user = $this->modelClass::findByUsername($username);
                if ($user && $user->validatePassword($password)) {
                    return $user;
                }
            },
        ];
        return $behaviors;
    }

    /**
     * @return \yii\web\Response
     */
    public function actionKey()
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();

        // adoption for lcobucci/jwt ^4.0 version
        $token = $jwt->getBuilder()
            ->issuedBy('http://example.com')// Configures the issuer (iss claim)
            ->permittedFor('http://example.org')// Configures the audience (aud claim)
            ->identifiedBy('4f1g23a12aa', true)// Configures the id (jti claim), replicating as a header item
            ->issuedAt($time)// Configures the time that the token was issue (iat claim)
            ->expiresAt($time + 3600)// Configures the expiration time of the token (exp claim)
            ->withClaim('uid', 100)// Configures a new claim, called "uid"
            ->getToken($signer, $key); // Retrieves the generated token

        // save JSON Web Token to database
        $user = $this->modelClass::findOne(Yii::$app->user->id);
        $user->access_token = (string) $token;
        $user->save();

        return $this->asJson([
            'token' => (string) $token,
        ]);
    }

    /**
     * Checks the privilege of the current user.
     *
     * @param string $action the ID of the action to be executed
     * @param \yii\base\Model $model the model to be accessed. If `null`, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
        if ($action === 'update' || $action === 'delete') {
            if ($model->id !== Yii::$app->user->id) {
                throw new ForbiddenHttpException(sprintf('You can %s only own user accoount.', $action));
            }
        }
    }
}
