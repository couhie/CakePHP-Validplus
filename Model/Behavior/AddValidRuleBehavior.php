<?php
/**
 * AddValidRuleBehavior.php
 * @author kohei hieda
 *
 */
class AddValidRuleBehavior extends ModelBehavior {

	/**
	 * setup
	 * @param $model
	 * @param $config
	 */
	function setup(&$model, $config = array()){
		//change encoding with parameter option.
		if (!empty($config['encoding'])) {
			mb_internal_encoding($config['encoding']);
		} else {
			mb_internal_encoding("UTF-8");
		}
	}

	/**
	 * alpha
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function alpha(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/[^A-Z]/i';
		return !preg_match($pattern, $word);
	}

	/**
	 * alphaNumericPlus
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function alphaNumericPlus(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/[^\\dA-Z@._\-]/i';
		return !preg_match($pattern, $word);
	}

	/**
	 * number
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function number(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^[\-]?[0-9]+$/';
		return preg_match($pattern, $word);
	}

	/**
	 * plusNumber
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function plusNumber(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^[1-9]+[0-9]*$/';
		return preg_match($pattern, $word);
	}

	/**
	 * minusNumber
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function minusNumber(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^[\-][0-9]+$/';
		return preg_match($pattern, $word);
	}

	/**
	 * email
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function email(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^[a-z0-9\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~\.]+?@([-a-z0-9]+\.)+[a-z]+$/i';
		return preg_match($pattern, $word);
	}

	/**
	 * hiraganaOnlyPlus
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function hiraganaOnlyPlus(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^[ぁ-んー\s　]*$/u';
		return preg_match($pattern, $word);
	}

	/**
	 * katakanaOnlyPlus
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function katakanaOnlyPlus(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^[ァ-ヶー゛゜\s　]*$/u';
		return preg_match($pattern, $word);
	}

	/**
	 * telFaxJp
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function telFaxJp(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^(0\d{1,4}[\s-]?\d{1,4}[\s-]?\d{1,4}|\+\d{1,3}[\s-]?\d{1,4}[\s-]?\d{1,4}[\s-]?\d{1,4})$/';
		return preg_match($pattern, $word);
	}

	/**
	 * postcode
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function postcode(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^\d{3}[\s-]?\d{4}$/';
		return preg_match($pattern, $word);
	}

	/**
	 * isOneOrMore
	 * @param $model
	 * @param $wordvalue
	 * @param $value
	 * @return boolean
	 */
	function isOneOrMore(&$model, $wordvalue, $value) {
		$keys = array_keys($wordvalue);
		$key = array_shift($keys);
		$word = array_shift($wordvalue);

		// IDが設定されていない場合は登録処理と見なす
		if (empty($model->id)) {
			return true;
		}

		if ($word == $value) {
			return true;
		}

		$conditions = array(
			$model->name.'.'.$model->primaryKey.' !='=>$model->id,
			$model->name.'.'.$key=>$value);
		$params = array(
			'conditions'=>$conditions,
			'recursive'=>-1);

		if ($model->find('count', $params) > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * notExists
	 * @param $model
	 * @param $wordvalue
	 * @param $args
	 * @return boolean
	 */
	function notExists(&$model, $wordvalue, $args) {
		$keys = array_keys($wordvalue);
		$key = array_shift($keys);
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		if (empty($args['model'])) {
			$targetModel = $model;
		} else {
			$targetModel =& ClassRegistry::init($args['model']);
		}

		if (empty($args['field'])) {
			$fieldName = $key;
		} else {
			$fieldName = $args['field'];
		}

		$conditions = array(
			$targetModel->name.'.'.$fieldName=>$word);
		if (!empty($targetModel->id)) {
			$conditions = Set::merge($conditions, array($targetModel->name.'.'.$targetModel->primaryKey.' <>'=>$targetModel->id));
		}
		if (!empty($args['conditions'])) {
			$conditions = Set::merge($conditions, $args['conditions']);
		}

		$params = array(
			'conditions'=>$conditions,
			'recursive'=>-1);

		if ($targetModel->find('count', $params) > 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * match
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function match(&$model, $wordvalue) {
		if (empty($model->id)) {
			return true;
		}

		$keys = array_keys($wordvalue);
		$key = array_shift($keys);
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$conditions = array(
			$model->name.'.'.$model->primaryKey=>$model->id,
			$model->name.'.'.$key=>$word);
		$params = array(
			'conditions'=>$conditions,
			'recursive'=>-1);

		if ($model->find('count', $params) > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * datetime
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function datetime(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		if (preg_match('/^([0-9]{4})-([0]?[1-9]|[1][0-2])-([0]?[1-9]|[1-2][0-9]|[3][0-2]) ([0-1]?[0-9]|[2][0-3])(?::([0-5]?[0-9]))?(?::([0-5]?[0-9]))?$/', $word, $matches)) {
			$day = intval($matches[3]);
			$time = mktime($matches[4], empty($matches[5]) ? '0' : $matches[5], empty($matches[6]) ? '0' : $matches[6], $matches[2], $matches[3], $matches[1]);
		}

		if (preg_match('/^([0-9]{4})-([0]?[1-9]|[1][0-2])-([0]?[1-9]|[1-2][0-9]|[3][0-2])$/', $word, $matches)) {
			$day = intval($matches[3]);
			$time = mktime('0', '0', '0', $matches[2], $matches[3], $matches[1]);
		}

		if (!empty($time) && date('d', $time) == $day) {
			return true;
		}		

		return false;
	}

	/**
	 * separatedDatetime
	 * @param $model
	 * @param $wordvalue
	 * @param $params
	 * @return boolean
	 */
	function separatedDatetime(&$model, $wordvalue, $params) {
		$key = array_shift(array_keys($wordvalue));

		$timeKey = $key;
		if (!empty($params['timePrefix'])) {
			$timeKey = $params['timePrefix'].$timeKey;
		}
		if (!empty($params['timeSuffix'])) {
			$timeKey = $timeKey.$params['timeSuffix'];
		}

		$data = null;
		if (empty($model->data[$model->alias][$timeKey])) {
			$data = array($key=>$model->data[$model->alias][$key]);
		} else {
			$data = array($key=>$model->data[$model->alias][$key].' '.$model->data[$model->alias][$timeKey]);
		}

		return $this->datetime($model, $data);
	}

	/**
	 * compareDatetime
	 * @param $model
	 * @param $wordvalue
	 * @param $params
	 * @return boolean
	 */
	function compareDatetime(&$model, $wordvalue, $params) {
		foreach ($params['order'] as $key) {
			if (!$this->datetime($model, array($key=>$model->data[$model->alias][$key]))) {
				return true;
			}
		}

		$current = null;
		$next = null;
		foreach ($params['order'] as $key) {
			$next = $model->data[$model->alias][$key];

			if (empty($next)) {
				continue;
			}

			if (!empty($current)) {
				if (strtotime($next) <= strtotime($current)) {
					return false;
				}
			}

			$current = $next;
		}

		return true;
	}

	/**
	 * compareSeparatedDatetime
	 * @param $model
	 * @param $wordvalue
	 * @param $params
	 * @return boolean
	 */
	function compareSeparatedDatetime(&$model, $wordvalue, $params) {
		foreach ($params['order'] as $key) {
			if (!$this->separatedDatetime($model, array($key=>$model->data[$model->alias][$key]), $params)) {
				return true;
			}
		}

		$current = null;
		$next = null;
		foreach ($params['order'] as $key) {
			$timeKey = $key;
			if (!empty($params['timePrefix'])) {
				$timeKey = $params['timePrefix'].$timeKey;
			}
			if (!empty($params['timeSuffix'])) {
				$timeKey = $timeKey.$params['timeSuffix'];
			}

			if (empty($model->data[$model->alias][$timeKey])) {
				$next = $model->data[$model->alias][$key];
			} else {
				$next = $model->data[$model->alias][$key].' '.$model->data[$model->alias][$timeKey].str_repeat(':00', 2 - substr_count($model->data[$model->alias][$timeKey], ':'));
			}

			if (empty($next)) {
				continue;
			}

			if (!empty($current)) {
				if (strtotime($next) <= strtotime($current)) {
					return false;
				}
			}

			$current = $next;
		}

		return true;
	}

	/**
	 * futureDatetime
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function futureDatetime(&$model, $wordvalue) {
		if (!$this->datetime($model, $wordvalue)) {
			return false;
		}

		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		return strtotime(date('Y-m-d')) <= strtotime($word);
	}

	/**
	 * popularString
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function popularString(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^([一-龠ぁ-んァ-ヴーa-zA-Z0-9ａ-ｚＡ-Ｚ０-９ !"#$%&\'()-=^~\\¥|@`\[{;+:*\]},<.>\/\?_　！”＃＄％＆’（）ー＝＾〜＼￥｜＠｀「『；＋：＊」』、＜。＞／？＿]|\r|\n)*$/u';
		return preg_match($pattern, $word);
	}

	/**
	 * rgb
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function rgb(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^[0-9a-f]{6}$/i';
		return preg_match($pattern, $word);
	}

	/**
	 * requiredOne
	 * @param $model
	 * @param $wordvalue
	 * @param $keys
	 * @return boolean
	 */
	function requiredOne(&$model, $wordvalue, $keys) {
		$word = array_shift($wordvalue);

		$count = 0;
		foreach ($keys as $key) {
			if (isset($model->data[$model->name][$key]) && $model->data[$model->name][$key] != '') {
				$count++;
			}
		}

		if ($count == 1) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * countRange
	 * @param $model
	 * @param $wordvalue
	 * @param $args
	 * @return boolean
	 */
	function countRange(&$model, $wordvalue, $args) {
		$word = array_shift($wordvalue);
		if (empty($word) || !is_array($word)) {
			return false;
		}
		$count = count($word);
		if (isset($args['min'])) {
			if ($count < $args['min']) {
				return false;
			}
		}
		if (isset($args['max'])) {
			if ($args['max'] < $count) {
				return false;
			}
		}
		return true;
	}

	/**
	 * time24
	 * @param $model
	 * @param $wordvalue
	 * @param $args
	 * @return boolean
	 */
	function time24(&$model, $wordvalue, $args) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '';
		if (strpos($args, 'h') !== false) {
			$pattern .= '([01]\d|2[0-3])';
		}
		if (strpos($args, 'm') !== false) {
			if (!empty($pattern)) {
				$pattern .= ':';
			}
			$pattern .= '[0-5]\d';
		}
		if (strpos($args, 's') !== false) {
			if (!empty($pattern)) {
				$pattern .= ':';
			}
			$pattern .= '[0-5]\d';
		}

		$pattern = "/^{$pattern}$/";
		return preg_match($pattern, $word);
	}

	/**
	 * googleAnalyticsCode
	 * @param $model
	 * @param $wordvalue
	 * @return boolean
	 */
	function googleAnalyticsCode(&$model, $wordvalue) {
		$word = array_shift($wordvalue);

		if ($word == '') {
			return true;
		}

		$pattern = '/^UA-\d+-\d+$/';
		return preg_match($pattern, $word);
	}

}
