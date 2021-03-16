<?php

namespace phuong17889\email\controllers;

use phuong17889\email\models\EmailMessage;
use phuong17889\email\models\search\EmailMessageSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * MessageController implements the CRUD actions for EmailMessage model.
 */
class MessageController extends Controller {

	/**
	 * @return array
	 */
	public function behaviors() {
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => [
							'index',
							'view',
							'resend',
						],
						'allow'   => true,
						'roles'   => ['@'],
					],
				],
			],
		];
	}

	/**
	 * Lists all EmailMessage models.
	 *
	 * @return mixed
	 */
	public function actionIndex() {
		$searchModel  = new EmailMessageSearch;
		$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
		return $this->render('index', [
			'dataProvider' => $dataProvider,
			'searchModel'  => $searchModel,
		]);
	}

	/**
	 * Displays a single EmailMessage model.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function actionView($id) {
		$model = $this->findModel($id);
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect([
				'view',
				'id' => $model->id,
			]);
		} else {
			return $this->render('view', ['model' => $model]);
		}
	}

	/**
	 * Displays a single EmailMessage model.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	public function actionResend($id) {
		$model                    = $this->findModel($id);
		$emailMessage             = new EmailMessage();
		$emailMessage->attributes = $model->attributes;
		$emailMessage->status     = EmailMessage::STATUS_NEW;
		$emailMessage->save();
		Yii::$app->session->setFlash('success', Yii::t('email', 'Email has been sent!'));
		return $this->redirect([
			'index',
		]);
	}

	/**
	 * Finds the EmailMessage model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @param integer $id
	 *
	 * @return EmailMessage the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = EmailMessage::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}
