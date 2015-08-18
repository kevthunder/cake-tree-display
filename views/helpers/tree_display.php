<?php
class TreeDisplayHelper extends AppHelper {

	var $helpers = array('Html');
	
	var $stacks = array();
	var $data = array();
	
	function isActive($options){
		$opt = $this->parseOpt($options);
		return $opt['active'];
	}
	function columnClass($options=array()){
		if($this->isActive($options)){
			$opt = $this->parseOpt($options);
			echo ' '.$opt['columnClass'];
		}
	}
	
	function fullData($data,$options=array()){
		if($this->isActive($options)){
			$opt = $this->parseOpt($options);
			$this->data[$opt['key']] = $data;
			$data = array_merge($opt['ancestors'],$data);
		}
		return $data;
	}
	
	function columnContent($data,$options=array()){
		if($this->isActive($options)){
			$html = '';
			$opt = $this->parseOpt($options);
			if(!array_key_exists($opt['key'],$this->stacks)){
				$this->Html->css('/tree_display/css/tree_column',null,array('inline'=>false));
				$this->Html->scriptBlock('
					(function( $ ) {
						$(function(){
							$(".treeBranches").each(function(){
								$(this).css("height",$(this).parent().outerHeight());
							});
						});
					})( jQuery );
				',array('inline'=>false));
				$this->stacks[$opt['key']] = array();
			}
			while(!empty($this->stacks[$opt['key']]) && $this->stacks[$opt['key']][0]['rght'] <= $data[$opt['model']]['lft']){
				array_shift($this->stacks[$opt['key']]);
			}
			$next = null;
			if(!empty($this->stacks[$opt['key']]) ){
				$ids = array_flip(Set::extract('{n}.'.$opt['model'].'.id',$opt['allData']));
				//debug($ids);
				$i = $ids[$data[$opt['model']]['id']];
				if(count($opt['allData']) > $i){
					$j = $i;
					do{
						$next = $opt['allData'][$j];
						$j++;
					}while(count($opt['allData']) > $j && $data[$opt['model']]['rght'] > $next[$opt['model']]['lft']);
					if($data[$opt['model']]['rght'] > $next[$opt['model']]['lft'] || $this->stacks[$opt['key']][0]['rght'] <= $next[$opt['model']]['lft']){
						$next = null;
					}
				}
			}
			$data[$opt['model']]['lastBranch'] = empty($next);
			array_unshift($this->stacks[$opt['key']],$data[$opt['model']]);
			$lvl = count($this->stacks[$opt['key']]);
			if($lvl > 1){
				$html .= '<span class="treeBranches">';
				$branches = '';
				for ($j = 0; $j < count($this->stacks[$opt['key']])-1; $j++) {
					$s = $this->stacks[$opt['key']][$j];
					if($j == 0){
						$branches = '<span class="'.($s['lastBranch']?'tip':'branch').'"></span>'.$branches;
					}else{
						$branches = '<span class="'.($s['lastBranch']?'empty':'line').'"></span>'.$branches;
					}
				}
				$html .= $branches;
				/*if($lvl > 2){
					$html .=  str_repeat('<span class="line"></span>',$lvl-2);
				}
				if($lvl > 1){
					$html .=  '<span class="'.(empty($next)?'tip':'branch').'"></span>';
				}*/
				$html .= '</span>';
			}
			return $html;
		}
	}
	
	function parseOpt($options){
		$defOpt = array(
			'active' => true,
			'model' => null,
			'key' => null,
			'columnClass' => 'treeDisplayColumn',
			'ancestors' => array(),
			'data' => array(),
		);
		if(empty($options['key'])){
			if(empty($options['model'])){
				$view =& ClassRegistry::getObject('view');
				if(!empty($this->params['treeDisplay'])){
					$options['key'] = key($this->params['treeDisplay']);
				}elseif(!empty($view->model)){
					$options['key'] = $view->model;
				}else{
					$options['key'] = Inflector::classify($this->params['controller']);
				}
			}else{
				$options['key'] = $options['model'];
			}
		}
		if(!empty($this->params['treeDisplay'][$options['key']])){
			$options = array_merge($this->params['treeDisplay'][$options['key']],$options);
		}
		if(empty($options['model'])){
			$options['model'] = $options['key'];
		}
		$opt = array_merge($defOpt,$options);
		if(empty($options['data']) && !empty($this->data[$opt['key']]) ){
			$opt['data'] = $this->data[$opt['key']];
		}
		$opt['allData'] = array_merge($opt['ancestors'],$opt['data']);
		return $opt;
	}
}
