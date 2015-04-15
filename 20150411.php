<?php


$data = [
    [
        'id' => 1,
        'parent_id' => 0,
        'value' => '親1',
    ],
    [
        'id' => 2,
        'parent_id' => 0,
        'value' => '親2',
    ],
    [
        'id' => 3,
        'parent_id' => 1,
        'value' => '子1-1',
    ],
    [
        'id' => 4,
        'parent_id' => 1,
        'value' => '子1-2',
    ],
    [
        'id' => 5,
        'parent_id' => 2,
        'value' => '子2-1',
    ],
];

$data = [
    [
        'id' => 1,
        'parent_id' => 0,
        'value' => '親1',
    ],
    [
        'id' => 2,
        'parent_id' => 0,
        'value' => '親2',
    ],
    [
        'id' => 3,
        'parent_id' => 1,
        'value' => '子1-1',
    ],
    [
        'id' => 4,
        'parent_id' => 1,
        'value' => '子1-2',
    ],
    [
        'id' => 5,
        'parent_id' => 2,
        'value' => '子2-1',
    ],
    [
        'id' => 6,
        'parent_id' => 4,
        'value' => '孫1-2-1',
    ],
    [
        'id' => 7,
        'parent_id' => 3,
        'value' => '孫1-1-1',
    ],
    [
        'id' => 8,
        'parent_id' => 7,
        'value' => 'ひ孫1-1-1-1',
    ],
    [
        'id' => 9,
        'parent_id' => 5,
        'value' => '孫2-1-1',
    ],
    [
        'id' => 10,
        'parent_id' => 5,
        'value' => '孫2-1-2',
    ],
    [
        'id' => 11,
        'parent_id' => 2,
        'value' => '子2-2',
    ],
    [
        'id' => 12,
        'parent_id' => 4,
        'value' => '孫1-2-2',
    ],
    [
        'id' => 13,
        'parent_id' => 9,
        'value' => 'ひ孫2-1-1-1',
    ],
    [
        'id' => 14,
        'parent_id' => 5,
        'value' => '孫2-1-3',
    ],
    [
        'id' => 15,
        'parent_id' => 2,
        'value' => '子2-3',
    ],
];

Output::$mode = Output::MODE_COMMAND;

$nodes = new Nodes($data);
$nodes->exec();

echo  $nodes->output();


class Nodes {
	
	private $data = array();
	
	private $topNodes = array();

	public function __construct(array $data) {
		$this->data = $data;
	}
	
	public function exec() {
		$data	= $this->data;
		
		$nodes	= self::createNodes($data);
		
		$this->topNodes = self::getTopNodes($nodes);
	}
	
	private static function createNodes(array $data) {
		$result = array();
		for ($i = 0, $cnt = count($data); $i < $cnt; ++$i) {
			$id			= $data[$i]['id'];
			$parent_id	= $data[$i]['parent_id'];
			$value		= $data[$i]['value'];
			
			$result[$id] = new Node($id, $parent_id, $value);
		}
		return $result;
	}
	
	private static function getTopNodes(array $nodes) {
		$results = array();
		foreach ($nodes as $node) {
			$parentId = $node->getParentId();
			if (empty($parentId)) {
				$results[] = $node;
			} else {
				$parentNode = $nodes[$parentId];
				$parentDepth = $parentNode->getDepth();
				$parentDepth++;
				$node->setDepth($parentDepth);
				$parentNode->addChild($node);
			}
		}
		return $results;
	}
	
	public function output() {
		$topNodes = $this->topNodes;
		return Output::runTopNode($topNodes);
	}
}


class Node {
	
	private $depth = 0;
	
	private $id = '';
	
	private $parent_id = '';
	
	private $value = '';
	
	private $children = array();


	public function __construct($id = '', $parent_id = '', $value = '') {
		$this->id			= $id;
		$this->parent_id	= $parent_id;
		$this->value		= $value;
	}
	
	public function addChild(self $node) {
		$this->children[] = $node;
	}

	public function output() {
		return Output::runNode($this);
	}

	public function getId() {
		return $this->id;
	}

	public function getParentId() {
		return $this->parent_id;
	}

	public function getValue() {
		return $this->value;
	}
	
	public function getDepth() {
		return $this->depth;
	}
	
	public function getChildren() {
		return $this->children;
	}

	public function setDepth($depth) {
		$this->depth = $depth;
	}
}

class Output {
	
	const ASTER = '*';
	const SPACE = "\t";

	const MODE_HTML = 'html';
	
	const MODE_COMMAND = 'Command';
	
	public static $mode = self::MODE_COMMAND;
	
	
	public static function runTopNode($topNodes) {
		$mode = self::$mode;
		switch ($mode) {
			case self::MODE_HTML:
				return self::runTopNodeHtml($topNodes);
			case self::MODE_COMMAND:
				return self::runTopNodeCommand($topNodes);
			default :
				throw new RuntimeException('Output Mode Error');
		}
	}
	
	private static function runTopNodeHtml($topNodes) {
		$results = array();
		
		$results[] = '<ul>';
		for ($i = 0, $cnt = count($topNodes); $i < $cnt; ++$i) {
			$results[] =  $topNodes[$i]->output();
		}
		$results[] = '</ul>';
		
		return join("\n", $results);
	}
	
	private static function runTopNodeCommand($topNodes) {
		$aster = self::ASTER;
		
		$results = array();
		for ($i = 0, $cnt = count($topNodes); $i < $cnt; ++$i) {
			$results[] = $aster . $topNodes[$i]->output();
		}
		return join("\n", $results);
	}
	
	public static function runNode($nodes) {
		$mode = self::$mode;
		switch ($mode) {
			case self::MODE_HTML:
				return self::runNodeHtml($nodes);
			case self::MODE_COMMAND:
				return self::runNodeCommand($nodes);
			default :
				throw new RuntimeException('Output Mode Error');
		}
	}
	
	private static function runNodeHtml(Node $nodes) {
		$value		= $nodes->getValue();
		$children	= $nodes->getChildren();
		
		$result = array();
		$result[] = '<li>';
		$result[] = $value;
		$result[] = '<ul>';
		for ($i = 0, $cnt = count($children); $i < $cnt; ++$i) {
			$result[] = $children[$i]->output();
		}
		$result[] = '</ul>';
		$result[] = '</li>';
		
		return join("\n", $result);
	}
	
	private static function runNodeCommand(Node $nodes) {
		$aster		= self::ASTER;
		$space		= self::SPACE;
		
		$value		= $nodes->getValue();
		$children	= $nodes->getChildren();
		$depth		= $nodes->getDepth() + 1;
		
		$indent		= str_repeat($space, $depth);
		
		$result = array();
		$result[] = $value;
		for ($i = 0, $cnt = count($children); $i < $cnt; ++$i) {
			$result[] = $indent . $aster . $children[$i]->output();
		}
		return join("\n", $result);
	}
	
}