<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="language" content="zh_CN" />

<title><?php echo $title?></title>
<script>
	var SITEURL = "<?php echo $siteurl ?>";
	var USERNAME = "<?php echo $username ?>";
	var xui_ini = {
			appPath:"<?php echo $appPath ?>"
	}
	var MENUS = '<?php echo $menus ?>';
</script>


	
</head>
<body>
<div id='loading' style="top:0;left:0; right:0; bottom:0;position:absolute;margin:auto">
<img style="top:0;left:0; right:0; bottom:0;position:absolute;margin:auto" src="<?php echo $appPath ?>loading.gif" alt="Loading..." />
</div>
<?php echo $css;?>
<?php echo $js;?>
</body>
</html>
