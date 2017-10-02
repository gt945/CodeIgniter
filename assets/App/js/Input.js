// 默认的代码是一个从 xui.Module 派生来的类
Class('App.Input', 'xui.Module',{
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
				.setLeft(230)
				.setTop(170)
				.setHeight(190)
				.setResizer(false)
				.setCaption("输入")
				.setMinBtn(false)
				.setMaxBtn(false)
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
				.setHost(host,"btn1")
				.setLeft(50)
				.setTop(10)
				.setWidth(80)
				.setCaption("确定")
				.onClick("_btn1_onclick")
				);
			
			host.ctl_block.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"btn2")
				.setTop(10)
				.setWidth(80)
				.setRight(50)
				.setCaption("取消")
				.onClick("_btn2_click")
				);
			
			host.dialog.append(
				xui.create("xui.UI.Input")
				.setHost(host,"input")
				.setDefaultFocus(true)
				.setLeft(40)
				.setTop(25)
				.setWidth(220)
				);
			
			host.dialog.append(
				xui.create("xui.UI.Label")
				.setHost(host,"label")
				.setLeft(16)
				.setTop(65)
				.setWidth(260)
				.setHeight(40)
				.setCaption("")
				.setHAlign("left")
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
			var ns=this;
			if(ns.properties.caption){
				ns.dialog.setCaption(ns.properties.caption);
			}
			if(ns.properties.btn1){
				ns.btn1.setCaption(ns.properties.btn1);
			}
			if(ns.properties.btn2){
				ns.btn2.setCaption(ns.properties.btn2);
			}
			if(ns.properties.label){
				ns.label.setCaption(ns.properties.label);
			}
		},
		_btn1_onclick:function(){
			var ns=this;
			ns.fireEvent("onSelect",[ns.input.getUIValue()]);
			this.dialog.close();
		},
		_btn2_click:function(){
			var ns=this;
			ns.fireEvent("onCancel");
			this.dialog.close();
		}
	}
});
