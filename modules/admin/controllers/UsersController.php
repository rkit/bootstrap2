<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\filters\VerbFilter;
use app\traits\ModelTrait;
use app\models\entity\User;
use app\models\entity\UserProfile;
use app\modules\admin\models\forms\UserForm;
use app\modules\admin\models\forms\UserProfileForm;
use app\modules\admin\models\search\UserSearch;

class UsersController extends \yii\web\Controller
{
    use ModelTrait;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'set-active' => ['post'],
                    'set-block' => ['post'],
                    'delete' => ['post'],
                    'batch' => ['post'],
                    'photo-upload' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'batch' => [
                'class' => 'app\modules\admin\actions\BatchAction',
                'modelClass' => User::class,
                'actions' => [
                    'delete' => [],
                    'set-active' => ['status' => User::STATUS_ACTIVE],
                    'set-block' => ['status' => User::STATUS_BLOCKED]
                ]
            ],
            'set-active' => [
                'class' => 'app\modules\admin\actions\UpdateAttributesAction',
                'modelClass' => User::class,
                'attributes' => ['status' => User::STATUS_ACTIVE],
            ],
            'set-block' => [
                'class' => 'app\modules\admin\actions\UpdateAttributesAction',
                'modelClass' => User::class,
                'attributes' => ['status' => User::STATUS_BLOCKED],
            ],
            'delete' => [
                'class' => 'app\modules\admin\actions\DeleteAction',
                'modelClass' => User::class,
            ],
            'photo-upload' => [
                'class'     => 'rkit\filemanager\actions\UploadAction',
                'modelClass' => UserProfile::class,
                'attribute' => 'photo',
                'inputName' => 'file',
            ],
        ];
    }

    public function actionIndex()
    {
        $userSearch = new UserSearch();
        $dataProvider = $userSearch->search(Yii::$app->request->get());
        $statuses = User::getStatuses();

        return $this->render('index', [
            'userSearch' => $userSearch,
            'dataProvider' => $dataProvider,
            'statuses' => $statuses,
            'roles' => Yii::$app->authManager->getRoles()
        ]);
    }

    public function actionEdit($id = null)
    {
        $model = new UserForm();

        if ($id) {
            $model->setModel($this->findModel(new User, $id));
        }

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->save();

                Yii::$app->session->setFlash('success', Yii::t('app.msg', 'Saved successfully'));
                return $this->redirect(['edit', 'id' => $model->id]);
            }
            return $this->asJsonModelErrors($model);
        }

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    public function actionProfile($id)
    {
        $model = new UserProfileForm();
        $model->setModel($this->findModel(new UserProfile, $id));

        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->save();

                Yii::$app->session->setFlash('success', Yii::t('app.msg', 'Saved successfully'));
                return $this->redirect(['profile', 'id' => $model->user_id]);
            }
            return $this->asJsonModelErrors($model);
        }

        return $this->render('profile', [
            'model' => $model
        ]);
    }
}
