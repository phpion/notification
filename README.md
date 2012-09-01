Notification module for Kohana framework
========================================

This module provides methods to set and read notification messages between application requests. You can use it to display global error messages, confirmations after redirects or just custom messages.

Installation
------------

1. Download source from Github.
2. Extract files into your modules directory.
3. Enable notification module in your bootstrap.php

Usage
-----

Example of basic usage is shown in notification controller (classes/controller/notification.php).

``` php
<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Notification extends Controller {
	public function action_index() {
		$notification = Notification::instance();
		
		if ($this->request->method() === 'POST') {
			$validation = Validation::factory($this->request->post());
			
			$validation
				->rule('email', 'not_empty')
				->rule('email', 'Valid::email')
			;
			
			if ($validation->check()) {
				$key = $notification->confirm('Ok!');
				
				$this->request->redirect('notification/index');
			}
			else {
				$key = $notification->error('Error!');
				
				$email = $validation['email'];
			}
		}
		else {
			$email = '';
		}
		
		$body = $notification->render();
		$body .= Form::open();
		$body .= 'E-mail: '.Form::input('email', $email);
		$body .= Form::submit(NULL, 'Go!');
		$body .= Form::close();
		
		$this->response->body($body);
	}
}
```

Creating custom messages
------------------------

Module provides 2 types of messages (Notification::CONFIRM and Notification::ERROR) and 2 methods to create notifications (confirm($message) and error($message). You can also create your own types - just extend Notification class by adding your own methods, for example:

``` php
public function warning($message) {
	$this->set('warning', $message);
}

//

$notification->warning('I warn you!');
```

You can also just use set($type, $message, $session) method without any extending:

``` php
$notification->set('warning', 'I warn you!');
```

Messages templating
-------------------

In configuration file (config/notification.php) you can find:

``` php
<?php
return array(
	'default' => array(
		'template' => array(
			'confirm' => '<div class="notification confirm" style="color: green;">%s</div>',
			'error' => '<div class="notification error" style="color: red;">%s</div>',
			'default' => '<div class="notification %s">%s</div>'
		)
	)
);
```

Rendering warning notification with this configuration will use default template:

``` html
<div class="notification warning">I warn you!</div>
```

If you want to define custom template for notification type you should add template to configuration:
``` php
<?php
return array(
	'default' => array(
		'template' => array(
			'confirm' => '<div class="notification confirm" style="color: green;">%s</div>',
			'error' => '<div class="notification error" style="color: red;">%s</div>',
			
			'warning' => '<div class="notification warning" style="color: orange;">Hey man! %s</div>',
			
			'default' => '<div class="notification %s">%s</div>'
		)
	)
);
```

Now our output will be:

``` html
<div class="notification warning">Hey man! I warn you!</div>
```