<?php

namespace backend\controllers;

use common\models\RolesAssignment;
use Yii;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UsermanageController implements the CRUD actions for User model.
 */
class UsermanageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'except' => ['error'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['creatorPermission'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        Url::remember();
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        Url::remember();
        $roleAssign = RolesAssignment::find()->where(['user_id'=>$id])->one()
            ? RolesAssignment::find()->where(['user_id'=>$id])->one() : new RolesAssignment;
        return $this->render('view', [
            'model' => $this->findModel($id),
            'roleAssign' => $roleAssign,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) ) {
//            var_dump($model); die;
//            var_dump(Yii::$app->request->post()); die;
            $model->password_hash = Yii::$app->security->generatePasswordHash($model->password);

            $model->status = 10;
            $date = new \DateTime();
            $model->created_at =  new Expression('CURRENT_TIMESTAMP()');
            $model->updated_at = time();


            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            print_r($model->getErrors());

        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $assigned = RolesAssignment::find()->where(['user_id'=>$id])->one();
        if ($assigned) {
            $assigned->delete();
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionAssign($id)
    {
        $assigned = RolesAssignment::find()->where(['user_id'=>$id])->one();
        $request = Yii::$app->request->post('RolesAssignment');

        if ($assigned ) {
            $assigned['item_name'] = $request['item_name'];
            $assign = $assigned;
        } else {
            $assign = new RolesAssignment();
            $assign['item_name'] = $request['item_name'];
            $assign['user_id'] = $id;
        }

        if ($assign->save()){
//            Yii::$app->session->setFlash('success', "получилось");
            return $this->redirect(Url::previous());
        } else {
            Yii::$app->session->setFlash('error', "неполучается");
            return $this->redirect(Url::previous());
        }


    }
    public function actionSetPass($id)
    {
        $post = Yii::$app->request->post('User');
        $password = $post['password_hash'];
        $model = $this->findModel($id);
        $model->password_hash = Yii::$app->security->generatePasswordHash($password);

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'пароль пользователя '.$model->username.' изменен');
            return $this->redirect(Url::previous());
        }
    }
}
