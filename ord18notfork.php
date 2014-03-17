<?php

/**
 * http://nabetani.sakura.ne.jp/hena/ord18notfork/
 * フォークじゃない 〜 横へな 2014.2.1 問題の回答
 * 
 * 2014/03/15 NagoyaPhp h_hanai
 */

/**
 * レジクラス
 */
class Register {
	
	/**
	 * レジの処理能力
	 * @var type 
	 */
	protected $processingCapacity = 0;

	/**
	 * 並んでいる客
	 * @var type 
	 */
	protected $customers = array();
	
	/**
	 * コンストラクタ
	 * @param type $processingCapacity（レジの処理能力）
	 */
	public function __construct($processingCapacity) {
		$this->processingCapacity = $processingCapacity;
	}

	/**
	 * 会計処理
	 * @return type
	 */
	public function runAccounting() {
		for ($i = 0; $i < $this->processingCapacity; ++$i) {
			$customer = array_shift($this->customers);
			if (is_null($customer)) {
				return;
			}
			if (!$customer->accounting()) {
				array_unshift($this->customers, $customer);
				break;
			}
		}
	}
	
	/**
	 * 並んでいる客数を取得
	 * @return type
	 */
	public function getCustomersCnt() {
		return count($this->customers);
	}
	
	/**
	 * 新たな客を並ばせる
	 * @param array $customers
	 */
	public function addCustomers(array $customers) {
		for ($i = 0, $cnt = count($customers); $i < $cnt; ++$i) {
			 array_push($this->customers, $customers[$i]);
		}
	}
}

/**
 * 客クラス
 */
class Customer {
	
	protected $x = null;

	public function __construct($x = null) {
		$this->x = $x;
	}
	/**
	 * 会計処理
	 * @return type
	 */
	public function accounting() {
		return is_null($this->x)? true: false;
	}
}

/**
 * プロセスの実行クラス
 */
class Process {
	
	const X = 'x';
	const RUN = '.';
	
	/**
	 * プロセスの実行
	 * @param ProcessDTO $dto
	 */
	public static function run(ProcessDTO $dto) {
		$inputs		= $dto->getInputs();
		$registers	= $dto->getRegisters();
		for ($i = 0, $cnt = count($inputs); $i < $cnt; ++$i) {
			$input = $inputs[$i];
			
			if ($input !== self::RUN) {
				// 列に人が補充される
				// 入力情報から客を取得する
				$customers = self::_getCustomers($input);
				// 客をレジに並ばせる
				self::_settingCustomers($registers, $customers);
			} else {
				// 会計処理を行う
				self::_runAccounting($registers);
			}
		}
		// 実行結果を取得
		$result = self::_getResult($registers);
		$dto->setResult($result);
	}
	
	/**
	 * 客をレジに並ばせる
	 * @param array $registers
	 * @param array $customers
	 */
	private static function _settingCustomers(array $registers, array $customers) {
		$addIndex = null;
		$customersCnt = null;
		for ($i = 0, $cnt = count($registers); $i < $cnt; ++$i) {
			$register = $registers[$i];
			if (is_null($customersCnt) || $customersCnt > $register->getCustomersCnt()) {
				
				$addIndex = $i;
				$customersCnt =  $register->getCustomersCnt();
			}
		}
		$registers[$addIndex]->addCustomers($customers);
	}

	/**
	 * 実行結果を取得
	 * @param array $registers
	 * @return type
	 */
	private static function _getResult(array $registers) {
		$results = array();
		for ($i = 0, $cnt = count($registers); $i < $cnt; ++$i) {
			$results[] = $registers[$i]->getCustomersCnt();
		}
		return join(',', $results);
	}

	/**
	 * レジが会計処理を行う
	 * @param array $registers
	 */
	private static function _runAccounting(array $registers) {
		for ($i = 0, $cnt = count($registers); $i < $cnt; ++$i) {
			$register = $registers[$i];
			$register->runAccounting();
		}
	}

	/**
	 * 入力情報から客を取得する
	 * @param type $input
	 * @return \Customer
	 */
	private static function _getCustomers($input) {
		if ($input === self::X) {
			return array(new Customer($input));
		}
		$customers = array();
		for ($i = 1; $i <= $input; ++$i) {
			$customers[] = new Customer();
		}
		return $customers;
	}
}

/**
 * プロセスデータ設定用DTOクラス
 */
class ProcessDTO {
	
	/**
	 * 実行結果
	 * @var type 
	 */
	protected $result = '';

	/**
	 * レジオブジェクト
	 * @var type 
	 */
	protected $registers = array();

	/**
	 * 入力パラメータ
	 * @var type 
	 */
	protected $inputs = array();
	
	/**
	 * レジ情報の追加
	 * @param Register $register
	 */
	public function addRegister(Register $register) {
		array_push($this->registers, $register);
	}

	/**
	 * レジ情報の取得
	 * @return type
	 */
	public function getRegisters() {
		return $this->registers;
	}
	
	/**
	 * 入力情報の設定
	 * @param type $input
	 */
	public function setInputParams($input) {
		for ($i = 0, $cnt = strlen($input); $i < $cnt; ++$i) {
			$this->inputs[] = substr($input, $i, 1);
		}
	}
	
	/**
	 * 入力情報の取得
	 * @param type $input
	 */
	public function getInputs() {
		return $this->inputs;
	}

	/**
	 * 実行結果の設定
	 * @param type $input
	 */
	public function setResult($result) {
		$this->result = $result;
	}

	/**
	 * 実行結果の取得
	 * @return type
	 */
	public function getResult() {
		return $this->result;
	}
}

/**
 * テストデータ
 * （入力値　=> 想定結果）
 */
$inputs = array(
	'42873x.3.'		=> '0,4,2,0,0',
	'1'				=> '1,0,0,0,0',
	'.'				=> '0,0,0,0,0',
	'x'				=> '1,0,0,0,0',
	'31.'			=> '1,0,0,0,0',
	'3x.'			=> '1,1,0,0,0',
	'99569x'		=> '9,9,6,6,9',
	'99569x33'		=> '9,9,9,9,9',
	'99569x33.'		=> '7,2,6,4,7',
	'99569x33..'	=> '5,0,4,0,5',
	'12345x3333.'	=> '4,0,3,2,3',
	'54321x3333.'	=> '3,0,3,0,4',
	'51423x3333.'	=> '3,4,4,0,4',
	'12x34x.'		=> '1,0,1,0,2',
	'987x654x.32'	=> '7,6,4,10,5',
	'99999999999x99999999.......9.'	=> '20,10,12,5,20',
	'997'			=> '9,9,7,0,0',
	'.3.9'			=> '1,9,0,0,0',
	'832.6'			=> '6,6,0,0,0',
	'.5.568'		=> '3,5,6,8,0',
	'475..48'		=> '4,8,0,0,0',
	'7.2..469'		=> '1,4,6,9,0',
	'574x315.3'		=> '3,3,1,7,1',
	'5.2893.x98'	=> '10,9,5,4,1',
	'279.6xxx..4'	=> '2,1,4,1,1',
	'1.1.39..93.x'	=> '7,1,0,0,0',
	'7677749325927'		=> '16,12,17,18,12',
	'x6235.87.56.9.'	=> '7,2,0,0,0',
	'4.1168.6.197.6.'	=> '0,0,3,0,0',
	'2.8.547.25..19.6'	=> '6,2,0,0,0',
	'.5.3x82x32.1829..'	=> '5,0,5,0,7',
	'x.1816..36.24.429.'		=> '1,0,0,0,7',
	'79.2.6.81x..26x31.1'		=> '1,0,2,1,1',
	'574296x6538984..5974'		=> '14,13,10,15,14',
	'99.6244.4376636..72.6'		=> '5,6,0,0,3',
	'1659.486x5637168278123'	=> '17,16,16,18,17',
	'.5.17797.x626x5x9457.3.'		=> '14,0,3,5,8',
	'..58624.85623..4.7..23.x'		=> '1,1,0,0,0',
	'716.463.9.x.8..4.15.738x4'		=> '7,3,5,8,1',
	'22xx.191.96469472.7232377.'	=> '10,11,18,12,9',
	'24..4...343......4.41.6...2'	=> '2,0,0,0,0',
	'32732.474x153.866..4x29.2573'		=> '7,5,7,8,5',
	'786.1267x9937.17.15448.1x33.4'		=> '4,4,8,4,10',
	'671714849.149.686852.178.895x3'	=> '13,16,13,10,12',
	'86x.47.517..29621.61x937..xx935'	=> '7,11,8,8,10',
	'.2233.78x.94.x59511.5.86x3.x714.'	=> '4,6,10,8,8',
	'.793...218.687x415x13.1...x58576x'		=> '8,11,8,6,9',
	'6.6x37.3x51x932.72x4x33.9363.x7761'	=> '15,13,15,12,15',
	'6..4.x187..681.2x.2.713276.669x.252'	=> '6,7,8,6,5',
	'.6.xx64..5146x897231.x.21265392x9775'	=> '19,17,19,20,17',
	'334.85413.263314.x.6293921x3.6357647x'	=> '14,14,12,16,10',
	'4.1..9..513.266..5999769852.2.38x79.x7'	=> '12,10,13,6,10',
);

/**
 * テスト
 */
foreach ($inputs as $input => $checkResult) {

	$dto = new ProcessDTO();
	// レジオブジェクトの設定
	$dto->addRegister(new Register(2));
	$dto->addRegister(new Register(7));
	$dto->addRegister(new Register(3));
	$dto->addRegister(new Register(5));
	$dto->addRegister(new Register(2));
	// 入力パラメータの設定
	$dto->setInputParams($input);
	// 処理プロセスを実行
	Process::run($dto);
	// 処理結果を取得
	$result = $dto->getResult();
	// テスト結果の出力
	if ($result === $checkResult) {
		echo 'OK::' . $result . '<br />';
	} else {
		echo 'NG::' . $result . '<br />';
	}
}