// 默认的代码是一个从 xui.Module 派生来的类
Class('App.Setting', 'xui.Module',{
	autoDestroy : true,
	// 要确保键值对的值不能包含外部引用
	Instance:{
		// 本Com是否随着第一个控件的销毁而销毁
		autoDestroy : true,
		// 初始化属性
		properties : {},
		// 实例的属性要在此函数中初始化，不要直接放在Instance下
		initialize : function(){
		},
		// 初始化内部控件（通过界面编辑器生成的代码，大部分是界面控件）
		// *** 如果您不是非常熟悉XUI框架，请慎重手工改变本函数的代码 ***
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append(
				xui.create("xui.DataBinder")
				.setHost(host,"databinder")
				.setName("databinder")
			);
			
			append(
				xui.create("xui.UI.Dialog")
				.setHost(host,"dialog")
				.setLeft(180)
				.setTop(130)
				.setWidth(320)
				.setHeight(150)
				.setResizer(false)
				.setCaption("系统设置")
				.setMovable(false)
				.setMinBtn(false)
				.setMaxBtn(false)
			);
			
			var current_year=new Date().getFullYear();
			var items=[
				{id:0,caption:"全部"}
			];
			for(var i=2013;i<=current_year;i++){
				items.push(
					{id:i,caption:i}
				);
			}
			
			host.dialog.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"workyear")
				.setDataBinder("databinder")
				.setDataField("workyear")
				.setShowDirtyMark(false)
				.setLeft(35)
				.setTop(30)
				.setWidth(200)
				.setLabelSize(80)
				.setLabelCaption("当前工作年")
				.setType("listbox")
				.setItems(items)
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "center"
					}
				})
			);
			
			host.dialog.append((new xui.UI.Block())
				.setHost(host,"xui_ui_block4")
				.setHeight(40)
				.setDock("bottom")
				.setBorderType("none")
			);
			
			host.xui_ui_block4.append(
				xui.create("xui.UI.Button")
				.setHost(host,"save")
				.setLeft(50)
				.setTop(10)
				.setWidth(100)
				.setCaption("确定")
				.onClick("_save_onclick")
			);
			
			host.xui_ui_block4.append(
				xui.create("xui.UI.Button")
				.setHost(host,"close")
				.setLeft(170)
				.setTop(10)
				.setWidth(100)
				.setCaption("关闭")
				.onClick("_close_click")
			);
			
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		// 可以自定义哪些界面控件将会被加到父容器中
		customAppend : function(parent, subId, left, top){
			this.dialog.showModal(parent, left, top);
			return true;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this,db=ns.databinder;
			AJAX.callService('system/request', null, "setting_get", null, function(rsp){
				db.setData(rsp.data);
				db.updateDataToUI();
			},function(){
				ns.dialog.busy();
			},function(){
				ns.dialog.free();
			});
		},
		_close_click:function(){
			this.dialog.close();
		},
		_save_onclick:function(){
			var ns=this;
			var db=ns.databinder;
			db.updateDataFromUI(true);
			var data=db.getData();
			AJAX.callService('system/request', null, "setting_set", data, function(rsp){
			},function(){
				ns.dialog.busy();
			},function(result){
				ns.dialog.free();
				if(result!='fail'){
					ns.dialog.close();
				}
			});
		}
	}
});