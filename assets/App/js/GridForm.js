Class('App.GridForm', 'xui.Com',{
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
				.setResizer(false)
				.setOverflow("hidden")
				.setCaption("编辑")
				.setMinBtn(false)
				.setMaxBtn(false)
				.onHotKeydown("_maindlg_onhotkeydown")
				.beforeClose("_maindlg_beforeclose")
				);
			host.mainDlg.append((new xui.UI.Block())
				.setHost(host,"ctl_block")
				.setLeft(5)
				.setTop(0)
				.setWidth(host._width(host.getProperties("gridFormWidth"))+35)
				.setHeight(host._height(host.getProperties("gridFormHeight"))+30)
				.setOverflow("visible")
				.setBorderType("inset")
				);
			var setting=host.getProperties("gridSetting");
			var recordIds=host.getProperties("recordIds");
			var index=0;
			var data={};
			for(var f in setting){
				var dataField=f;
				var ele=_.unserialize(setting[f].form);
				if(f==host.getProperties("gridGroup")&&!_.isSet(host.properties.recordId)){
					ele.setProperties('initialValue',host.properties._gid);
				}else if(f==host.getProperties("gridTreeMode")){
					ele.setProperties('initialValue',host.properties._pid);
				}else if(setting[f].template&&recordIds.length==0){
					ele.setProperties('initialValue',{value:setting[f].template});
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
                index++;
			}
			host.databinder.setData(data);
			
			host.mainDlg.append((new xui.UI.SButton())
				.setHost(host,"btnSave")
				.setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 - 100)
				.setTop(host._height(host.getProperties("gridFormHeight"))+40)
				.setWidth(70)
				.setCaption("保存")
				.setTabindex(++index)
				.onClick("_ctl_sbutton14_onclick")
				);
				
			host.mainDlg.append((new xui.UI.SButton())
				.setHost(host,"btnClose")
				.setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 + 30)
				.setTop(host._height(host.getProperties("gridFormHeight"))+40)
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
                xui.ComFactory.newCom(ns.properties.dataFilter,function(){
                    ns._dataFilter=this;
                    callback();
                },null,null,null);

            }

		},
		_width:function(v){
			return (v+1)*180;
		},
		_height:function(v){
			return (v+1)*24+v*6;
		},
		_left:function(v){
			return 15+v*180;
		},
		_top:function(v){
			return v*30+15;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var recordIds=this.properties.recordIds;
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
			if(_.isSet(recordIds)&&recordIds.length>0){
				this.updateUIfromService(recordIds[0]);
			}
			this.setDirty(false);
		},
		setDirty:function(dirty){
			this.__dirty=dirty;
		},
		isGridDirty:function(){
			return !!this.__dirty;
		},
		_ctl_sbutton14_onclick:function (profile,e,src,value){
			this.saveUI(function(o,s){
				if (s==1){
					o.close(false);
				}
			});
		},
		saveUI:function(callback){
			var ns=this, db=ns.databinder;
			
			if(db.isDirtied() || ns.isGridDirty()){
				
				if(!db.checkValid()){
					xui.message("错误发生!");
					return;
				}
				var recordIds=this.properties.recordIds,
					hash=db.getDirtied(),
					hashPair=db.getDirtied(true);

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
				// _.each(hash,function(o,i){
				// 	if(_.isDate(o)){
				//        hash[i]=xui.Date.format(o, "yyyy-mm-dd hh:nn:ss");
				// 	}
				// });
				
				if(recordIds.length>0){
					var rqsD={
						id:recordIds
					};
					if(hash && !_.isEmpty(hash))
						rqsD.fields=hash;
					
					AJAX.callService('xui/request',ns.properties.gridName,"set",rqsD,function(rsp){
						if(rsp.data==1){
							xui.message("保存成功!");
							ns.fireEvent("afterUpdated", [recordIds,hashPair], ns);
							db.updateValue();
							ns.setDirty(false);
							if(callback)callback(ns.mainDlg,rsp.data);
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
					AJAX.callService('xui/request',ns.properties.gridName,"create",{
						fields:hash
					},function(rsp){
						if(rsp.data){
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
				}

			}else{
				xui.message("未修改");
			}
		},
		updateUIfromService:function(recordId){
			var ns=this,data=ns.databinder.getData();
			AJAX.callService('xui/request',ns.properties.gridName,"get",{id:recordId},function(rsp){
				var cells=rsp.data.rows[0].cells,
				cols=ns.properties.gridCols,
				settings=ns.properties.gridSetting;
				_.arr.each(cols,function(col,i){
					data[col]=cells[i];
				});
				
				ns.databinder.setData(data).updateDataToUI();
				
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
				xui.confirm("确认", "确定退出?", function(){
					ns.mainDlg.close(false);
				}, null);
				return false;
			}else{
				return true;
			}
		},
		_maindlg_onhotkeydown:function (profile, keyboard, e, src){
			if(keyboard.key=="esc"){
				this.mainDlg.close(true);
			}
		},
		_get_relate:function(profile){
			var ns=this,db=ns.databinder;
			var relate=profile.properties.setting.relate.split(',');
			var data={};
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
			return data;
		},
		_select_beforepopshow:function(profile, popCtl){
			var ns = this, elem = popCtl.boxing();
			var para = {field:profile.boxing().getDataField()};
			para['relate']=ns._get_relate(profile);
			if(!elem._isset){
				AJAX.callService('xui/request',ns.properties.gridName,"get_select",para,
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
			xui.ComFactory.newCom(ctrl.getProperties("app"), function(){
				this.setProperties({
					key:ns.properties.gridName,
					field:ctrl.getDataField(),
					pos:ctrl.getRoot(),
					cmd:ctrl.getProperties("cmd"),
					value:ctrl.getUIValue(),
					setting:setting[ctrl.getDataField()],
					relate:ns._get_relate(profile)
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
									ele.setUIValue(exval.cell.value);
									if(typeof(exval.cell.caption)==="string"){
										ele.setCaption(exval.cell.caption);
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
		}
	}
});
