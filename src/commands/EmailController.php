<?php

namespace phuong17889\email\commands;

use Exception;
use phuong17889\daemon\commands\DaemonController;
use phuong17889\email\components\EmailManager;
use phuong17889\email\models\EmailMessage;
use phuong17889\email\Module;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;

/**
 * @author  Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author  Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * Class EmailCommand
 * @package email\commands
 */
class EmailController extends DaemonController {

	/**
	 * @return bool
	 * @throws Exception
	 */
	protected function worker() {
		return $this->actionSendOne();
	}

	/**
	 * @return string
	 */
	protected function name() {
		return 'mail-daemon';
	}

	/**
	 * @throws InvalidConfigException
	 */
	public function actionResend() {
		/**@var EmailManager $instance */
		$instance    = Yii::$app->get('emailManager');
		$db          = Yii::$app->db;
		$transaction = $db->beginTransaction();
		try {
			/**@var EmailMessage[] $emails */
			$emails = EmailMessage::find()->where(['status' => EmailMessage::STATUS_IN_PROGRESS])->andWhere([
				'<',
				'created_at',
				strtotime($instance->resendAfter . ' minutes ago'),
			])->andWhere([
				'<',
				'try_time',
				$instance->tryTime,
			])->all();
			foreach ($emails as $email) {
				$email->updateAttributes([
					'status'   => EmailMessage::STATUS_NEW,
					'try_time' => $email->try_time + 1,
				]);
			}
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
		}
	}

	/**
	 * Send one email from queue
	 * @return bool
	 * @throws Exception
	 */
	public function actionSendOne() {
		$db          = Yii::$app->db;
		$transaction = $db->beginTransaction();
		try {
			$id = $db->createCommand('SELECT id FROM {{%email_message}} WHERE status=:status ORDER BY priority DESC, id ASC LIMIT 1 FOR UPDATE', [
				'status' => EmailMessage::STATUS_NEW,
			])->queryScalar();
			if ($id === false) {
				$transaction->rollBack();
				return false;
			}
			/** @var EmailMessage $model */
			$model         = EmailMessage::findOne($id);
			$model->status = EmailMessage::STATUS_IN_PROGRESS;
			$model->updateAttributes(['status']);
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
		$transaction = $db->beginTransaction();
		try {
			if (filter_var($model->to, FILTER_VALIDATE_EMAIL)) {
				$result = EmailManager::getInstance()->send($model->from, $model->to, $model->subject, $model->text, $model->files, $model->bcc);
				if ($result) {
					$model->sent_at = time();
					$model->status  = EmailMessage::STATUS_SENT;
				} else {
					$model->status = EmailMessage::STATUS_ERROR;
				}
			} else {
				$model->status = EmailMessage::STATUS_ERROR;
			}
			$model->updateAttributes([
				'sent_at',
				'status',
			]);
			$transaction->commit();
		} catch (Exception $e) {
			$transaction->rollBack();
			throw $e;
		}
		return true;
	}

	/**
	 * @throws Throwable
	 * @throws StaleObjectException
	 */
	public function actionClean() {
		/**@var Module $module */
		$module        = Yii::$app->getModule('mailer');
		$emailMessages = EmailMessage::find()->andWhere([
			'<',
			'created_at',
			(time() - ($module->cleanAfter * 3600 * 24)),
		])->all();
		foreach ($emailMessages as $emailMessage) {
			$emailMessage->delete();
		}
	}
}
