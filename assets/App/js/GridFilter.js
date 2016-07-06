// 默认的代码是一个从 xui.Com 派生来的类
Class('App.GridFilter', 'xui.Com',{
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
            
            append((new xui.UI.Dialog())
                .setHost(host,"mainDlg")
                .setLeft(240)
                .setTop(170)
                .setWidth(400)
                .setHeight(270)
                .setCaption("过滤")
                .setResizer(false)
                .setMinBtn(false)
                .setMaxBtn(false)
//                .setCloseBtn(false)
                .setRestoreBtn(false)
                .beforeClose("_xui_ui_dialog_beforeclose")
            );
            
            host.mainDlg.append((new xui.UI.Block())
                .setHost(host,"xui_ui_block3")
                .setLeft(5)
                .setTop(0)
                .setWidth(380)
                .setHeight(200)
                .setBorderType("inset")
                .setOverflow("overflow-x:hidden;overflow-y:auto")
                );
            
            host.xui_ui_block3.append((new xui.UI.Pane())
                .setHost(host,"xui_ui_c_bar")
                .setDock("width")
                .setWidth("auto")
                .setHeight(35)
                .setPosition("relative")
                );
            
            host.xui_ui_c_bar.append((new xui.UI.ComboInput())
                .setHost(host,"groupOp")
                .setDirtyMark(false)
                .setLeft(21)
                .setTop(5)
                .setType("listbox")
                .setItems([{
                    "id" : "AND",
                    "caption" : "满足全部条件"
                },
                {
                    "id" : "OR",
                    "caption" : "满足任一条件"
                }])
                .setValue("AND")
                );
            
            host.xui_ui_c_bar.append((new xui.UI.SButton())
        		.setHost(host,"xui_ui_button_add")
        		.setLeft(150)
        		.setTop(5)
        		.setWidth(20)
        		.setCaption("+")
        		.setTips("增加条件")
        		.onClick("_xui_ui_button_add_onclick")
            );
            
            host.mainDlg.append((new xui.UI.SButton())
                .setHost(host,"xui_ui_button_find")
                .setLeft(80)
                .setTop(210)
                .setWidth(70)
                .setCaption("过滤")
                .onClick("_xui_ui_button_find_onclick")
                );
            
            host.mainDlg.append((new xui.UI.SButton())
                .setHost(host,"xui_ui_button_reset")
                .setLeft(160)
                .setTop(210)
                .setWidth(70)
                .setCaption("清空")
                .onClick("_xui_ui_button_reset_onclick")
                );
            
            host.mainDlg.append((new xui.UI.SButton())
                .setHost(host,"xui_ui_button_cancel")
                .setLeft(240)
                .setTop(210)
                .setWidth(70)
                .setCaption("取消")
                .onClick("_xui_ui_button_cancel_onclick")
                );
            
            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        // 可以自定义哪些界面控件将会被加到父容器中
        customAppend : function(parent, subId, left, top){
        	this.mainDlg.showModal(parent, left, top);
            return true;
        },
        // 加载其他资源可以用本函数
        iniResource: function(com, threadid){
            //xui.Thread.suspend(threadid);
            //var callback=function(/**/){
            //    /**/
            //    xui.Thread.resume(threadid);
            //};
        },
        // 加载其他Com可以用本函数
        iniExComs : function(com, threadid){
            //xui.Thread.suspend(threadid);
            //var callback=function(/**/){
            //    /**/
            //    xui.Thread.resume(threadid);
            //};
        },
        _get_fields_list:function(setting){
        	var items=[];
        	for(var f in setting){
        		if (setting[f].filter)
        			items.push({"id":f, "caption":setting[f].caption[0], "option":setting[f].filterOpts, "form":setting[f].form});
            }
        	return items;
        },
        _update_opt:function(ele,t){
        	var items=[];
        	var v=0;
        	var opts=[
	          {opt:"eq",caption:"等于"},
	          {opt:"ne",caption:"不等于"},
	          {opt:"lt",caption:"小于"},
	          {opt:"le",caption:"小于等于"},
	          {opt:"gt",caption:"大于"},
	          {opt:"ge",caption:"大于等于"},
	          {opt:"cn",caption:"包含"},
	          {opt:"nc",caption:"不包含"},
	          {opt:"bw",caption:"开始于"},
	          {opt:"bn",caption:"不开始于"},
	          {opt:"ew",caption:"结束于"},
	          {opt:"en",caption:"不结束于"},
	          {opt:"in",caption:"属于"},
	          {opt:"ni",caption:"不属于"},
	          {opt:"nu",caption:"为空"},
	          {opt:"nn",caption:"不为空"}
	        ];
        	t=parseInt(t,10);
        	while(t>0){
        		if(t%2){
        			items.push({"id":opts[v].opt,"caption":opts[v].caption});
        		}
        		t=parseInt(t/2,10);
        		v++;
        	}
        	if(items.length){
        		ele.setItems(items);
        		ele.setUIValue(items[0].id);
        	}
        },
        _xui_ui_button_add_onclick:function (profile,e,src){
            var ns = this, uictrl = profile.boxing();
            var json_code ='new xui.UI.Pane({"key":"xui.UI.Pane","properties":{"dock":"width","width":"auto","height":25,"position":"relative"},"children":[[{"type":"field","key":"xui.UI.ComboInput","properties":{"dirtyMark":false,"left":10,"top":0,"type":"listbox"},"events":{"afterUIValueSet":"_xui_ui_field_onchange"}}],[{"type":"operation","key":"xui.UI.ComboInput","properties":{"dirtyMark":false,"left":135,"top":0,"width":70,"type":"listbox"}}],[{"key":"xui.UI.SButton","properties":{"left":340,"top":0,"width":20,"caption":"-"},"events":{"onClick":"_xui_ui_button_del_onclick"}}]]})';
            var nb=_.unserialize(json_code);
            _.arr.each(nb.getChildren(null, true), function(o, i) {
            	var ob=o.boxing();
				ob.setHost(ns);
				if(o.type=="field"){
					var items=ns._get_fields_list(ns.properties.gridSetting);
					ob.setItems(items);
					ob.setUIValue(items[0].id);
				}
			});
            this.xui_ui_block3.append(nb);
            
        },
        _xui_ui_button_del_onclick:function(profile,e,src){
        	profile.parent.boxing().removePanel();
        },
        _xui_ui_field_onchange:function (profile,oldValue,newValue,force,tag){
            var ns = this, uictrl = profile.boxing();
            var item = profile.getItemByItemId(newValue);
            var ele=_.unserialize(item.form);
            var pane=profile.parent.boxing();
            for(var i in profile.parent.children){
            	var tmp=profile.parent.children[i][0];
            	if (tmp.type=='value'){
            		tmp.boxing().destroy();
            	} else if(tmp.type=='operation'){
            		ns._update_opt(tmp.boxing(), item.option);
            	}
            }
            
            ele.get(0).type="value";
            pane.append(ele.setHost(ns)
	        	.setLeft(210)
	        	.setLabelSize(0)
	        	.setDataField(item.id)
	        	.setDirtyMark(false)
        	);
        },
        _xui_ui_button_find_onclick:function(profile,e,src){
        	var ns=this;
        	var filters={};
        	filters.groupOp=ns.groupOp.getUIValue();
        	filters.rules=[];
        	_.arr.each(ns.xui_ui_block3.getChildren(null, false), function(o, i) {
				if (o.alias!="xui_ui_c_bar"){
					var group={};
					_.arr.each(o.boxing().getChildren(null, true), function(c,d){
						e=c.boxing();
						if(c.type=="field"){
							group.field=e.getUIValue();
						}else if(c.type=="operation") {
							group.op=e.getUIValue();
						}else if (c.type=="value"){
							group.data=e.getUIValue();
						}
					});
					filters.rules.push(group);
				}
        	});
        	
        	ns.fireEvent("onSelect",[filters]);
        	ns.mainDlg.hide();
        },
        _xui_ui_button_reset_onclick:function(profile,e,src){
        	var ns = this;
        	_.arr.each(ns.xui_ui_block3.getChildren(null, false), function(o, i) {
				if (o.alias!="xui_ui_c_bar"){
					o.boxing().removePanel();
				}
        	});
        },
        _xui_ui_button_cancel_onclick:function(profile,e,src){
        	var ns=this;
        	ns.mainDlg.hide();
        },
        _xui_ui_dialog_beforeclose:function(profile,e,src){
        	this._xui_ui_button_cancel_onclick(profile,e,src);
        	return false;
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
            var setting=ns.getProperties("gridSetting")
            xui.ComFactory.newCom(ctrl.getProperties("app"), function(){
                this.setProperties({
                    key:ns.getProperties("gridName"),
                    field:ctrl.getDataField(),
                    pos:ctrl.getRoot(),
                    cmd:ctrl.getProperties("cmd"),
                    value:ctrl.getUIValue(),
                    setting:setting[ctrl.getDataField()]
                });
                this.setEvents({
                    onCancel:function(){
                    	if(!ctrl.isDestroyed()){
                    		ctrl.activate();
                    	}
                    },
                    onSelect:function(value,caption,item){
                    	if(!ctrl.isDestroyed()){
	                        ctrl.setUIValue(value);
	                        if(caption){
	                        	ctrl.setCaption(caption);
	                        }
	                        ctrl.activate();
                    	}
                    }
                });
                this.show(); 
            });
            return false;
        }
        
    }
});