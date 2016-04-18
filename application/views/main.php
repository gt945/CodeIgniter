<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="zh_CN" />

<title>Layout with Accordion</title>
<?php echo $css;?>

<?php echo $js;?>
	
<script>
	var baseurl = "<?php echo $baseurl ?>";
</script>
</head>
<body>

	<div class="ui-layout-north ui-widget-content" style="display: none;">
		<span>当前登陆：<?php echo $username?> </span>
		<span><a href="<?php echo $logout?>">退出</a></span>
	</div>

	<div class="ui-layout-south ui-widget-content" style="display: none;">South Pane</div>

	<div id="tabs_div" class="ui-layout-center" style="display: none;">
		<ul class="ui-tabs-nav" style="-moz-border-radius-bottomleft: 0; -moz-border-radius-bottomright: 0; border-bottom-right-radius: 0px; border-bottom-left-radius: 0px;">
			<li><a href="#tab_main"><span>Main</span></a></li>
		</ul>
		<div id="tab_main">
			<div class="ui-layout-content ui-widget-content ui-corner-bottom" style="border-top: 0; padding-bottom: 1em;">
				<p>lorem ipsum dolor sit amet, consectetur adipiscing elit. vestibulum condimentum neque a velit laoreet dapibus. etiam eleifend tempus pharetra. aliquam vel ante mauris, eget aliquam sapien.
					aenean euismod vulputate quam, eget vehicula lectus placerat eu. class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. curabitur et ipsum orci, at fermentum
					metus. etiam volutpat metus sit amet sapien tincidunt non fermentum velit aliquet. pellentesque malesuada accumsan mi a accumsan. nam commodo lectus non tellus rhoncus in facilisis metus iaculis.
					proin id sapien felis, sit amet pretium dui. suspendisse purus erat, blandit ut mollis elementum, bibendum a leo. curabitur pulvinar arcu quis orci ultricies vestibulum. cras convallis nisi eget
					tortor tristique gravida. nam augue magna, dapibus in luctus ac, tincidunt dapibus tellus. donec massa metus, pretium sit amet pulvinar id, ultrices ac eros. cum sociis natoque penatibus et
					magnis dis parturient montes, nascetur ridiculus mus. maecenas placerat lacus nec tortor feugiat condimentum.</p>

				<p>cras nec arcu sed nisi varius fermentum ut non nulla. pellentesque ultricies condimentum nibh, nec imperdiet felis laoreet sit amet. aenean a molestie tortor. pellentesque habitant morbi
					tristique senectus et netus et malesuada fames ac turpis egestas. praesent enim magna, imperdiet adipiscing tempus nec, molestie id elit. ut varius ante gravida est dignissim sodales. nulla
					consectetur nibh eget metus sodales vulputate. mauris lacinia risus nec ipsum sodales elementum. nunc non tortor turpis. vestibulum a euismod ligula.</p>

				<p>nam non hendrerit augue. nunc sit amet est lectus. morbi non nisl eget dolor rutrum ullamcorper. sed dictum commodo elit sed rutrum. nunc eu massa nulla, at gravida dolor. aenean at interdum
					nisi. integer consequat malesuada urna quis dignissim. duis luctus porta ullamcorper. aliquam tortor nunc, porta vel vestibulum at, egestas id mi. in quis arcu in felis laoreet varius a et
					ligula. sed in magna a orci posuere ullamcorper ultrices ut ante. suspendisse velit enim, venenatis et pharetra sed, mollis ut dui. donec erat eros, dignissim ac ultrices ac, hendrerit a elit.</p>

				<p>lorem ipsum dolor sit amet, consectetur adipiscing elit. vestibulum condimentum neque a velit laoreet dapibus. etiam eleifend tempus pharetra. aliquam vel ante mauris, eget aliquam sapien.
					aenean euismod vulputate quam, eget vehicula lectus placerat eu. class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. curabitur et ipsum orci, at fermentum
					metus. etiam volutpat metus sit amet sapien tincidunt non fermentum velit aliquet. pellentesque malesuada accumsan mi a accumsan. nam commodo lectus non tellus rhoncus in facilisis metus iaculis.
					proin id sapien felis, sit amet pretium dui. suspendisse purus erat, blandit ut mollis elementum, bibendum a leo. curabitur pulvinar arcu quis orci ultricies vestibulum. cras convallis nisi eget
					tortor tristique gravida. nam augue magna, dapibus in luctus ac, tincidunt dapibus tellus. donec massa metus, pretium sit amet pulvinar id, ultrices ac eros. cum sociis natoque penatibus et
					magnis dis parturient montes, nascetur ridiculus mus. maecenas placerat lacus nec tortor feugiat condimentum.</p>

				<p>cras nec arcu sed nisi varius fermentum ut non nulla. pellentesque ultricies condimentum nibh, nec imperdiet felis laoreet sit amet. aenean a molestie tortor. pellentesque habitant morbi
					tristique senectus et netus et malesuada fames ac turpis egestas. praesent enim magna, imperdiet adipiscing tempus nec, molestie id elit. ut varius ante gravida est dignissim sodales. nulla
					consectetur nibh eget metus sodales vulputate. mauris lacinia risus nec ipsum sodales elementum. nunc non tortor turpis. vestibulum a euismod ligula.</p>
			</div>
		</div>
	</div>

	<div class="ui-layout-west" style="display: none;">
		<div id="accordion1" class="basic">
			<?php foreach($menus as $m) {?>
			<h3>
				<a href="#"><?php echo $m['id']; ?></a>
			</h3>
			<div>
				<ul>
					<?php foreach($m['children'] as $m1) {?>
					<li>
						<?php echo "<a href=\"javascript: switch_to_tab('{$m1['id']}', '{$m1['id']}', '{$m1['target']}')\">{$m1['id']}</a>"; ?>
					<?php } ?>
					</li>
				</ul>
			</div>
			<?php } ?>

		</div>
	</div>


</body>
</html>
