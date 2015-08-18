<?php 
class TreeDisplayComponent extends Object
{
	var $components = array();
	var $controller = null;
	
	function initialize(&$controller) {
		$this->controller =& $controller;
	}
	function init(&$controller) {
		$this->controller =& $controller;
	}
	
	function initData($data,$options=array()){
		$local = array('modelClass');
		$opt = $this->parseOpt($options);
		$Model = $opt['modelClass'];
		$param = array_diff_key($opt,array_flip($local));
		$param['data'] = $data;
		$showTree = empty($this->controller->params['named']['sort'])?(!empty($this->controller->paginate['order']) && $this->controller->paginate['order'] == $Model->alias.'.lft'):$this->controller->params['named']['sort'] == 'lft';
		$param['active'] = $showTree;
		if($showTree && !empty($data)){
			$findOpt = array('conditions'=>array($data[0][$Model->alias]['lft'].' BETWEEN `'.$Model->alias.'.lft`+1 AND `'.$Model->alias.'.rght`-1'),'order'=>''.$Model->alias.'.lft');
			if(!empty($root)){
				$this->paginate['conditions'][] = $Model->alias.'.lft BETWEEN '.($root[$Model->alias]['lft']+1).' AND '.($root[$Model->alias]['rght']-1);
			}
			$ancestors = $Model->find('all',$findOpt);
			foreach($ancestors as &$a){
				$a[$Model->alias]['continuing'] = true;
			}
			$param['ancestors'] = $ancestors;
		}
		$this->controller->params['treeDisplay'][$opt['key']] = $param;
	}
	
	function parseOpt($options){
		$defOpt = array(
			'model' => null,
			'modelClass' => null,
			'key' => null,
		);
		if(empty($options['model'])){
			$options['model'] = $this->controller->modelClass;
		}
		if(empty($options['modelClass'])){
			$options['modelClass'] = ClassRegistry::init($options['model']);
		}
		if(empty($options['key'])){
			$options['key'] = $options['model'];
		}
		$opt = array_merge($defOpt,$options);
		return $opt;
	}
}