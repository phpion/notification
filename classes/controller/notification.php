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