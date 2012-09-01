<?php
return array(
	'default' => array(
		'template' => array(
			'confirm' => '<div class="notification confirm" style="color: green;">%s</div>',
			'error' => '<div class="notification error" style="color: red;">%s</div>',
			'default' => '<div class="notification %s">%s</div>'
		),
		'session' => array(
			'type' => 'native',
			'key' => 'notification'
		)
	)
);