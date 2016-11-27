Class('App.TableSelect', 'xui.Com',{
    Instance:{
        autoDestroy : true,
        properties : {},
        initialize : function(){
        },
        iniComponents : function(){
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append(
                (new xui.UI.Panel())
	            .setHost(host,"mainPanel")
	            .setDock("none")
	            .setLeft(0)
	            .setTop(0)
	            .setWidth(800)
	            .setHeight(600)
	            .setZIndex(1)
	            .setCaption("选择窗口")
	            .setCloseBtn(true)
	            .beforeClose("_mainpanel_beforeclose")
            );
            
            host.mainPanel.append(
                (new xui.UI.TreeGrid())
	            .setHost(host,"grid")
	            .setRowHandlerWidth(27)
	            .setRowHandler(true)
	            .setTreeMode(false)
            );
            
            host.mainPanel.append(
                (new xui.UI.Block())
	            .setHost(host,"ctl_block8")
	            .setDock("top")
	            .setHeight(30)
            );

            host.ctl_block8.append(
                (new xui.UI.PageBar())
                .setHost(host,"pagebar")
                .setTop(3)
                .setRight(100)
                .setCaption("页数:")
                .onClick("_pagebar_onclick")
            );

            host.ctl_block8.append(
            	(new xui.UI.SButton())
	            .setHost(host)
	            .setTop(3)
	            .setWidth(80)
	            .setRight(10)
	            .setImage("@xui_ini.appPath@image/refresh.png")
	            .setCaption("刷新")
	            .onClick("_ctl_sbutton1_onclick")
            );
            
            host.mainPanel.append(
            	(new xui.UI.Block())
	            .setHost(host,"ctl_block9")
	            .setDock("bottom")
	            .setHeight(40)
            );
            
            host.ctl_block9.append(
            	(new xui.UI.SButton())
	            .setHost(host)
	            .setTop(10)
	            .setWidth(80)
	            .setLeft(25)
	            .setCaption("确定")
	            .onClick("_ctl_sbutton2_onclick")
            );
            
            host.ctl_block9.append(
            	(new xui.UI.SButton())
	    		.setHost(host)
	    		.setTop(10)
	    		.setWidth(80)
	    		.setRight(25)
	    		.setCaption("关闭")
	    		.onClick("_ctl_sbutton3_onclick")
            );
            
            return children;
        },
        _fillGrid:function(headers,rows){
            var ns=this,grid=ns.grid;
            grid.setHeader(headers);
            grid.setRows(rows);
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
                ns.fireEvent("onCancel");
                ns.destroy(); 
            });
            xui.Event.keyboardHook("esc", false, false, false,function(){
                ns.fireEvent("onCancel");
                ns.destroy(); 
            },null,null,domId);
           
            ns.loadGridData(1);
            return true;
        },
        loadGridData:function(curPage){
            var ns=this, 
                grid=ns.grid;
            this._curPage=curPage;
            AJAX.callService('xui/request',ns.properties.key,"table_select",{
            	field:ns.properties.field,
                page:curPage,
                size:20
            },function(rsp){
                if(!ns.isDestroyed()){
                    ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count,10)/20) ),true);
                    ns._fillGrid(rsp.data.headers, rsp.data.rows);
                    // grid.setUIValue(ns.properties.value);
                    ns.data = rsp.data;
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
        	var ns=this,caption=null,
            	grid=ns.grid;
        	var index=_.arr.subIndexOf(ns.data.headers,'id',ns.data.caption);
            var row=grid.getActiveRow();
            var extra=[];
            if(index>=0){
                caption=row.cells[index].value;
            }
            _.arr.each(ns.data.map,function(map){
                index=_.arr.subIndexOf(ns.data.headers, 'id', map.id2);
                if(index>=0)
                    extra.push({id:map.id1,cell:row.cells[index]});
            });
        	ns.fireEvent("onSelect",[{value:row.id,caption:caption},extra]);
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