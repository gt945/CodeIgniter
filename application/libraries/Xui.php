<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xui {
	
public function filter($json)
	{
		$search = array(
				"\"%",
				"%\""
		);
		$replace = array(
				"",
				""
		);
		$json = str_replace($search, $replace, $json);
		return $json;
	}
	
	public function menus($menus)
	{
		$obj = (object) array(
				"alias" => "menus",
				"key" =>"xui.UI.Stacks",
				"host" => "%SPA%",
				"properties" => (object) array(
						"value" => null,
						"items" => array()
				),
				"children" => array()
		);
		foreach ($menus as $m) {
			$item_m = (object) array(
					"id" => "m{$m['id']}",
					"caption" => "{$m['name']}"
			);
			if (!$obj->properties->value) {
				$obj->properties->value = "m{$m['id']}";
			}
			$obj->properties->items[] = $item_m;
				
			$child = array();
			$obj_c = (object) array(
					"alias" => "sub_menus{$m['id']}",
					"key" => "xui.UI.TreeView",
					"host" => "%SPA%",
					"properties" => (object) array(
							"items" => array()
					),
					"events" => (object) array(
							"onItemSelected" => "_menus_selected"
					),
			);
			foreach($m['children'] as $c) {
				$item_c = (object) array(
						"id" => "c{$c['id']}",
						"caption" => "{$c['name']}",
						"target" => "{$c['target']}",
						"app" => "{$c['app']}"
				);
				$obj_c->properties->items[] = $item_c;
			}
				
			$child[0] = $obj_c;
			$child[1] = "m{$m['id']}";
			$obj->children[] = $child;
		}
		$json = json_encode($obj);
		
		return $this->filter(" new xui.UI.Stacks({$json})");
	}
	public function form($dbContext)
	{
			/*
		 * $obj = ( object ) array (
		 *
		 * "alias" => "mainDlg",
		 * "key" => "xui.UI.Dialog",
		 * "properties" => ( object ) array (
		 * "left" => 25,
		 * "top" => 19,
		 * "width" => 690,
		 * "height" => 457,
		 * "resizer" => false,
		 * "caption" => "Order",
		 * "imagePos" => "left top",
		 * "minBtn" => false,
		 * "maxBtn" => false,
		 * "overflow" => "hidden"
		 * ),
		 * "events" => ( object ) array (
		 * "onHotKeydown" => "_maindlg_onhotkeydown",
		 * "beforeClose" => "_maindlg_beforeclose"
		 * ),
		 * "children" => array (
		 * array (
		 * ( object ) array (
		 * "alias" => "ctl_block",
		 * "key" => "xui.UI.Block",
		 * "properties" => ( object ) array (
		 * "left" => 5,
		 * "top" => 0,
		 * "width" => 675,
		 * "height" => 390,
		 * "borderType" => "inset",
		 * "overflow" => "visible"
		 * ),
		 * "children" => array ()
		 * )
		 * ),
		 * array (
		 * ( object ) array (
		 * "alias" => "btnSave",
		 * "key" => "xui.UI.SButton",
		 * "properties" => ( object ) array (
		 * "left" => 170,
		 * "top" => 400,
		 * "width" => 70,
		 * "tabindex" => 13,
		 * "caption" => "Save"
		 * ),
		 * "events" => ( object ) array (
		 * "onClick" => "_ctl_sbutton14_onclick"
		 * )
		 * )
		 * ),
		 * array (
		 * ( object ) array (
		 * "alias" => "btnClose",
		 * "key" => "xui.UI.SButton",
		 * "properties" => ( object ) array (
		 * "left" => 450,
		 * "top" => 400,
		 * "width" => 70,
		 * "tabindex" => 14,
		 * "caption" => "Close"
		 * ),
		 * "events" => ( object ) array (
		 * "onClick" => "_ctl_sbutton486_onclick"
		 * )
		 * )
		 * )
		 * )
		 * );
		 */
		$obj = ( object ) array (
				"alias" => "btnClose",
				"key" => "xui.UI.SButton",
				"properties" => ( object ) array (
						"left" => 450,
						"top" => 400,
						"width" => 70,
						"tabindex" => 14,
						"caption" => "Close" 
				),
				"events" => ( object ) array (
						"onClick" => "_ctl_sbutton486_onclick" 
				) 
		);
		$json = json_encode($obj);
		
		return $this->filter(" xui.UI.SButton({$json})");
	}
	
}
