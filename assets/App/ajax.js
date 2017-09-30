AJAX={
	serviceURI:SITEURL,
	pageSize:20,
	callService:function(rpc, objectName, action, paras, callback, onStart, onEnd, file){
		_.tryF(onStart);
		xui.Thread.observableRun(function(threadid){
			paras=paras||{};
			_.merge(paras,{action:action});
			
			var data={key:objectName, paras:paras}, options;
			options={method:'post'};

			if(file){
				data.file=file;
			}
			xui.request(AJAX.serviceURI+rpc, data, function(rsp){
				var obj=rsp,result="ok";
				if(obj && obj.code){
					if(obj.code==200){
						if(obj.warn){
							xui.message(obj.warn);
						}
						_.tryF(callback,[obj]);
					}else{
						result="fail";
						xui.alert(obj.msg);
					}
				}else{
					result="fail";
					xui.alert("有错误发生");
					LOG.error(_.serialize(rsp));
				}
				_.tryF(onEnd,[result]);
			},function(rsp){
				xui.alert(_.serialize(rsp));
				_.tryF(onEnd,["fail"]);
			}, threadid,options);
		});
	},
	callService2:function(rpc, objectName, action, paras, callback, onStart, onEnd, file){
		_.tryF(onStart);
		paras=paras||{};
		_.merge(paras,{action:action});
		
		var data={key:objectName, paras:paras}, options;
		options={method:'post'};

		if(file){
			data.file=file;
		}
		xui.request(AJAX.serviceURI+rpc, data, function(rsp){
			var obj=rsp,result="ok";
			if(obj && obj.code){
				if(obj.code==200){
					if(obj.warn){
						xui.message(obj.warn);
					}
					_.tryF(callback,[obj]);
				}else{
					result="fail";
					xui.alert(obj.msg);
				}
			}else{
				result="fail";
				xui.alert("有错误发生");
				LOG.error(_.serialize(rsp));
			}
			_.tryF(onEnd,[result]);
		},function(rsp){
			xui.alert(_.serialize(rsp));
			_.tryF(onEnd,["fail"]);
		}, null,options);
	}
};
