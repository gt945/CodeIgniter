// 默认的代码是一个从 xui.Com 派生来的类
Class('App.GridExporter', 'xui.Com',{
	autoDestroy : true,
	// 要确保键值对的值不能包含外部引用
	Instance:{
		// 本Com是否随着第一个控件的销毁而销毁
		autoDestroy : true,
		// 初始化属性
		properties : {},
		// 实例的属性要在此函数中初始化，不要直接放在Instance下
		initialize : function(){
			var ns=this;
			ns._raw=0;
			ns._key=0;
		},
		// 初始化内部控件（通过界面编辑器生成的代码，大部分是界面控件）
		// *** 如果您不是非常熟悉XUI框架，请慎重手工改变本函数的代码 ***
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append((new xui.UI.Dialog())
				.setHost(host,"dialog")
				.setLeft(180)
				.setTop(120)
				.setWidth(300)
				.setHeight(350)
				.setCaption("导出数据")
				.setResizer(false)
				.setMinBtn(false)
				.setMaxBtn(false)
				);
			host.dialog.append((new xui.UI.Block())
				.setHost(host,"ctl_block8")
				.setDock("top")
				.setHeight(30)
				);
			host.ctl_block8.append((new xui.UI.ToolBar())
				.setHost(host,"toolbar")
				.setHandler(false)
//                .setDock("fill")
				.setItems([{
					"id" : "grp1",
					"sub" : [{
						"id" : "key",
						"image" : "@xui_ini.appPath@image/key.png",
						"caption" : "包含主键",
						"type" : "statusButton"
					},{
						"id" : "raw",
						"image" : "@xui_ini.appPath@image/raw.png",
						"caption" : "原始数据",
						"type" : "statusButton"
					}],
					"caption" : "grp1"
				}])
				.onClick("_toolbar_onclick")
				);
			host.dialog.append((new xui.UI.TreeGrid())
				.setHost(host,"grid")
				.setRowNumbered(false)
				.setRowHandler(false)
				.setShowDirtyMark(false)
				.setHeader([{
					"id" : "field",
					"caption" : "字段",
					"relWidth" : true,
					"type" : "label"
				},{
					"id" : "dump",
					"caption" : "导出",
					"relWidth" : true,
					"type" : "checkbox",
					"editable" : true,
					"editMode" : "inline"
				}])
			);
			
			host.dialog.append((new xui.UI.Block())
				.setHost(host,"ctl_block")
				.setDock("bottom")
				.setHeight(40)
			);
			
			host.ctl_block.append((new xui.UI.SButton())
				.setHost(host)
				.setTop(10)
				.setWidth(80)
				.setLeft(50)
				.setCaption("导出")
				.onClick("_export_onclick")
			);
			host.ctl_block.append((new xui.UI.SButton())
				.setHost(host)
				.setTop(10)
				.setWidth(80)
				.setRight(50)
				.setCaption("关闭")
				.onClick("_close_click")
			);
	
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		events:{"onRender":"_com_onrender"},
		// 可以自定义哪些界面控件将会被加到父容器中
		customAppend : function(parent, subId, left, top){
			this.dialog.showModal(parent, left, top);
			return true;
		},
		_com_onrender:function(com,threadid){
			var ns=this,grid=ns.grid;
			var rows=[];
			for(i in ns.properties.gridSetting){
				rows.push({cells:[{value:i,caption:ns.properties.gridSetting[i].caption[0]},{value:1}]});
			}
			grid.setRows(rows);
		},
		_toolbar_onclick:function (profile, item, group, e, src){
			var ns = this,ctrl=profile.boxing(),row;
			switch(item.id){
			case "key":
				if (item.value){
					ns._key=1;
				}else{
					ns._key=0;
				}
				break;
			case "raw":
				if (item.value){
					ns._raw=1;
				}else{
					ns._raw=0;
				}
				break;
			}
		},
		_close_click:function(){
			this.dialog.close();
		},
		_export_onclick:function(){
			var ns=this,grid=ns.grid;
			var paras={
				setting:grid.getRows('min'),
				filters:ns.properties._filter,
				search:ns.properties._search,
				key:ns._key,
				raw:ns._raw,
				sidx:ns.properties._sidx,
				sord:ns.properties._sord
			};
			
			xui.IAjax.post(SITEURL+'data/export',{key:ns.properties.gridName,paras:paras});
		}
	}
});
