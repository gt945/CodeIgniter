Class('App.SendMessage', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		properties : {},
		initialize : function(){
		},
		iniComponents : function(){
			//[[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			append((new xui.DataBinder())
				.setHost(host,"databinder")
				.setName("databinder")
				.setData()
			);
			append(
				xui.create("xui.UI.Dialog")
				.setHost(host,"mainDlg")
				.setLeft(120)
				.setTop(100)
				.setWidth(640)
				.setHeight(480)
				.setCaption("发送消息")
				.setMinBtn(false)
			);
			
			host.mainDlg.append(
				xui.create("xui.UI.Block")
				.setHost(host,"xui_ui_block4")
				.setDock("top")
				.setHeight(43)
				.setBorderType("none")
				);
			
			host.xui_ui_block4.append(
				xui.create("xui.UI.ComboInput")
				.setHost(host,"xui_ui_comboinput25")
				.setDataBinder("databinder")
				.setDataField('receiver')
				.setDock("width")
				.setLeft(146)
				.setTop(15)
				.setWidth(470)
				.setLabelSize(70)
				.setLabelCaption("发送给:")
				.setType("cmdbox")
				.beforeComboPop("_cmdbox_beforecombopop")
				.setCustomStyle({
					"LABEL" : {
						"text-align" : "center"
					}
				})
				);
			
			//host.xui_ui_block4.append(
			//	xui.create("xui.UI.ComboInput")
			//	.setHost(host,"xui_ui_comboinput76")
			//	.setDock("width")
			//	.setLeft(16)
			//	.setTop(45)
			//	.setWidth(220)
			//	.setLabelSize(70)
			//	.setLabelCaption("标　题:")
			//	.setType("none")
			//	.setCustomStyle({
			//		"LABEL" : {
			//			"text-align" : "center"
			//		}
			//	})
			//	);
			
			host.mainDlg.append(
				xui.create("xui.UI.RichEditor")
				.setHost(host,"ctl_richeditor1")
				.setDock("fill")
				.setDataBinder("databinder")
				.setDataField('message')
				);
			
			host.mainDlg.append(
				xui.create("xui.UI.Block")
				.setHost(host,"xui_ui_block4")
				.setDock("bottom")
				.setHeight(35)
				.setBorderType("none")
				);
			
			host.xui_ui_block4.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"btnSave")
				.setLeft(220)
				.setTop(10)
				.setWidth(70)
				.setCaption("发送")
				.onClick("_ctl_sbutton14_onclick")
				);
			
			host.xui_ui_block4.append(
				xui.create("xui.UI.SButton")
				.setHost(host,"btnClose")
				.setLeft(350)
				.setTop(10)
				.setWidth(70)
				.setCaption("关闭")
				.onClick("_ctl_sbutton486_onclick")
				);
			
			return children;
			//]]Code created by CrossUI RAD Studio
		},
		customAppend : function(parent, subId, left, top){
			return false;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this;
		},
		_cmdbox_beforecombopop:function(profile,pos,e,src){
			var ns=this,ctrl=profile.boxing();
			xui.ModuleFactory.newCom("App.UserGroupSelect", function(){
				if (!_.isEmpty(this)){
					this.setProperties({
						field:"gid",
						pos:ctrl.getRoot(),
						value:ctrl.getUIValue(),
						mode:'multibycheckbox',
						type:'usergroup'
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
		_ctl_sbutton486_onclick:function(){
			var ns=this;
			ns.mainDlg.close(false);
		},
		_ctl_sbutton14_onclick:function(){
			var ns=this,db=ns.databinder;
			var data=db.getDirtied(true);
			if(!_.isSet(data.message)||!_.isSet(data.receiver)){
				xui.alert('数据不全');
			}else{
				var prop={
					receiver:data.receiver.value,
					message:data.message
				};
				AJAX.callService('message/request',null,"message_send",prop,function(rsp){
					
				},function(){
					ns.mainDlg.busy('发送中...');
				},function(result){
					if(ns.mainDlg){
						ns.mainDlg.free();
					}
					if(result!='fail'){
						xui.alert('发送成功');
						ns.destroy();
					}
				});
			}
			
		}
	}
});
