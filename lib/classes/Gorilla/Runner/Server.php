<?php

class Gorilla_Runner_Server extends Gorilla_Runner {
	public function get_options() {
		if (!empty($this->options)) {
			return $this->options;
		}
		$options = array(
			'uri' => 'http://localhost/wptrunk/wp-app.php',
			'auth' => false
		);
		if (!empty($_REQUEST)) {
			if (empty($_REQUEST['uri'])) {
				throw new Exception('URI must be specified');
			}
			$options['uri'] = $_REQUEST['uri'];
			if (!empty($_REQUEST['auth'])) {
				$options['auth'] = $_REQUEST['auth'];
			}
		}

		if (empty($options['uri'])) {
			throw new Exception('URI must be specified');
		}
		$this->set_options($options);
		return $this->options;
	}
}