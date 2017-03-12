Class('App.AdvInput', 'xui.Module',{
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
				.setWidth(250)
				.setHeight(300)
				.setZIndex(1)
				.setCaption("输入窗口")
				.setCloseBtn(true)
				.beforeClose("_mainpanel_beforeclose")
			);
			
			host.mainPanel.append(
				(new xui.UI.TreeGrid())
				.setHost(host,"grid")
				.setSelMode("multibycheckbox")
				.setRowHandlerWidth(40)
				.setRowHandler(true)
				.setTreeMode(false)
			);
			
			host.mainPanel.append(
				(new xui.UI.Block())
				.setHost(host,"ctl_block8")
				.setDock("top")
				.setHeight(30)
				.setBorderType("none")
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
				.setBorderType("none")
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
			grid.sortColumn('value',false);
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
		loadGridData:function(){
			var ns=this, 
				grid=ns.grid;
			
			AJAX.callService('xui/request',ns.properties.key,"advance_input",{
				field:ns.properties.field,
				relate:ns.properties.relate
			},function(rsp){
				if(!ns.isDestroyed()){
					ns._fillGrid(rsp.data.headers, rsp.data.rows);
					var value=[];
					if(ns.properties.cmd=="bit"){
						var t=parseInt(ns.properties.value,10);
						var v=1;
						while(t>0){
							if(t%2){
								value.push(v);
							}
							t=parseInt(t/2,10);
							v*=2;
						}
					}else if(ns.properties.cmd=="multi"){
						value = ns.properties.value.split(",");
						// _.each(value,function(v,i){
						// 	if(_.isNaN(parseInt(v,10))){
						// 		value.splice(i,1);
						// 	}
						// });
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
			this.loadGridData();
		},
		_ctl_sbutton2_onclick:function (profile, e, src, value){
			var ns=this,
				grid=ns.grid;
			
			var value,caption,captions=[];
			var ids=grid.getUIValue(true);
			if(ns.properties.cmd=="bit"){
				value=0;
				_.arr.each(ids,function(id){
					var cell=grid.getCellbyRowCol(id, 'caption');
					if(cell){
						value+=parseInt(id,10);
						captions.push(cell.value);
					}
				});
				value=parseInt("0"+value,10);
			}else if(ns.properties.cmd=="multi"){
				value=ids.join(",");
				_.arr.each(ids,function(id){
					var cell=grid.getCellbyRowCol(id, 'caption');
					if (cell){
						captions.push(cell.value);
					}
				});
			}
			if (captions.length){
				caption=captions.join(',');
			}else{
				caption=' ';
			}
			ns.fireEvent("onSelect",[{value:value,caption:caption}]);
			ns.destroy();
		},
		_ctl_sbutton3_onclick:function(){
			var ns=this;
			ns.fireEvent("onCancel");
			ns.destroy(); 
		}
	}
});
