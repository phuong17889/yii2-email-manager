<?php

namespace phuong17889\email\models;

use baibaratsky\yii\behaviors\model\SerializedAttributes;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * This is the model class for table "{{%email_message}}".
 *
 * @property integer $id
 * @property integer $status
 * @property integer $priority
 * @property string  $from
 * @property string  $to
 * @property string  $subject
 * @property string  $text
 * @property string  $created_at
 * @property string  $sent_at
 * @property string  $bcc
 * @property string  $files
 * @property int     $try_time
 */
class EmailMessage extends ActiveRecord {

	const STATUS_NEW         = 0;

	const STATUS_IN_PROGRESS = 1;

	const STATUS_SENT        = 2;

	const STATUS_ERROR       = 3;

	const STATUS             = [
		'New',
		'In progress',
		'Sent',
		'Error',
	];

	public $files = [];

	public $bcc   = [];

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%email_message}}';
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors() {
		return [
			'timestamp'            => [
				'class'      => TimestampBehavior::class,
				'attributes' => [
					static::EVENT_BEFORE_INSERT => ['created_at'],
				],
			],
			'serializedAttributes' => [
				'class'      => SerializedAttributes::class,
				'attributes' => [
					'files',
					'bcc',
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[
				[
					'status',
					'priority',
					'try_time',
				],
				'integer',
			],
			[
				['text'],
				'string',
			],
			[
				[
					'created_at',
					'sent_at',
					'files',
				],
				'safe',
			],
			[
				[
					'from',
					'to',
					'subject',
				],
				'string',
				'max' => 255,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'         => 'ID',
			'status'     => Yii::t('email', 'Status'),
			'priority'   => Yii::t('email', 'Priority'),
			'from'       => Yii::t('email', 'From'),
			'to'         => Yii::t('email', 'To'),
			'subject'    => Yii::t('email', 'Subject'),
			'text'       => Yii::t('email', 'Text'),
			'created_at' => Yii::t('email', 'Created At'),
			'sent_at'    => Yii::t('email', 'Sent At'),
			'try_time'   => Yii::t('email', 'Try Time'),
		];
	}
}
