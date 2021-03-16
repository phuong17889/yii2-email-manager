<?php

use kartik\grid\DataColumn;
use kartik\grid\GridView;
use phuong17889\email\helpers\EmailHelper;
use phuong17889\email\models\EmailMessage;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var yii\web\View                                    $this
 * @var yii\data\ActiveDataProvider                     $dataProvider
 * @var phuong17889\email\models\search\EmailMessageSearch $searchModel
 */
$this->title                   = Yii::t('email', 'Email History');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-message-index">
	<?php Pjax::begin();
	try {
		echo GridView::widget([
			'dataProvider' => $dataProvider,
			'filterModel'  => $searchModel,
			'columns'      => [
				['class' => 'yii\grid\SerialColumn'],
				[
					'attribute' => 'to',
					'format'    => 'html',
					'value'     => function(EmailMessage $data) {
						return EmailHelper::protectOff($data->to);
					},
				],
				'subject',
				[
					'attribute' => 'status',
					'filter'    => EmailMessage::STATUS,
					'value'     => function(EmailMessage $data) {
						return EmailMessage::STATUS[$data->status];
					},
				],
				[
					'class'               => DataColumn::class,
					'attribute'           => 'created_at',
					'filterType'          => GridView::FILTER_DATE_RANGE,
					'filterWidgetOptions' => [
						'readonly'      => 'readonly',
						'convertFormat' => true,
						'pluginOptions' => [
							'locale'    => ['format' => 'Y-m-d'],
							'autoclose' => true,
						],
						'pluginEvents'  => [
							"cancel.daterangepicker" => 'function(ev,picker){$(this).val("").trigger("change");}',
						],
					],
					'format'              => [
						'datetime',
					],
				],
				[
					'class'               => DataColumn::class,
					'attribute'           => 'sent_at',
					'filterType'          => GridView::FILTER_DATE_RANGE,
					'filterWidgetOptions' => [
						'readonly'      => 'readonly',
						'convertFormat' => true,
						'pluginOptions' => [
							'locale'    => ['format' => 'Y-m-d'],
							'autoclose' => true,
						],
						'pluginEvents'  => [
							"cancel.daterangepicker" => 'function(ev,picker){$(this).val("").trigger("change");}',
						],
					],
					'format'              => [
						'datetime',
					],
				],
				[
					'class'    => 'yii\grid\ActionColumn',
					'template' => '{view}{resend}',
					'buttons'  => [
						'resend' => function($url, $model) {
							return Html::a('<i class="fa fa-refresh"></i>', Url::to([
								'/mailer/message/resend',
								'id' => $model->id,
							]), ['title' => Yii::t('email', 'Resend'),]);
						},
					],
				],
			],
			'responsive'   => true,
			'hover'        => true,
			'condensed'    => true,
			'floatHeader'  => true,
			'panel'        => [
				'heading'    => '',
				'type'       => 'info',
				'before'     => '',
				'after'      => Html::a('<i class="glyphicon glyphicon-repeat"></i> ' . Yii::t('email', 'Reset List'), ['index'], ['class' => 'btn btn-info']),
				'showFooter' => false,
			],
		]);
	} catch (Exception $e) {
		echo '<pre>';
		print_r($e);
		die;
	}
	Pjax::end(); ?>

</div>
