<table id="<?php echo $jqgrid?>" class="jqgrid_table"
	onselectstart="return false"></table>
<div id="<?php echo $jqpager?>"></div>
<script>
$(function() {
	var <?php echo $jqgid?> = "<?php echo $jqgid_value?>";
	var <?php echo $jqdata?> = {
		select_row: function(rowid, status, e) {
	        var lastsel = $(this).data('lastsel');
	        if (typeof(e) !== "object" || e.ctrlKey) {
	        	$(this).data('lastsel', rowid);
	        } else if ( e.shiftKey ){
	        	 if(rowid && rowid !== lastsel){ 
	     				var ids = $(this).jqGrid('getDataIDs');
	     				var sel = false;
	     				var brk = false;
	     				var cid;
	                 	$(this).jqGrid('resetSelection');
	                 	for (cid in ids) {
	     					if (ids[cid] === rowid || ids[cid] === lastsel) {
	     						if (!sel) sel = true;
	     						else brk = true;
	     					}
	     					if (sel) $(this).jqGrid('setSelection', ids[cid], false);
	     					if (brk) break;
	                     }
	              } 
	        } else {
	        	$(this).jqGrid('resetSelection');
	        	$(this).jqGrid('setSelection', rowid, true);
	        }
	    },
	    jqcallback: function(response, postdata) {
	    	select2_change();
	    	if (response.responseText.length) {
				return [false, response.responseText];
			} else {
				return [true, ""];
			}
	    },
	    grid_data: function(postdata) {
	    	var tmp = {};
			postdata['_table'] = '<?php echo $table?>';
			for( key in postdata ){
				if ($(".FormElement[name="+key+"]").hasClass("datatd-disabled")) {
				} else {
					tmp[encodeURIComponent(key).replace(/%/g,":")] = postdata[key];
					//delete postdata[key];
				}
			}
			var selarrrow = $("#<?php echo $jqgrid?>").jqGrid('getGridParam', 'selarrrow');
			if (selarrrow.length > 1) {
				tmp['id'] = selarrrow.join(",");
			}
			<?php if ($group) {?>
			tmp['_gid'] = <?php echo $jqgid?>;
			if ($("#subgroup_<?php echo $jqgrid?>").find("span").hasClass("ui-icon-check")) {
				tmp['_subgroup'] = 1;
			} else {
				tmp['_subgroup'] = 0;
			}
			<?php }?>
			return tmp;
	    },
	    reload: function() {
	    	$("#<?php echo $jqgrid?>").trigger("reloadGrid");
	    },
	    before_show:function (a, b) {
			$(".CaptionTD .form-lock").remove();
			var selarrrow = $("#<?php echo $jqgrid?>").jqGrid('getGridParam', 'selarrrow');
			if (selarrrow.length > 1) {
				$(".CaptionTD").prepend("<span class=\"ui-icon ui-icon-circle-close form-lock\" style=\"float: left;\"></span>");
				$(".DataTD").children().prop("disabled", true).addClass("datatd-disabled");
					
				$(".CaptionTD").unbind('click').bind('click', function() {
					var lock = $(this).find(".form-lock");
					lock.toggleClass("ui-icon-circle-check");
					var datatd = $(this).next().children();
					if (lock.length) {
						if (lock.hasClass("ui-icon-circle-check")) {
							datatd.prop("disabled", false).removeClass("datatd-disabled");
						} else {
							datatd.prop("disabled", true).addClass("datatd-disabled");
						}
					}
				});
				$(".navButton").hide();
			} else {
				$(".DataTD").children().prop("disabled", false).removeClass("datatd-disabled");
				$(".navButton").show();
			}
		},
		resize:function(width, index) {
			var cols = $("#<?php echo $jqgrid?>").jqGrid('getGridParam', 'colModel');
			var colname = cols[index]['name'];
			$.post("<?php echo $resizeurl?>", {t:"<?php echo $table?>", f:colname, w:width, o:$("#content").width()});
		}
	}
	$("#<?php echo $jqgrid?>").data('lastsel', "");
	$("#<?php echo $jqgrid?>").jqGrid({
		<?php echo $parm;?>
	});
	$("#<?php echo $jqgrid?>").jqGrid(
			'navGrid',
			'#<?php echo $jqpager?>',
			{<?php echo $prmenable?>},
			{<?php echo $prmedit?>}, //edit
			{<?php echo $prmadd?>},	//add
			{<?php echo $prmdel?>},	//del
			{<?php echo $prmsearch?>}	//search
	);
	$("#<?php echo $jqgrid?>").on('jqGridAddEditAfterSelectUrlComplete', function(jq, elm){
		select2_init(elm);
	});
	<?php if ($group) {	?>
	$("#<?php echo $jqgrid?>").jqGrid('navGrid',"#<?php echo $jqpager?>").jqGrid('navButtonAdd',"#<?php echo $jqpager?>",
	{caption:"显示子组数据",
	 buttonicon:"ui-icon-cancel",
	 onClickButton: function(obj){
		$(obj.currentTarget).find("span").toggleClass("ui-icon-check");
		$("#<?php echo $jqgrid?>").trigger("reloadGrid");
	 },
	 title:"显示子组数据",
	 id:"subgroup_<?php echo $jqgrid?>"});
	 $("#<?php echo $jqgid?>_s").change(function(){
		 <?php echo $jqgid?> = $("#<?php echo $jqgid?>_s").val();
		 $("#<?php echo $jqgrid?>").trigger("reloadGrid");
	 });
	<?php }?>
});

</script>
