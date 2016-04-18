<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?></title>
<?php echo $css;?>

<?php echo $js;?>
<script>
	var baseurl = "<?php echo $baseurl;?>";
</script>
</head>
<body style="min-width: 1280px;">
<?php echo $content ?>
</body>
</html>