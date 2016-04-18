// 默认的代码是一个从 xui.Com 派生来的类
Class('App.AdvInput', 'xui.Com',{
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
            
            append((new xui.UI.Panel())
            .setHost(host,"mainPanel")
            .setDock("none")
            .setLeft(0)
            .setTop(0)
            .setWidth(250)
            .setHeight(300)
            .setZIndex(1)
            .setCaption("Selection Window")
            .setCloseBtn(true)
            .beforeClose("_mainpanel_beforeclose")
            );
            
            host.mainPanel.append((new xui.UI.TreeGrid())
            .setHost(host,"grid")
            .setSelMode("multibycheckbox")
            .setRowHandlerWidth(27)
            .setRowHandler(true)
            .setTreeMode(false)
            );
            
            host.mainPanel.append((new xui.UI.Block())
            .setHost(host,"ctl_block8")
            .setDock("top")
            .setHeight(30)
            );
            
            host.ctl_block8.append((new xui.UI.SButton())
            .setHost(host)
            .setTop(3)
            .setWidth(80)
            .setRight(10)
            .setImage("@xui_ini.appPath@image/refresh.png")
            .setCaption("刷新")
            .onClick("_ctl_sbutton1_onclick")
            );
            
            host.ctl_block8.append((new xui.UI.PageBar())
            .setHost(host,"pagebar")
            .setTop(3)
            .setRight(100)
            .onClick("_pagebar_onclick")
            );
            
            host.mainPanel.append((new xui.UI.Block())
            .setHost(host,"ctl_block9")
            .setDock("bottom")
            .setHeight(40)
            );
            host.ctl_block9.append((new xui.UI.SButton())
            .setHost(host)
            .setTop(10)
            .setWidth(80)
            .setLeft(25)
            .setCaption("保存")
            .onClick("_ctl_sbutton2_onclick")
            );
            host.ctl_block9.append((new xui.UI.SButton())
    		.setHost(host)
    		.setTop(10)
    		.setWidth(80)
    		.setRight(25)
    		.setCaption("关闭")
    		.onClick("_ctl_sbutton3_onclick")
            );
            
            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        // 可以自定义哪些界面控件将会被加到父容器中
        customAppend : function(parent, subId, left, top){
            // "return false" 表示默认情况下所有的第一层内部界面控件会被加入到父容器
            return false;
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
        _fillGrid:function(cols,setting,rows){
            var ns=this,grid=ns.grid,caps={},
                header=[];
            if(setting){
                _.each(setting,function(o,i){
                    if(o.tag){
                        caps[i]=o.tag;             
                    }
                });
            }
            var grows=[],grow,cell,obj,index;
            _.arr.each(cols,function(col){
                if(setting && setting[col]){
                    obj=setting[col];
                    obj.id=col;
//                    obj.caption = col.replace(/([A-Z]+)/g," $1");
                    header.push(obj);
                }
            });
            
            _.arr.each(rows,function(row){
                grow={id:row.id,cells:[]};
//                _.arr.each(header,function(col){
//                    index=_.arr.indexOf(cols, col.id);
//                    cell={
//                        value:row.id,
//                        caption:row.caption
//                    };
////                    if(caps[col.id] && (index=_.arr.indexOf(cols,caps[col.id]))!=-1){
////                        cell.caption=row.caption;
////                    }
//                    grow.cells.push(cell);
//                });
                grow.cells.push({value:row.id});
                grow.cells.push({value:row.caption});
                grows.push(grow);
            });

            grid.setHeader(header);
            grid.setRows(grows);
            
            grid.activate();
        },
        _mainpanel_beforeclose:function (profile){
            this.fireEvent("onCancel");
        },
        customAppend : function(parent, subId, left, top){
            var ns=this, root=ns.mainPanel,
                domId=root.getDomId();
            root.getRoot().popToTop(ns.properties.pos);
            root.getRoot().setBlurTrigger(domId, function(){
                // fire custom event
                ns.fireEvent("onCancel");
                ns.destroy(); 
            });
            xui.Event.keyboardHook("esc", false, false, false,function(){
                // fire custom event
                ns.fireEvent("onCancel");
                ns.destroy(); 
            },null,null,domId);
           
            ns.loadGridData(1);
            return true;
        },
        loadGridData:function(curPage){
            var ns=this, 
                grid=ns.grid;
            
            AJAX.callService(ns.properties.key,"getlist_sel",{
            	field:ns.properties.field,
                page:curPage,
                size:20
            },function(rsp){
                if(!ns.isDestroyed()){
                ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count[0][0],10)/20) ),true);
                    ns._fillGrid(rsp.data.cols,rsp.data.setting, rsp.data.rows);
                    var value=[];
                    if(ns.properties.cmd=="bit"){
                    	t=parseInt(ns.properties.value,10);
                    	v=1;
                    	while(t>0){
                    		if(t%2){
                    			value.push(v);
                    		}
                    		t=parseInt(t/2,10);
                    		v*=2;
                    	}
                    }else if(ns.properties.cmd=="multi"){
                    	value = ns.properties.value.split(",");
                    	_.each(value,function(v,i){
                    		if(_.isNaN(parseInt(v,10))){
                    			value.splice(i,1);
                    		}
                    	});
                	}
                    grid.setUIValue(value);
                }
            },function(){
                grid.busy("正在处理 ...");
            },function(result){
                grid.free();
                if(result=='fail'){
                    if(!ns.isDestroyed())
                        ns.destroy(); 
                }
            });   
        },
        _ctl_sbutton1_onclick:function (profile, e, src, value){
            this.loadGridData(this._curPage);
        },
        _ctl_sbutton2_onclick:function (profile, e, src, value){
        	var ns=this,
            	grid=ns.grid;
        	
        	var value;
        	ids=grid.getUIValue(true);
        	if(ns.properties.cmd=="bit"){
        		value=0;
        		_.arr.each(ids,function(id){
        			value+=parseInt(id,10);
        		});
        		value=parseInt("0"+value,10)
        	}else if(ns.properties.cmd=="multi"){
        		value=ids.join(",");
        	}
        	ns.fireEvent("onSelect",[value]);
        	ns.destroy();
        },
        _ctl_sbutton3_onclick:function(){
        	var ns=this;
        	ns.fireEvent("onCancel");
            ns.destroy(); 
        },
        _pagebar_onclick:function (profile, page){
            this.loadGridData(page);
        }
    }
});