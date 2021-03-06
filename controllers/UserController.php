<?php

namespace app\controllers;

use app\models\Activity;
use app\models\forms\UpdateUserForm;
use Yii;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'update', 'delete', 'profile'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view', 'update', 'delete', 'profile'],
                        'roles' => ['user'],
                    ]
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $query = User::find();

        if (!Yii::$app->user->can('admin')) {
            $query->andWhere(['id' => Yii::$app->user->id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    public function actionDelete(int $id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionProfile()
    {
        $user = Yii::$app->user->identity;

        $dataProvider = new ActiveDataProvider([
            'query' => Activity::find()->andWhere(['user_id' => $user->id]),
        ]);

        $model = new UpdateUserForm(
            $user->toArray(['username', 'email'])
        );

        if($model->load(Yii::$app->request->post()) && $model->update($user)){
            Yii::$app->session->setFlash('success', 'Изменения сохранены');
        }

        return $this->render('profile', compact('model','dataProvider'));
    }

    protected function findModel(int $id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
