<?php

namespace backend\controllers;

use Yii;
//use backend\models\MbUraianPekerjaan;
//use backend\models\MbUraianPekerjaanSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use backend\models\customs\UraianPekerjaan;
use backend\models\customs\search\UraianPekerjaanSearch;
use backend\models\customs\LokasiPekerjaan;
use backend\models\customs\UraianPekerjaanHasStatus;

/**
 * MbUraianPekerjaanController implements the CRUD actions for MbUraianPekerjaan model.
 */
class MbUraianPekerjaanController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all MbUraianPekerjaan models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UraianPekerjaanSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single MbUraianPekerjaan model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MbUraianPekerjaan model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UraianPekerjaan();
        $modelLokasi = new LokasiPekerjaan();
        $modelStatus = new UraianPekerjaanHasStatus();

        //if ($model->load(Yii::$app->request->post()) && $model->save()) {
            //return $this->redirect(['view', 'id' => $model->mb_uraian_pekerjaan_id]);
        //} 
        if ($model->load(Yii::$app->request->post()) && $modelLokasi->load(Yii::$app->request->post()) && $modelStatus->load(Yii::$app->request->post()) ) {
            //var_dump($model->save());
            //exit();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->save()) {
                    $modelLokasi->mb_uraian_pekerjaan_id = $model->mb_uraian_pekerjaan_id;
                    //$modelLokasi->mb_kelurahan_desa_id = $modelLokasi->mb_kelurahan_desa_id;
                    $modelStatus->mb_uraian_pekerjaan_id = $model->mb_uraian_pekerjaan_id;
                    if ($modelLokasi->save() && $modelStatus->save()) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success','Data berhasil disimpan');
                    } else {
                        var_dump($modelLokasi->getErrors());
                        var_dump($modelStatus->getErrors());
                        exit();
                    }
                    return $this->redirect(['index']);
                } else {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error','Terjadi kesalahan, Data tidak bisa disimpan');
                    return $this->redirect(['index']);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error','Rollback transaction. Data tidak bisa disimpan');
                return $this->redirect(['index']);
            }
            //return $this->redirect(['view', 'id' => $model->mb_skpd_has_rekening_rincian_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'modelStatus' => $modelStatus,
                'modelLokasi' => $modelLokasi
            ]);
        }
    }

    /**
     * Updates an existing MbUraianPekerjaan model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->mb_uraian_pekerjaan_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing MbUraianPekerjaan model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionUraiandetail()
    {
        $id = Yii::$app->request->post('expandRowKey');
        $model = $this->findModel($id);

        return $this->renderPartial('_detailuraian', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the MbUraianPekerjaan model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MbUraianPekerjaan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UraianPekerjaan::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
