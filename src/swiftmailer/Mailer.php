<?php
/**
 * Created by phuong17889.
 * @project yii2-cms
 * @author  Thuc
 * @email   thuchm92[at]gmail.com
 * @date    9/12/2016
 * @time    4:33 PM
 */

namespace phuong17889\email\swiftmailer;

use phuong17889\email\Module;
use Yii;

class Mailer extends \yii\swiftmailer\Mailer {

	/**
	 * {@inheritDoc}
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init() {
		parent::init();
		if (Module::hasSetting()) {
			$configure              = [
				'class'         => 'Swift_SmtpTransport',
				'host'          => Yii::$app->setting->get('smtp_host'),
				'username'      => Yii::$app->setting->get('smtp_user'),
				'password'      => Yii::$app->setting->get('smtp_password'),
				'port'          => Yii::$app->setting->get('smtp_port'),
				'encryption'    => Yii::$app->setting->get('smtp_encryption'),
				'streamOptions' => [
					'ssl' => [
						'allow_self_signed' => true,
						'verify_peer'       => false,
						'verify_peer_name'  => false,
					],
				],
			];
			$this->useFileTransport = false;
			$this->setTransport($configure);
		}
	}
}
