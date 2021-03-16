<?php

namespace phuong17889\email\models;

use BadMethodCallException;
use Exception;
use phuong17889\email\components\EmailManager;
use phuong17889\email\Module;
use phuong17889\email\twig\EmailTemplateLoader;
use phuong17889\language\models\Language;
use Twig\Environment;
use Twig_LoaderInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * This is the model class for table "{{%email_template}}".
 *
 * @property integer $id
 * @property string  $shortcut
 * @property string  $from
 * @property string  $subject
 * @property string  $text
 * @property string  $language
 */
class EmailTemplate extends ActiveRecord {

	public $params = [];

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%email_template}}';
	}

	/**
	 * @param $shortcut
	 * @param $language
	 *
	 * @return EmailTemplate
	 * @throws InvalidConfigException
	 */
	public static function loadTemplate($shortcut, $language) {
		return self::findByShortcut($shortcut, $language);
	}

	/**
	 * @param string $shortcut
	 * @param string $language
	 *
	 * @return self
	 * @throws InvalidConfigException
	 */
	public static function findByShortcut($shortcut, $language = null) {
		if ($language == null) {
			$language = Yii::$app->language;
		}
		$template = static::findOne([
			'shortcut' => $shortcut,
			'language' => $language,
		]);
		if ($template === null) {
			$manager      = EmailManager::getInstance();
			$languageCode = $language;
			$list         = [
				$language,
				$manager->defaultLanguage,
				'en-US',
				'en',
			];
			if (Module::hasMultiLanguage()) {
				$list = ArrayHelper::map(Language::getLanguages(), 'code', 'code');
			}
			foreach ($list as $l) {
				$template     = static::findOne([
					'shortcut' => $shortcut,
					'language' => $l,
				]);
				$languageCode = $l;
				if ($template) {
					return $template;
				}
			}
		} else {
			return $template;
		}
		if (YII_ENV_DEV) {
			return new self();
		}
		throw new BadMethodCallException('Template not found: ' . VarDumper::dumpAsString($shortcut) . ', language ' . $languageCode);
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[
				[
					'shortcut',
					'from',
					'text',
					'language',
				],
				'required',
			],
			[
				['text'],
				'string',
			],
			[
				[
					'shortcut',
					'from',
					'subject',
					'language',
				],
				'string',
				'max' => 255,
			],
			[
				[
					'shortcut',
					'language',
				],
				'unique',
				'targetAttribute' => [
					'shortcut',
					'language',
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id'       => 'ID',
			'shortcut' => Yii::t('email', 'Shortcut'),
			'from'     => Yii::t('email', 'From'),
			'subject'  => Yii::t('email', 'Subject'),
			'text'     => Yii::t('email', 'Text'),
			'language' => Yii::t('email', 'Language'),
		];
	}

	/**
	 * Renders attribute using twig
	 *
	 * @param string $attribute
	 * @param array  $params
	 *
	 * @return string
	 */
	public function renderAttribute($attribute, array $params) {
		$twig = $this->getTwig(new EmailTemplateLoader([
			'attributeName' => $attribute,
		]));
		try {
			$result = $twig->render($this->shortcut, $params);
		} catch (Exception $e) {
			$result = Yii::t('email', 'Error compiling email {0}: {1}', [
				$attribute,
				$e->getMessage(),
			]);
		}
		return $result;
	}

	/**
	 * Twig instance factory
	 *
	 * @param Twig_LoaderInterface $loader
	 *
	 * @return Environment
	 */
	protected function getTwig(Twig_LoaderInterface $loader) {
		$twig = new Environment($loader);
		$twig->setCache(false);
		return $twig;
	}

	/**
	 * Queues current template for sending with the given priority
	 *
	 * @param       $to
	 * @param array $params
	 * @param int   $priority
	 * @param array $files
	 * @param null  $bcc
	 *
	 * @return bool
	 * @throws InvalidConfigException
	 */
	public function queue($to, array $params = [], $priority = 0, $files = [], $bcc = null) {
		$text    = $this->renderAttribute('text', $params);
		$subject = $this->renderAttribute('subject', $params);
		EmailManager::getInstance()->queue($this->from, $to, $subject, $text, $priority, $files, $bcc);
		return true;
	}

	/**
	 * Queues current template for sending with the given priority
	 *
	 * @param       $to
	 * @param array $params
	 * @param array $files
	 * @param null  $bcc
	 *
	 * @return bool
	 * @throws InvalidConfigException
	 */
	public function send($to, array $params = [], $files = [], $bcc = null) {
		$text            = ($this->renderAttribute('text', $params));
		$subject         = $this->renderAttribute('subject', $params);
		$model           = new EmailMessage();
		$model->from     = $this->from;
		$model->to       = $to;
		$model->subject  = $subject;
		$model->text     = $text;
		$model->priority = 1;
		$model->files    = $files;
		$model->bcc      = $bcc;
		$model->status   = 2;
		$model->save();
		EmailManager::getInstance()->send($this->from, $to, $subject, $text, $files, $bcc);
		return true;
	}
}
