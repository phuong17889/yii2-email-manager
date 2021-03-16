<?php

use yii\helpers\Html;

/**
 * @var yii\web\View               $this
 * @var common\models\EmailMessage $model
 */
$this->title                   = $model->subject;
$this->params['breadcrumbs'][] = [
	'label' => 'Email Messages',
	'url'   => ['index'],
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-message-view">
	<div class="page-header">
		<h1><?= Html::encode($this->title) ?></h1>
	</div>

	<div style="background: #ddd">
		<div class="container">
			<?= $model->text ?>
		</div>
	</div>

</div>
