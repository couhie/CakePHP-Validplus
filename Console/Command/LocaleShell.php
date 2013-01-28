<?php
class LocaleShell extends Shell {
	var $tasks = array('Validplus.LocaleReady');

	/**
	 * startup
	 *
	 * @params
	 * @return
	 */
	function startup(){
		$this->_welcome();
	}

	/**
	 * main
	 *
	 * @param
	 * @return
	 */
	function main() {
		$this->out(__d('cake_console', '<info>Validplus.Locale Shell</info>'));
		$this->hr();
		$this->out(__d('cake_console', '[R]eady dummy file for i18n'));
		$this->out(__d('cake_console', '[H]elp'));
		$this->out(__d('cake_console', '[Q]uit'));

		$choice = strtolower($this->in(__d('cake_console', 'What would you like to do?'), array('R', 'H', 'Q')));
		switch ($choice) {
		case 'r':
			$this->LocaleReady->execute();
			break;
		case 'h':
			$this->out($this->OptionParser->help());
			break;
		case 'q':
			exit(0);
			break;
		default:
			$this->out(__d('cake_console', 'You have made an invalid selection. Please choose a command to execute by entering R, H, or Q.'));
		}
		$this->hr();
		$this->main();
	}

	/**
	 * merge
	 *
	 * @param
	 * @return
	 */
	/*
	function merge(){
		$this->LocaleReady->execute();
	}
	*/

	function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser->description(__d('cake_console', 'Validplus.locale Shell create a dummy file named i18n.php in the Config directory for i18n.'));
	}

}
