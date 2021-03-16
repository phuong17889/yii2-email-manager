<?php

namespace phuong17889\email\models\search;

use kartik\daterange\DateRangeBehavior;
use phuong17889\email\models\EmailMessage;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EmailMessageSearch represents the model behind the search form about `common\models\EmailMessage`.
 */
class EmailMessageSearch extends EmailMessage {

	public $createTimeStart;

	public $createTimeEnd;

	public $sendTimeStart;

	public $sendTimeEnd;

	/**
	 * @return array
	 */
	public function behaviors() {
		return [
			[
				'class'              => DateRangeBehavior::class,
				'attribute'          => 'created_at',
				'dateStartAttribute' => 'createTimeStart',
				'dateEndAttribute'   => 'createTimeEnd',
			],
			[
				'class'              => DateRangeBehavior::class,
				'attribute'          => 'sent_at',
				'dateStartAttribute' => 'sendTimeStart',
				'dateEndAttribute'   => 'sendTimeEnd',
			],
		];
	}

	/**
	 * @return array
	 */
	public function rules() {
		return [
			[
				[
					'id',
					'status',
					'priority',
				],
				'integer',
			],
			[
				[
					'from',
					'to',
					'subject',
					'text',
					'created_at',
					'sent_at',
					'bcc',
					'files',
				],
				'safe',
			],
		];
	}

	/**
	 * @return array
	 */
	public function scenarios() {
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	/**
	 * @param $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params) {
		$query        = EmailMessage::find();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort'  => [
				'defaultOrder' => [
					'id' => SORT_DESC,
				],
			],
		]);
		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}
		$query->andFilterWhere([
			'id'       => $this->id,
			'status'   => $this->status,
			'priority' => $this->priority,
		]);
		$query->andFilterWhere([
			'>=',
			'created_at',
			$this->createTimeStart,
		])->andFilterWhere([
			'<',
			'created_at',
			$this->createTimeEnd,
		]);
		$query->andFilterWhere([
			'>=',
			'sent_at',
			$this->sendTimeStart,
		])->andFilterWhere([
			'<',
			'sent_at',
			$this->sendTimeEnd,
		]);
		$query->andFilterWhere([
			'like',
			'from',
			$this->from,
		])->andFilterWhere([
			'like',
			'to',
			$this->to,
		])->andFilterWhere([
			'like',
			'subject',
			$this->subject,
		])->andFilterWhere([
			'like',
			'text',
			$this->text,
		])->andFilterWhere([
			'like',
			'bcc',
			$this->bcc,
		])->andFilterWhere([
			'like',
			'files',
			$this->files,
		]);
		return $dataProvider;
	}
}
