// The default code is a com class (inherited from xui.Com)
Class('App.GridForm', 'xui.Com',{
    Instance:{
        // To initialize internal components (mostly UI controls)
        // *** If you're not a skilled, dont modify this function manually ***
        iniComponents : function(){
            // [[Code created by CrossUI RAD Tools
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            append((new xui.DataBinder())
            .setHost(host,"databinder")
            .setName("databinder")
            .afterUpdateDataFromUI("_databinder_afterupdatedatafromui")
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
                    .setImagePos("left top")
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
            var setting=host.getProperties("gridSetting")
            var index=1;
            for(var f in setting){
            	var dataField=f;
            	var lableWidth=75;
            	var ele;
            	host.ctl_block.append((new xui.UI.Label())
					.setHost(host,"ctl_slabel"+f)
					.setLeft(host._left(setting[f].x))
					.setTop(host._top(setting[f].y))
					.setWidth(lableWidth)
					.setHeight(host._height(setting[f].h))
					.setVAlign("middle")
					.setCaption(setting[f].caption)
					);
            	ele=_.unserialize(setting[f].form);
            	
            	host.ctl_block.append(ele
					.setHost(host,"form_input"+f)
					.setDataBinder("databinder")
					.setDataField(dataField)
					.setLeft(host._left(setting[f].x)+lableWidth+5)
					.setTop(host._top(setting[f].y))
					.setWidth(host._width(setting[f].w)-lableWidth-5)
					.setHeight(host._height(setting[f].h))
					.setTabindex(index++)
					);
            }
            
            host.mainDlg.append((new xui.UI.SButton())
                .setHost(host,"btnSave")
                .setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 - 100)
                .setTop(host._height(host.getProperties("gridFormHeight"))+40)
                .setWidth(70)
                .setCaption("保存")
                .setTabindex(index++)
                .onClick("_ctl_sbutton14_onclick")
                );
                
            host.mainDlg.append((new xui.UI.SButton())
                .setHost(host,"btnClose")
                .setLeft((host._width(host.getProperties("gridFormWidth"))+50)/ 2 + 30)
                .setTop(host._height(host.getProperties("gridFormHeight"))+40)
                .setWidth(70)
                .setCaption("关闭")
                .setTabindex(index++)
                .onClick("_ctl_sbutton486_onclick")
                );
                    
            return children;
            // ]]Code created by CrossUI RAD Tools
        },
        customAppend : function(parent, subId, left, top){
            this.mainDlg.showModal(parent, left, top);
            return false;
        },
        _width:function(v){
        	return (v+1)*120+v*30;
        },
        _width_r:function(v){
        	r=Math.round((v-80)/150);
        	return r<0?0:r;
        },
        _height:function(v){
        	return (v+1)*24+v*6;
        },
        _height_r:function(v){
        	r=Math.round((v-24)/30);
        	return r<0?0:r;
        },
        _left:function(v){
        	return 15+v*120+v*30;
        },
        _left_r:function(v){
        	r=Math.round((v-15)/150);
        	return r<0?0:r;
        },
        _top:function(v){
        	return v*30+15;
        },
        _top_r:function(v){
        	r=Math.round((v-15)/30);
        	return r<0?0:r;
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
            var recordId=this.properties.recordId;
            // clear all UI
            // for inputs
            this.databinder.setData().updateDataToUI();
            // open
            if(_.isSet(recordId)){
                this.updateUIfromService(recordId);
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
            
            // need save?
            if(db.isDirtied() || ns.isGridDirty()){
                
                // check UI valid
                if(!db.checkValid()){
                    xui.message("错误发生!");
                    return;
                }
                var recordId=this.properties.recordId,
                    hash=db.getDirtied();
                
                // adjust data
                _.each(hash,function(o,i){
                    if(_.isDate(o)){
                        hash[i]=xui.Date.format(o, "yyyy-mm-dd hh:nn:ss");
                    }
                });
                
                if(recordId){
                    //update
                    rqsD={
                        id:recordId
                    };
                    if(db.isDirtied() && hash && !_.isEmpty(hash))
                    rqsD.fields=hash;
//                    if(ns.isGridDirty() && details && details.length>0)
//                        rqsD.details=details;
                    
                    AJAX.callService(ns.getProperties("gridName"),"set",rqsD,function(rsp){
                        if(rsp.data==1){
                            xui.message("保存成功!");
                        }else{
                            xui.message(rsp);
                        }
                        
                        // update grid value
                        ns.fireEvent("afterUpdated", [recordId, db.getDirtied(true)], ns);
                        db.updateValue();
                        ns.setDirty(false);
                        if(callback)callback(ns.mainDlg,rsp.data);
                    },function(){
                        ns.mainDlg.busy("正在处理 ...");
                    },function(){
                        if(ns.mainDlg)
                            ns.mainDlg.free();
                    });        
                }else{
                    //create
                    AJAX.callService(ns.getProperties("gridName"),"create",{
                        fields:hash,
//                        details:details
                    },function(rsp){
                        if(rsp.data){
                            xui.message("保存成功!");
                        }else{
                            xui.message(rsp);
                        }
                        // rsp.data
                        ns.mainDlg.free();
                        db.updateValue();
                        
                        // add to grid 
                        ns.fireEvent("afterCreated", [rsp.data], ns);
                        ns.setDirty(false);
                        if(callback)callback(ns.mainDlg,rsp.data);
                        
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
            // In this class, we use control's get/setValue directly
            AJAX.callService(ns.getProperties("gridName"),"get",{id:recordId},function(rsp){
                var row=rsp.data.rows[0], map=rsp.data.caps,bmap=rsp.data.bools;
                _.arr.each(rsp.data.cols,function(col,i){
                    data[col]=row[i];
                    if(map && map[col]){
                        data[col]={
                            value:row[i],
                            caption:row[_.arr.indexOf(rsp.data.cols, map[col])]
                        };
                    }
                    if(bmap && bmap[col]){
                        data[col]=parseInt(data[col],10);
                    }
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
            // adjust data
            _.each(dataFromUI,function(o,i){
                if(_.isDate(o)){
                    dataFromUI[i]=xui.Date.format(o, "yyyy-mm-dd hh:nn:ss", firstDayOfWeek);
                }
            });
        },
        _ctl_sbutton486_onclick:function (profile, e, src, value){
            this.mainDlg.close(true);
        },
        _maindlg_beforeclose:function (profile){
            var ns=this, db=ns.databinder;
            // need save?
            if(db.isDirtied() || ns.isGridDirty()){
                xui.confirm("确认", "数据已修改,是否保存?", function(){
                    ns.saveUI(function(dlg){
                        ns.mainDlg.close(false);
                    });
                }, function(){
                    ns.mainDlg.close(false);
                });
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
        _select_beforepopshow:function(profile, popCtl){
        	var ns = this, elem = popCtl.boxing();
        	AJAX.callService(ns.getProperties("gridName"),"get_select",{field:profile.boxing().getDataField()},function(rsp){
                if(!elem.isDestroyed()){
                    profile.boxing().setItems(rsp.data);
                    elem.setItems(rsp.data).setValue(null,true);
                }
            },function(){
            	elem.setItems(["加载中 ..."],true);
            },function(){
            });
        },
        _select_beforecombopop:function (profile, pos,e ,src){
            var ns=this,ctrl=profile.boxing();
            
            xui.ComFactory.newCom('App.AdvInput', function(){
                this.setProperties({
                    key:ns.getProperties("gridName"),
                    field:ctrl.getDataField(),
                    pos:ctrl.getRoot(),
                    cmd:ctrl.getProperties("cmd"),
                    value:ctrl.getUIValue()
                });
                this.setEvents({
                    onCancel:function(){
                        ctrl.activate();
                    },
                    onSelect:function(value){
                        ctrl.setUIValue(value);
                        ctrl.activate();
                    }
                });
                this.show(); 
            });
            return false;
        }
    }
});