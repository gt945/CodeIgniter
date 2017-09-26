// 默认的代码是一个从 xui.Module 派生来的类
Class('App.Switch', 'xui.Module',{
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
				.setCaption("切换用户")
				.setMovable(false)
				.setMinBtn(false)
				.setMaxBtn(false)
			);

			host.dialog.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"user")
				.setDataBinder("databinder")
				.setDataField("user")
				.setShowDirtyMark(false)
				.setLeft(35)
				.setTop(30)
				.setWidth(200)
				.setLabelSize(80)
				.setLabelCaption("用户")
				.setType("cmdbox")
				.beforeComboPop("_cmdbox_beforecombopop")
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
		iniResource: function(com, threadid){
			var ns=this;
			xui.Thread.suspend(threadid);
			var callback=function(){
				xui.Thread.resume(threadid);
			};
			AJAX.callService('system/request',null,"get_userlist",null,
			function(rsp){
			    var items=[];
			    _.arr.each(rsp.data,function(d){
			       items.push({id:d.id,caption:d.username});
			    });
			    ns.user.setItems(items);
			},function(){
			},function(){
			    callback();
			});
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this,db=ns.databinder;
		},
		_close_click:function(){
			this.dialog.close();
		},
		_cmdbox_beforecombopop:function(profile,pos,e,src){
			var ns=this,ctrl=profile.boxing();
			xui.ModuleFactory.newCom("App.UserGroupSelect", function(){
				if (!_.isEmpty(this)){
					this.setProperties({
						field:"gid",
						pos:ctrl.getRoot(),
						value:ctrl.getUIValue(),
						mode:'single',
						type:'user'
					});
					this.setEvents({
						onCancel:function(){
							if(!ctrl.isDestroyed()){
								ctrl.activate();
							}
						},
						onSelect:function(val,extra){
							if(!ctrl.isDestroyed()){
								ctrl.setUIValue(val.value);
								if(typeof(val.caption)==="string"){
									ctrl.setCaption(val.caption);
								}
								ctrl.activate();
								if(extra && _.isArr(extra)){
									_.arr.each(extra,function(exval){
										var setting=ns.properties.gridSetting;
										var ele=db.getUI(exval.id);
										if(ele){
											ele.setUIValue(exval.cell.value);
											if(typeof(exval.cell.caption)==="string"){
												ele.setCaption(exval.cell.caption);
											}
										}else{
											LOG.error(exval.id,1,2);
										}
									});
								}
							}
						}
					});
					this.show();
				}

			});
		},
		_save_onclick:function(){
			var ns=this;
			var db=ns.databinder;
			db.updateDataFromUI(true);
			var data=db.getData();
			AJAX.callService('system/request', null, "user_switch_to", data, function(rsp){
				location.reload();
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