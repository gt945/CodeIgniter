// 默认的代码是一个从 xui.Com 派生来的类
Class('App.TableSetting', 'xui.Com',{
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
            
//            append(
//                (new xui.DataBinder())
//                .setHost(host,"databinder")
//                .setPropBinder({})
//                .setName("databinder")
//                .afterUpdateDataFromUI("_databinder_afterupdatedatafromui")
//            );
            
            append(
                (new xui.UI.Dialog())
                .setHost(host,"panel")
                .setLeft(160)
                .setTop(90)
                .setWidth(250)
                .setHeight(120)
                .setResizer(false)
                .setCaption("Form")
                .setImagePos("left top")
                .setMovable(false)
                .setMinBtn(false)
                .setMaxBtn(false)
                .setCloseBtn(false)
                .setOverflow("hidden")
            );
            
            host.panel.append(
                (new xui.UI.Block())
                .setHost(host,"block")
                .setLeft(5)
                .setTop(0)
                .setWidth(235)
                .setHeight(50)
                .setBorderType("inset")
                .setOverflow("visible")
                );
            
            append(
                (new xui.UI.ComboInput())
                .setHost(host,"table_select")
                .setDirtyMark(false)
                .setLeft(20)
                .setTop(40)
                .setType("listbox")
                .setCaption("选择数据表")
                .onChange("_table_select_onchange")
            );
            
            append(
                (new xui.UI.List())
                .setHost(host,"field_list")
                .setDirtyMark(false)
                .setLeft(20)
                .setTop(90)
                .setDropKeys("fl")
                .setDragKey("fl")
            );
            
            append(
                (new xui.UI.Button())
                .setHost(host,"save")
                .setLeft(670)
                .setTop(40)
                .setWidth(100)
                .setHeight(22)
                .setRight(20)
                .setCaption("Save")
                .onClick("_save_onclick")
            );
            
            append(
                (new xui.UI.SLabel())
                .setHost(host,"ctl_slabel3")
                .setLeft(20)
                .setTop(70)
                .setCaption("拖拽调整顺序")
            );
            
            append(
                (new xui.UI.Slider())
                .setHost(host,"rows")
                .setShowDirtyMark(false)
                .setLeft(160)
                .setTop(40)
                .setSteps(15)
                .setIsRange(false)
                .setValue("0")
                .afterUIValueSet("_rows_onchange")
                .onChange("_rows_onchange")
            );
            
            append(
                (new xui.UI.Slider())
                .setHost(host,"cols")
                .setShowDirtyMark(false)
                .setLeft(160)
                .setTop(10)
                .setSteps(8)
                .setIsRange(false)
                .setValue("0")
                .afterUIValueSet("_cols_onchange")
                .onChange("_cols_onchange")
            );
            
            append(
                (new xui.UI.Button())
                .setHost(host,"layout")
                .setLeft(520)
                .setTop(40)
                .setWidth(100)
                .setHeight(22)
                .setCaption("Layout")
                .onClick("_layout_onclick")
            );
            
            append(
                (new xui.UI.ComboInput())
                .setHost(host,"layout_type")
                .setDirtyMark(false)
                .setLeft(370)
                .setTop(40)
                .setType("listbox")
                .setItems([{
                    "id" : "0",
                    "caption" : "1 X"
                },{
                    "id" : "1",
                    "caption" : "2 X"
                },{
                    "id" : "2",
                    "caption" : "3 X"
                },{
                    "id" : "3",
                    "caption" : "4 X"
                }])
                .setValue("0")
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
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
        	var ns=this,
        		ts=ns.table_select;
        	AJAX.callService(null,"tables",null,function(rsp){
        		ns._fillSelect(rsp.data);
        	});
        },
        _fillSelect:function(data){
        	var ns=this,
    			ts=ns.table_select;
        	ts.setItems(data);
        },
        _table_select_onchange:function (profile, oldValue, newValue, force, tag){
            var ns = this, uictrl = profile.boxing();
            AJAX.callService(null,"fields",{tid:newValue},function(rsp){
            	item = profile.getItemByItemId(newValue);
            	ns.cols.setUIValue(item.w, true);
            	ns.rows.setUIValue(item.h, true);
        		ns._fillList(rsp.data);
        		ns._update_layout();
        	});
            return true;
        },
        _fillList:function(data){
        	var ns=this,
    			fl=ns.field_list;
        	fl.setItems(data)
        	.setHeight(data.length*21);
        },
        _adjustFormX:function(x){
        	var ns=this;
        	width=ns._width(x)+50;
        	ns.panel.setWidth(width);
        	ns.block.setWidth(width-15);
        },
        _adjustFormY:function(y){
        	var ns=this;
        	height=ns._height(y)+100;
        	ns.panel.setHeight(height);
        	ns.block.setHeight(height-70);
        },
        _save_onclick:function (profile, e, value){
            var ns = this, uictrl = profile.boxing()
            	,fl=ns.field_list
            	,ts=ns.table_select
            	,tid=ts.getUIValue()
            	,w=ns.cols.getUIValue()
            	,h=ns.rows.getUIValue();
            AJAX.callService(null,"setting",{fields:fl.getItems(),tid:tid,table_w:w,table_h:h}, function(rsp){
            	if(rsp.data==1) {
            		items=ts.getItems();
            		i=_.arr.subIndexOf(items,"id",tid);
            		items[i].w=w;
            		items[i].h=h;
            		xui.alert("Save OK");
            	} else {
            		xui.alert("Save Failed");
            	}
            });
        },
        _rows_onchange:function (profile, oldValue, newValue, force, tag){
            var ns = this, uictrl = profile.boxing(),ts=ns.table_select;
            ns._adjustFormY(parseInt(newValue,10));
        },
        _cols_onchange:function (profile, oldValue, newValue, force, tag){
            var ns = this, uictrl = profile.boxing(),ts=ns.table_select;
            
            ns._adjustFormX(parseInt(newValue,10));
        },
        _layout_onclick:function (profile, e, value){
            var ns = this, uictrl = profile.boxing(),fl=ns.field_list;
            items=fl.getItems();
            cols=parseInt(ns.layout_type.getUIValue(), 10);
            ns.cols.setUIValue(cols*2+1, true);
            ns.rows.setUIValue(Math.floor((items.length+cols)/(cols+1))-1, true);
            _.each(items,function(o,i){
            	x=(i%(cols+1))*2;
            	y=Math.floor(i/(cols+1));
            	o.x=x;
            	o.y=y;
            	o.w=1;
            	o.h=0;
            });
            ns._update_layout();
        },
        _width:function(v){
        	return (v+1)*120+v*30;
        },
        _width_r:function(v){
        	r=Math.round((v-80)/150);
        	return r<0?0:r;
        },
        _height:function(v){
        	return (v+1)*20+v*10;
        },
        _height_r:function(v){
        	r=Math.round((v-20)/30);
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
        _update_layout:function(){
        	var ns = this,fl=ns.field_list;
        	items=fl.getItems();
        	ns.block.removeChildren(null,true);
        	_.each(items,function(o,i){
        		x=parseInt(o.x,10);
        		y=parseInt(o.y,10);
        		w=parseInt(o.w,10);
        		h=parseInt(o.h,10);
        		prop={
        			ele:o.id,
                    forceMovable:false,
                    minWidth:0,
                    minHeight:0,
                    dragArgs:{
                        widthIncrement:1,
                        heightIncrement:1
                    },
                    zIndex:xui.Dom.TOP_ZINDEX
                };
        		ele=(new xui.UI.Button())
					.setHost(ns)
	        		.setCaption(o.caption)
	            	.setLeft(ns._left(x))
					.setTop(ns._top(y))
					.setWidth(ns._width(w))
					.setHeight(ns._height(h));
        		ns.block.append(ele);
        		ns.block.append((xui.create({key:'App.AdvResizer'}))
        			.setHost(ns)
					.setProperties(prop)
					.resetTarget(ele, false)
	            	.onContextmenu(function(){return false})
	            	.onUpdate(function(resizer, target, size, cssPos){
	                    if(target){
	                        target.each(function(target){
	                            target = xui([target]);
	                            items=fl.getItems();
	                            i=_.arr.subIndexOf(items,"id",resizer.properties.ele)
	                            var profile = xui.UIProfile.getFromDom(target.get(0).id);
                                if(size){
                                	orig_size=profile.getRoot().cssSize();
                                	items[i].w=ns._width_r(size.width+orig_size.width);
                                	items[i].h=ns._height_r(size.height+orig_size.height);
                                	size.width=ns._width(items[i].w);
                                	size.height=ns._height(items[i].h);
                                	profile.getRoot().cssSize(size);
                                    xui.UI.$tryResize(profile,size.width,size.height,null,true);
                                }
                                if(cssPos){
                                	orig_pos=profile.getRoot().cssPos();
                                	items[i].x=ns._left_r(cssPos.left+orig_pos.left);
                                	items[i].y=ns._top_r(cssPos.top+orig_pos.top);
                                	cssPos.left=ns._left(items[i].x);
                                	cssPos.top=ns._top(items[i].y);
                                    profile.getRoot().cssPos(cssPos);
                                }
                                
                        	});
	                    }
	                    resizer.boxing().rePosSize();
	                    return false;
	            	})
            	);
//        		xui.ComFactory.newCom('App.AdvResizer', function(){
//        			this.setHost(ns).resetTarget(ele, false);
//        			ns.block.append(this);
//                }, null, {
//        			ele:o.id,
//                    forceMovable:false,
//                    minWidth:0,
//                    minHeight:0,
//                    dragArgs:{
//                        widthIncrement:1,
//                        heightIncrement:1
//                    },
//                    zIndex:xui.Dom.TOP_ZINDEX
//                }, {
//                    onUpdate:function(resizer, target, size, cssPos){
//	                    if(target){
//	                        target.each(function(target){
//	                            target = xui([target]);
//	                            items=fl.getItems();
//	                            i=_.arr.subIndexOf(items,"id",resizer.properties.ele)
//	                            var profile = xui.UIProfile.getFromDom(target.get(0).id);
//                                if(size){
//                                	orig_size=profile.getRoot().cssSize();
//                                	items[i].w=ns._width_r(size.width+orig_size.width);
//                                	items[i].h=ns._height_r(size.height+orig_size.height);
//                                	size.width=ns._width(items[i].w);
//                                	size.height=ns._height(items[i].h);
//                                	profile.getRoot().cssSize(size);
//                                    xui.UI.$tryResize(profile,size.width,size.height,null,true);
//                                }
//                                if(cssPos){
//                                	orig_pos=profile.getRoot().cssPos();
//                                	items[i].x=ns._left_r(cssPos.left+orig_pos.left);
//                                	items[i].y=ns._top_r(cssPos.top+orig_pos.top);
//                                	cssPos.left=ns._left(items[i].x);
//                                	cssPos.top=ns._top(items[i].y);
//                                    profile.getRoot().cssPos(cssPos);
//                                }
//                                
//                        	});
//	                    }
//	                    resizer.boxing().rePosSize();
//	                    return false;
//	            	},
//	            	onContextmenu:function(){
//	            		return false;
//	            	}
//                });
            });
        }
    }
});
