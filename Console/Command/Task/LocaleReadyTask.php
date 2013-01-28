<?php
class LocaleReadyTask extends Shell {

	var $messageArray = array();

	function execute() {
		$this->out();

		foreach (scandir(APP.'Model'.DS) as $fileName) {
			if (preg_match('/(.+)\.php/', $fileName, $className) == 0) {
				continue;
			}

			$class = ClassRegistry::init(array('class'=>$className[1]));

			if (!empty($class->validation_patterns)) {
				array_walk_recursive($class->validation_patterns, array($this, '_get_message'));
			}

			if (!empty($class->validate)) {
				array_walk_recursive($class->validate, array($this, '_get_message'));
			}
		}

		$this->messageArray = array_unique($this->messageArray);

		if (!$this->_put_messages()) {
			$this->out(__d('cake_console', 'Failed to ready.'));
			return;
		}

		$this->out();
		$this->out(__d('cake_console', 'Done.'));
	}

	function _get_message($value, $key) {
		if ($key === 'message') {
			$this->messageArray[] = $value;
		}
	}

	function _put_messages() {
		$filename = APP.'Config'.DS.'i18n.php';
		if (!$handle = fopen($filename, 'w')) {
			return false;
		}

		if (fwrite($handle, "<?php\n") === false) {
			break;
		}

		$ret = true;;
		foreach ($this->messageArray as $message) {
			$this->out(__d('cake_console', $message));
			if (($ret = fwrite($handle, "__('{$message}')\n")) === false) {
				break;
			}
		}

		fclose($handle);
		return $ret;
	}

}
