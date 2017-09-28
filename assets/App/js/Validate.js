// 默认的代码是一个从 xui.Module 派生来的类
Class('App.Validate', 'xui.Module',{
	autoDestroy : true,
	// 要确保键值对的值不能包含外部引用
	Instance:{
		// 本Com是否随着第一个控件的销毁而销毁
		autoDestroy : true,
		// 初始化属性
		properties : {},
		// 实例的属性要在此函数中初始化，不要直接放在Instance下
		initialize : function(){},
		// 初始化内部控件（通过界面编辑器生成的代码，大部分是界面控件）
		// *** 如果您不是非常熟悉XUI框架，请慎重手工改变本函数的代码 ***
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append(
				xui.create("xui.UI.Dialog")
				.setHost(host,"dialog")
				.setLeft(180)
				.setTop(120)
				.setHeight(350)
				.setResizer(false)
				.setCaption("检查数据")
				.setMinBtn(false)
				.setMaxBtn(false)
			);
			
			host.dialog.append(
				xui.create("xui.UI.TreeGrid")
				.setHost(host,"grid")
				.setShowDirtyMark(false)
				.setRowHandler(true)
				.setTreeMode(false)
				.setRowHandlerWidth(80)
				.setSelMode("multi")
				.setHeader([{
					"id" : "field",
					"caption" : "字段",
					"type" : "label",
					"width" : 80,
					"flexSize" : true
				}])
				);
			
			host.dialog.append(
				xui.create("xui.UI.Block")
				.setHost(host,"ctl_block")
				.setDock("bottom")
				.setHeight(40)
				.setBorderType("none")
				);
			
			host.ctl_block.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"xui_ui_sbutton16")
				.setLeft(50)
				.setTop(10)
				.setWidth(80)
				.setCaption("检查")
				.onClick("_validate_onclick")
				);
			
			host.ctl_block.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"xui_ui_sbutton17")
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
				rows.push({cells:[{value:i,caption:ns.properties.gridSetting[i].caption[0]}]});
			}
			grid.setRows(rows);
		},
		_close_click:function(){
			this.dialog.close();
		},
		_validate_onclick:function(){
			var ns=this,grid=ns.grid;
			var setting=[];
			var ids=grid.getUIValue(true);
			_.arr.each(ids,function(id){
				var cell=grid.getCellbyRowCol(id, 0, 'min');
				setting.push([cell,1]);
			});
			var paras={
				setting:setting,
				filters:ns.properties._filter,
				search:ns.properties._search,
				sidx:ns.properties._sidx,
				sord:ns.properties._sord,
				sub:ns.properties._sub
			};
			
			AJAX.callService('xui/request',ns.properties.gridId,"data_validate",paras,function(rsp){
				xui.ModuleFactory.newCom("App.TextResult",function(){
					if(!_.isEmpty(this)){
						this.show();
					}
				},null,{result:rsp.data});
			},function(){
				ns.dialog.busy("正在处理 ...");
			},function(result){
				ns.dialog.free();
			});
		}
	}
});
