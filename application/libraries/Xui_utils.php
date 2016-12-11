<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Xui_utils {
	
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
	
	public function menus($menus, $role)
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
			
			if (strpos(",{$m['role_r']}," , ",{$role}," ) === false) {
				continue;
			}
			
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
				if (strpos(",{$c['role_r']}," , ",{$role}," ) === false) {
					continue;
				}
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
	
	function grid_toolbar_items($dbContext, $extra_items = array())
	{
// 		$obj = (object) array(
// 				"alias" => "toolbar",
// 				"key" =>"xui.UI.ToolBar",
// 				"properties" => (object) array(
// 						"items" => array(
// 								(object)array(
// 										"id" => "grp1",
// 										"sub" => array(),
// 										"caption" => "grp1"
// 								)
// 						)
// 				),
// 				"events" => (object) array(
// 						"onClick" => "_toolbar_onclick"
// 				)
// 		);
		$items =array(
				(object)array(
						"id" => "grp1",
						"sub" => array(),
						"caption" => "grp1"
				)
		);
		if ($dbContext->crud_table['_role_c']) {
			$items[0]->sub[] = (object) array(
					"id" => "new",
					"image" => "@xui_ini.appPath@image/new.png",
					"caption" => "增加"
			);
		}
		if ($dbContext->crud_table['_role_u']) {
			$items[0]->sub[] = (object) array(
					"id" => "edit",
					"image" => "@xui_ini.appPath@image/edit.png",
					"caption" => "修改",
					"disabled" => true
			);
		}
		if ($dbContext->crud_table['_role_d']) {
			$items[0]->sub[] = (object) array(
					"id" => "delete",
					"image" => "@xui_ini.appPath@image/delete.png",
					"caption" => "删除",
					"disabled" => true
			);
		}
		if ($dbContext->filter) {
			$items[0]->sub[] = (object) array(
					"id" => "filter",
					"image" => "@xui_ini.appPath@image/filter.png",
					"caption" => "搜索"
			);
		}
		if ($dbContext->group) {
			$items[0]->sub[] = (object) array(
					"id" => "group",
					"image" => "@xui_ini.appPath@image/group.png",
					"caption" => $_SESSION['groupinfo']['groupname'],
					"gid" => $_SESSION['groupinfo']['id'],
					"type" => "dropButton"
			);
			$items[0]->sub[] = (object) array(
					"id" => "sub",
					"image" => "@xui_ini.appPath@image/sub.png",
					"caption" => "显示子组数据",
					"type" => "statusButton"
			);
		}
		
		if ($dbContext->export) {
			$items[0]->sub[] = (object) array(
					"id" => "export",
					"image" => "@xui_ini.appPath@image/export.png",
					"caption" => "导出"
			);
		}
		
		if ($dbContext->import) {
			$items[0]->sub[] = (object) array(
					"id" => "import",
					"image" => "@xui_ini.appPath@image/export.png",
					"caption" => "导入"
			);
		}
		if (count($extra_items) > 0) {
			$items[1] = (object)array(
					"id" => "grp2",
					"sub" => array(),
					"caption" => "grp2"
				);
			foreach($extra_items as $k=>$item){
				$items[1]->sub[] = (object) array(
					"id" => "custom{$k}",
					"image" => "@xui_ini.appPath@image/{$item['icon']}",
					"caption" => "{$item['name']}",
					"app" => "{$item['app']}"
				);
			}
		}
		$json = json_encode($items);
		
		return $this->filter($json);
	}
	
	function build_tree($data, $caption)
	{
		$ret = (object) array(
			"id" => $data['id'],
			"caption" => $data[$caption],	
		);
		if (isset($data['children']) && count($data['children'])) {
			$ret->sub = array();
			foreach ($data['children'] as $c) {
				$ret->sub[] = $this->build_tree($c,$caption);
			}
		}
		
		return $ret;
	}
}
