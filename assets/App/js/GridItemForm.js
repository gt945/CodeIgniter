Class('App.GridItemForm', 'xui.Module',{
	autoDestroy : true,
	Instance:{
		initialize : function(){
			var ns=this;
			ns._dataFilter=null;
		},
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			append((new xui.DataBinder())
				.setHost(host,"databinder")
				.setName("databinder")
				.afterUpdateDataFromUI("_databinder_afterupdatedatafromui")
				.setData()
			);
			append((new xui.UI.Dialog())
				.setHost(host,"mainDlg")
				.setLeft(25)
				.setTop(19)
				.setWidth(host._width(host.getProperties("gridFormWidth"))+50)
				.setHeight(host._height(host.getProperties("gridFormHeight"))+100)
				.setResizer(true)
				.setResizerProp({vertical:true,horizontal:false,minHeight:100})
				.setOverflow("hidden")
				.setCaption("编辑")
				.setMinBtn(false)
				.setMaxBtn(false)
				// 				.onHotKeydown("_maindlg_onhotkeydown")
				.beforeClose("_maindlg_beforeclose")
			);
			host.mainDlg.append((new xui.UI.Block())
				.setHost(host,"ctl_block")
				.setLeft(5)
				.setTop(0)
				.setWidth(host._width(host.getProperties("gridFormWidth"))+35)
				.setHeight(host._height(host.getProperties("gridFormHeight"))+30)
				.setBorderType("inset")
				.setDock("fill")
				.setOverflow("overflow-x:hidden;overflow-y:auto")
			);
			var setting=host.properties.gridSetting;
			var groups=host.properties.gridFormGroups;
			_.each(groups,function (g) {
				var ele=(new xui.UI.Group())
					.setHost(host)
					.setCaption(g.name)
					.setLeft(host._left(g.x) - 10)
					.setTop(host._top(g.y) - 30)
					.setWidth(host._width(g.w) + 20)
					.setHeight(host._height(g.h) + 35)
					.setToggleBtn(false);
				host.ctl_block.append(ele);
			});
			var index=0;
			var data={};
			var row=host.properties.editor.grid.getActiveRow();
			for(var f in setting){
				var dataField=f;
				if(setting[f].form) {
					var ele=_.unserialize(setting[f].form);
					if(setting[f].template){
						ele.setProperties('initialValue',setting[f].template);
					}
					if(f==host.properties.item.field&&row){
						var cell=host.properties.editor.grid.getCellbyRowCol(row.id,host.properties.item.field2);
						if(cell){
							ele.setProperties('initialValue',{value:row.id,caption:cell.value});
						}
					}
					if(setting[f].mask){
						ele.setMask(setting[f].mask);
					}
					if(setting[f].format){
						ele.setValueFormat(setting[f].format);
					}
					if(setting[f].currency){
						ele.setCurrencyTpl(setting[f].currency);
					}
					ele.setProperties('setting',setting[f]);
					host.ctl_block.append(ele
						.setHost(host,"form_input_"+index)
						.setDataBinder("databinder")
						.setDataField(dataField)
						.setLeft(host._left(setting[f].x)+5)
						.setTop(host._top(setting[f].y))
						.setWidth(host._width(setting[f].w)-25)
						.setHeight(host._height(setting[f].h))
						.setTabindex(index+1)
						.onChange("_form_onchange")
					);
				}
				index++;
			}
			host.databinder.setData(data);

			host.mainDlg.append((new xui.UI.Block())
				.setHost(host,"xui_ui_block4")
				.setHeight(35)
				.setDock("bottom")
				.setBorderType("none")
			);

			host.xui_ui_block4.append((new xui.UI.SButton())
				.setHost(host,"btnSave")
				.setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 - 100)
				.setTop(5)
				.setWidth(70)
				.setCaption("保存")
				.setTabindex(++index)
				.onClick("_ctl_sbutton14_onclick")
			);

			host.xui_ui_block4.append((new xui.UI.SButton())
				.setHost(host,"btnClose")
				.setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 + 30)
				.setTop(5)
				.setWidth(70)
				.setCaption("关闭")
				.setTabindex(++index)
				.onClick("_ctl_sbutton486_onclick")
			);

			return children;
		},
		customAppend : function(parent, subId, left, top){
			this.mainDlg.showModal(parent, left, top);
			return true;
		},
		iniExComs : function(com, threadid){
			var ns=this;
		},
		_width:function(v){
			v=parseInt(v,10);
			return (v+1)*150 + v*30;
		},
		_height:function(v){
			v=parseInt(v,10);
			return (v+1)*24+v*6;
		},
		_left:function(v){
			v=parseInt(v,10);
			return 15+v*180;
		},
		_top:function(v){
			v=parseInt(v,10);
			return v*30+15;
		},
		events:{"onRender":"_com_onrender", "beforeCreated":"_beforeCreated"},
		_beforeCreated:function(com, threadid){
			var ns=this;
			xui.Thread.suspend(threadid);
			var callback=function(/**/){
				xui.Thread.resume(threadid);
			};
			AJAX.callService(ns.properties.item.uri,null, ns.properties.item.target, null, function (rsp) {
				ns.setProperties(rsp.data);
			}, function(){
			}, function(){
				callback();
			});
		},
		_com_onrender:function (com, threadid){
			var ns=this;
			this.databinder.updateDataToUI();
			var prfs=this.databinder.get(0)._n;
			_.arr.each(prfs,function(prf){
				if(_.isSet(prf.properties.initialValue)){
					var b=prf.boxing();
					var v=prf.properties.initialValue;
					if(_.isSet(v.value)){
						b.setUIValue(v.value);
					}
					if(_.isSet(v.caption)){
						b.setCaption(v.caption);
					}
				}
			});
			this.setDirty(false);
		},
		setDirty:function(dirty){
			this.__dirty=dirty;
		},
		isGridDirty:function(){
			var ns=this,dirty=false;
			return dirty||(!!this.__dirty);
		},
		_ctl_sbutton14_onclick:function (profile,e,src,value){
			var ns=this;
			this.saveUI(function(o,s){
				if (s==1){
					o.close(false);
				}
			});
		},
		saveUI:function(callback){
			var ns=this, db=ns.databinder;
			if(db.isDirtied() || ns.isGridDirty()){

				if(!ns._checkValid()){
					xui.message("输入格式错误!");
					return;
				}
				var hash=db.getDirtied(),
					hashPair=db.getDirtied(true);

				if (hash && !_.isEmpty(hash)) {
					AJAX.callService('xui/request',ns.properties.gridId,"create",{
						fields:hash
					},function(rsp){
						if(rsp.data){
							var row=ns.properties.editor.grid.getActiveRow();
							if (row){
								ns.fireEvent("refreshRow", [row.id]);
							}
							db.updateValue();
							ns.setDirty(false);
							xui.message("保存成功!");
							if(callback)callback(ns.mainDlg,1);
						}else{
							xui.message(rsp);
						}
					},function(){
						ns.mainDlg.busy("正在处理 ...");
					},function(){
						if(ns.mainDlg)
							ns.mainDlg.free();
					});
				}else{
					xui.message("未修改");
				}


			}else{
				xui.message("未修改");
			}

		},
		updateUIfromService:function(recordId){
			var ns=this,db=ns.databinder,data=db.getData();
			AJAX.callService('xui/request',ns.properties.gridId,"get",{id:recordId},function(rsp){
				var cells=rsp.data.rows[0].cells,
					settings=ns.properties.gridSetting;
				var i=0;
				_.each(settings, function(s,n){
					if(!s.object&&!s.virtual){
						if(s.type=="checkbox"){
							cells[i].value=!!parseInt(cells[i].value,10);
						}
						data[n]=cells[i];
						i++;
					}
				});
				db.setData(data).updateDataToUI();
				_.asyRun(function(){
					ns.btnClose.activate();
				});

			},function(){
				ns.mainDlg.busy("正在处理 ...");
			},function(result){
				ns.mainDlg.free();
				if (result=="fail"){
					ns.mainDlg.close(false);
				}
			});
		},
		_databinder_afterupdatedatafromui:function (profile, dataFromUI){
			// _.each(dataFromUI,function(o,i){
			// 	if(_.isDate(o)){
			//        dataFromUI[i]=xui.Date.format(o, "yyyy-mm-dd hh:nn:ss");
			// 	}
			// });
		},
		_form_onchange:function(profile, oldValue, newValue, force, tag){
			var ns=this,db=ns.databinder;
			if(this._dataFilter&&!force){
				this._dataFilter.autoComplete(db);
			}
		},
		_ctl_sbutton486_onclick:function (profile, e, src, value){
			this.mainDlg.close(true);
		},
		_maindlg_beforeclose:function (profile){
			var ns=this, db=ns.databinder;
			if(db.isDirtied() || ns.isGridDirty()){
				xui.confirm("确认", "是否放弃修改?", function(){
					ns.mainDlg.close(false);
				}, null);
				return false;
			}else{
				return true;
			}
		},
// 		_maindlg_onhotkeydown:function (profile, keyboard, e, src){
// 			if(keyboard.key=="esc"){
// 				this.mainDlg.close(true);
// 			}
// 		},
		_get_relate:function(relate){
			var ns=this,db=ns.databinder;
			var data={};
			if(relate){
				var relate=relate.split(',');
				if(_.isArr(relate)&&relate.length){
					_.arr.each(relate,function(key){
						var fields=key.split(':');
						if(fields.length){
							var values=db.getUIValue();
							if(values[fields[0]])
								data[fields[0]]=values[fields[0]];
						}

					});
				}
			}
			return data;
		},
		_select_beforepopshow:function(profile, popCtl){
			var ns = this, elem = popCtl.boxing();
			var para = {field:profile.boxing().getDataField()};
			para['relate']=ns._get_relate(profile.properties.setting.relate);
			if(!elem._isset){
				AJAX.callService('xui/request',ns.properties.gridId,"get_select",para,
					function(rsp){
						if(!elem.isDestroyed()){
							profile.boxing().setItems(rsp.data);
							elem.setItems(rsp.data).setValue(null,true);
							elem._isset=1;
						}
					},function(){
						elem.setItems(["加载中 ..."],true);
					},function(){
					});
			}

		},
		_select_beforecombopop:function (profile, pos,e ,src){
			var ns=this,ctrl=profile.boxing();
			var setting=ns.properties.gridSetting;
			var db=ns.databinder;
			xui.ModuleFactory.newCom(ctrl.getProperties("app"), function(){
				this.setProperties({
					key:ns.properties.gridId,
					field:ctrl.getDataField(),
					pos:ctrl.getRoot(),
					cmd:ctrl.getProperties("cmd"),
					value:ctrl.getUIValue(),
					setting:setting[ctrl.getDataField()],
					relate:ns._get_relate(profile.properties.setting.relate)
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
			});
			return false;
		},
		_lock_onchange:function(profile,oldValue,newValue,force,tag){
			var ns = this, uictrl = profile.boxing();
			var db=ns.databinder;
			var ele=db.getUI(profile.properties.name);
			ele.setDisabled(!newValue);
			if(newValue){
				uictrl.setImage("@xui_ini.appPath@image/unlock.png");
				ns.setDirty(true);
			}else{
				uictrl.setImage("@xui_ini.appPath@image/lock.png");
				ele.updateValue().refresh();
			}
		},
		_checkValid:function(){
			var ns=this,db=ns.databinder;
			var valid=true;
			if(!db.checkValid()){
				return false;
			}
			return valid;
		}

	}
});
