Class('App.GridForm', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		initialize : function(){
			var ns=this;
			ns._dataFilter=null;
			ns._widgets={};
			ns._waitWidget=0;
		},
		iniComponents : function(com, threadid){
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
				.setBorderType("inset")
				.setDock("fill")
				.setOverflow("overflow-x:hidden;overflow-y:auto")
				);
			var setting=host.properties.gridSetting;
			var recordIds=host.properties.recordIds;
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
			xui.Thread.suspend(threadid);
			var callback=function(){
				widgets--;
				if(widgets<0){
					xui.Thread.resume(threadid);
				}
			};
			var widgets=0;
			for(var f in setting){
				var dataField=f;
				if(setting[f].form) {
					var ele=_.unserialize(setting[f].form);
					if(f==host.getProperties("gridGroup")&&!_.isSet(host.properties.recordId)){
						ele.setProperties('initialValue',host.properties._gid);
					}else if(f==host.getProperties("gridTreeMode")){
						ele.setProperties('initialValue',host.properties._pid);
					}else if(setting[f].template&&recordIds.length==0){
						ele.setProperties('initialValue',setting[f].template);
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
					if(recordIds.length>1) {
						ele.setDisabled(true);
						host.ctl_block.append(xui.create("xui.UI.Button")
							.setHost(host,"lock_form_input_"+index)
							.setLeft(host._left(setting[f].x)+host._width(setting[f].w)-18)
							.setTop(host._top(setting[f].y)+1)
							.setWidth(20)
							.setCaption("")
							.setShowDirtyMark(false)
							.setImage("@xui_ini.appPath@image/lock.png")
							.onChange("_lock_onchange")
							.setCustomStyle({
								"TDL" : {
									"display" : "none"
								},
								"TDR" : {
									"display" : "none"
								}
							})
							.setProperties("name", f)
							.setType("status")
						);
					}
				} else {
					widgets++;
					var pane=xui.create("xui.UI.Pane");
					host.ctl_block.append(pane
						.setHost(host,"form_pane_"+index)
						.setLeft(host._left(setting[f].x))
						.setTop(host._top(setting[f].y)-5)
						.setWidth(host._width(setting[f].w))
						.setHeight(host._height(setting[f].h)+5)
					);
					if(recordIds.length>1) {
						pane.setDisabled(true);
					} else {
						xui.ModuleFactory.newCom(setting[f].app,function(){
							if(!_.isEmpty(this)){
								host._widgets['widgets_'+this.properties.index]=this;
								callback();
							}
						},null,{
							parentId:host.properties.gridId,
							field:f,
							filter:{},
							pane:pane,
							dialog:host.mainDlg,
							recordIds:recordIds,
							index:index,
							setting:setting[f]
						},{
							onWidgetReady:function(ele){
								_.tryF(ele._load,null,ele);
							}
						});
					}
				}
				index++;
			}
			callback();

			host.mainDlg.append((new xui.UI.Block())
				.setHost(host,"xui_ui_block4")
				.setHeight(40)
				.setDock("bottom")
				.setBorderType("none")
			);

			if (recordIds.length==1){
				host.xui_ui_block4.append((new xui.UI.SButton())
					.setHost(host,"btnPrev")
					.setLeft(10)
					.setTop(10)
					.setWidth(20)
					.setCaption("<")
					.setTabindex(++index)
					.onClick("_ctl_btnprev_onclick")
				);
				host.xui_ui_block4.append((new xui.UI.SButton())
					.setHost(host,"btnNext")
					.setLeft(35)
					.setTop(10)
					.setWidth(20)
					.setCaption(">")
					.setTabindex(++index)
					.onClick("_ctl_btnnext_onclick")
				);
				host.xui_ui_block4.append((new xui.UI.SButton())
					.setHost(host,"btnCopy")
					.setLeft(60)
					.setTop(10)
					.setWidth(20)
					.setCaption("C")
					.setTabindex(++index)
					.onClick("_ctl_btncopy_onclick")
				);

			}

			host.xui_ui_block4.append((new xui.UI.SButton())
				.setHost(host,"btnSave")
				.setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 - 80)
				.setTop(10)
				.setWidth(70)
				.setCaption("保存")
				.setTabindex(++index)
				.onClick("_ctl_sbutton14_onclick")
				);

			host.xui_ui_block4.append((new xui.UI.SButton())
				.setHost(host,"btnClose")
				.setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 + 10)
				.setTop(10)
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
			if (ns.properties.dataFilter) {
				xui.Thread.suspend(threadid);
				var callback=function(/**/){
					xui.Thread.resume(threadid);
				};
				xui.ModuleFactory.newCom(ns.properties.dataFilter,function(){
					ns._dataFilter=this;
					callback();
				},null,null,null);
			}

		},
		_width:function(v){
			v=parseInt(v,10);
			return (v+1)*150+v*15;
		},
		_height:function(v){
			v=parseInt(v,10);
			return (v+1)*24+v*6;
		},
		_left:function(v){
			v=parseInt(v,10);
			return 15+v*165;
		},
		_top:function(v){
			v=parseInt(v,10);
			return v*30+15;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this;
			var recordIds=this.properties.recordIds;
			var db=ns.databinder;
			_.each(ns._widgets,function(w){
				w.show(null,w.properties.pane);
			});
			db.updateDataToUI();
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
			if(_.isSet(recordIds)&&recordIds.length>0){
				if (ns.properties.activeId){
					ns.updateUIfromService(ns.properties.activeId);
				}else{
					ns.updateUIfromService(recordIds[0]);
				}

			}else if(ns._dataFilter){
				_.tryF(ns._dataFilter.autoComplete,[db]);
			}
			this.setDirty(false);
		},
		setDirty:function(dirty){
			this.__dirty=dirty;
		},
		isGridDirty:function(){
			var ns=this,dirty=false;
			_.each(ns._widgets,function(ele){
				if(_.tryF(ele._isDirty,null,ele,false)){
					dirty=true;
					return false;
				}
			});
			return dirty||(!!this.__dirty);
		},
		_ctl_sbutton14_onclick:function (profile,e,src,value){
			var ns=this;
			xui.confirm("确认", "确定修改?", function(){
				ns.saveUI(function(o,s){
					ns._waitWidget--;
					if (s==1&&ns._waitWidget<0){
						o.close(false);
					}
				});
			}, null);
		},
		saveUI:function(callback){
			var ns=this, db=ns.databinder;
			ns._waitWidget=0;
			var saveDone=function(error){
				if(!error){
					callback(ns.mainDlg, 1);
				}
			}
			if(db.isDirtied() || ns.isGridDirty()){

				if(!ns._checkValid()){
					xui.message("输入格式错误!");
					return;
				}
				var recordIds=this.properties.recordIds,
					hash=db.getDirtied(),
					hashPair=db.getDirtied(true);

				_.each(hash,function(v,k){
					if(_.isDate(v)){
						hash[k]=xui.Date.format(v,db.getUI(k).getDateEditorTpl());
					}
				});

				if (recordIds.length>1) {
					var hash2=db.getData();
					var uiValue=db.getUIValue(true);
					var setting=this.properties.gridSetting;
					for(name in hash2){
						var ele=db.getUI(name);
						if(!ele.getDisabled()&&!ele.isDirtied()){
							hash[name]=hash2[name].value;
							hashPair[name]=uiValue[name];
						}
					}
				}

				if(recordIds.length>0){
					var rqsD={
						id:recordIds
					};
					if(hash && !_.isEmpty(hash)) {
						rqsD.fields = hash;

						AJAX.callService('xui/request', ns.properties.gridId, "set", rqsD, function (rsp) {
							if (rsp.data == 1 || typeof(rsp.data) === 'object') {
								_.each(ns._widgets,function(ele){
									ns._waitWidget+=_.tryF(ele._save,[saveDone,rsp],ele,0);
								});
								xui.message("保存成功!");
								ns.fireEvent("afterUpdated", [recordIds, hashPair, rsp.data], ns);
								db.updateValue();
								ns.setDirty(false);
								if (callback) callback(ns.mainDlg, 1);
							} else {
								xui.message(rsp);
							}

						}, function(){
							ns.mainDlg.busy("正在处理 ...");
						}, function(){
							if (ns.mainDlg)
								ns.mainDlg.free();
						});
					} else {
						_.each(ns._widgets,function(ele){
							ns._waitWidget+=_.tryF(ele._save,[saveDone,null],ele,0);
						});
						if (callback) callback(ns.mainDlg, 1);
					}

				}else{
					if (hash && !_.isEmpty(hash)) {
						AJAX.callService('xui/request',ns.properties.gridId,"create",{
							fields:hash
						},function(rsp){
							if(rsp.data){
								_.each(ns._widgets,function(ele){
									ns._waitWidget+=_.tryF(ele._save,[saveDone,rsp],ele,0);
								});
								xui.message("保存成功!");
								db.updateValue();

								ns.fireEvent("afterCreated", [rsp.data], ns);
								ns.setDirty(false);
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
				_.each(ns._widgets,function(ele){
					var relate=ns._get_relate(ele.properties.setting.relate);
					_.tryF(ele._update,[relate,db],ele);
				});
				_.asyRun(function(){
					ns.btnClose.activate();
				});
				if(ns._dataFilter){
					_.tryF(ns._dataFilter.autoComplete,[db]);
				}

			},function(){
				ns.mainDlg.busy("正在处理 ...");
			},function(result){
				ns.mainDlg.free();
				if (result=="fail"){
					ns.mainDlg.close(false);
				}
			});
		},
		navigateTo:function(recordId){
			var ns=this,db=ns.databinder;
			var data=db.getData();
			_.each(data,function(v,k){
				db.setData(k,"");
			});
			db.updateDataToUI();
			ns.properties.recordIds[0]=recordId;
			ns.updateUIfromService(recordId);
		},
		_databinder_afterupdatedatafromui:function (profile, dataFromUI){
			// _.each(dataFromUI,function(o,i){
			// 	if(_.isDate(o)){
			//         dataFromUI[i]=xui.Date.format(o, "yyyy-mm-dd hh:nn:ss");
            //
			// 	}
			// });
		},
		_form_onchange:function(profile, oldValue, newValue, force, tag){
			var ns=this,db=ns.databinder;
			if(ns._dataFilter&&!force){
				_.tryF(ns._dataFilter.autoComplete,[db]);
			}
			_.each(ns._widgets,function(ele){
				var relate=ns._get_relate(ele.properties.setting.relate);
				_.tryF(ele._update,[relate,db,profile],ele);
			});
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
				_.each(ns._widgets,function(ele){
					_.tryF(ele.destory,[null],ele);
				});
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
			_.each(ns._widgets,function(ele){
				if(!_.tryF(ele._checkValid,null,ele,true)){
					valid=false;
					return false;
				}
			});
			// if(!_.tryF(ns._dataFilter._checkValid,[db],ele,true)){
			// 	valid=false;
			// 	return false;
			// }
			return valid;
		},
		_ctl_btnprev_onclick:function(){
			var ns=this;
			ns.fireEvent("onNavigate",[-1]);
		},
		_ctl_btnnext_onclick:function(){
			var ns=this;
			ns.fireEvent("onNavigate",[1]);
		},
		_ctl_btncopy_onclick:function(){
			var ns=this,db=ns.databinder;
			var old=db.getData();
			ns.properties.recordIds=[];
			ns.btnCopy.setVisibility("hidden");
			ns.btnPrev.setVisibility("hidden");
			ns.btnNext.setVisibility("hidden");
			_.each(old,function(v,k){
				var ele=db.getUI(k);
				ele.resetValue();
				ele.setUIValue(v.value);
				if(v.caption){
					ele.setCaption(v.caption);
				}
			});
		}

	}
});
