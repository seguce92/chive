<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo $this->pageTitle; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->getBaseUrl(); ?>/css/style.css" />

<!--[if lte IE 7]>
<link href="css/patches/patch_my_layout.css" rel="stylesheet" type="text/css" />
<![endif]-->

<link rel="shortcut icon" href="<?php echo Yii::app()->baseUrl; ?>/images/favicon.ico">

<script type="text/javascript">
	// Set global javascript variables
	var baseUrl = '<?php echo Yii::app()->baseUrl; ?>';
</script>

<?php Yii::app()->clientScript->registerScript('userSettings', Yii::app()->user->settings->getJsObject(), CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/main.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/jquery.layout.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/jquery.tableForm.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/jquery-ui-1.7.1.custom.min.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/jquery.checkboxTable.js', CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/jquery.form.js', CClientScript::POS_HEAD); ?>


</head>
<body>

  <div id="loading"><?php echo Yii::t('core', 'loading'); ?>...</div>

  <div class="ui-layout-north">
	<div id="header">
		<div id="headerLeft">
			<ul class="breadCrumb">
				<li id="bc_root">
					<a href="<?php echo Yii::app()->baseUrl . '/#schemata'; ?>" style="float:left; margin-right: 5px;">
						<img src="<?php echo Yii::app()->baseUrl . "/images/logo.png"; ?>" />
					</a>
				</li>
				<?php if(isset($_GET['schema'])) { ?>
					<li id="bc_schema">
						<span>&raquo;</span>
						<a class="icon" href="<?php echo Yii::app()->baseUrl ?>/database/<?php echo $_GET['schema'] ?>">
							<com:Icon name="database" size="24" />
							<span><?php echo $_GET['schema']; ?></span>
						</a>
					</li>
				<?php } ?>
				<li id="bc_table" style="display: none;">
					<span>&raquo;</span>
					<a class="icon" href="<?php echo Yii::app()->baseUrl ?>/database/<?php echo $_GET['schema'] ?>">
						<com:Icon name="table" size="24" />
						<span>test</span>
					</a>
				</li>
			</ul>
		</div>
		<div id="headerLogo">
		</div>
		<div id="headerRight">
			<?php $this->widget('application.components.MainMenu',array(
				'items'=>array(
					array('label'=>'Home', 'icon'=>'home', 'url'=>array('/site/index'), 'visible'=>!Yii::app()->user->isGuest),
					array('label'=>'Refresh','icon'=>'refresh', 'url'=>array(), 'htmlOptions'=>array('onclick'=>'return reload();'), 'visible'=>!Yii::app()->user->isGuest),
					array('label'=>'Login', 'url'=>array('/site/login'), 'visible'=>Yii::app()->user->isGuest),
					array('label'=>'Logout', 'icon'=>'logout', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest)
				),
			)); ?>
		</div>
	</div>
  </div>
  <div class="ui-layout-west">

  <div class="basic" id="MainMenu">
  		<div class="sidebarHeader">
			<a class="icon">
				<com:Icon name="table" size="24" text="database.tables" />
				<span><?php echo Yii::t('database', 'tables'); ?></span>
			</a>
		</div>
		<div class="sidebarContent">

			<ul class="list icon">
				<?php foreach(Table::model()->findAll(array('select'=>'TABLE_NAME, TABLE_ROWS', 'condition'=>'TABLE_SCHEMA=:schema', 'params'=>array(':schema'=>$_GET['schema']), 'order'=>'TABLE_NAME ASC')) AS $table) { ?>
					<li class="nowrap">
						<a href="#tables/<?php echo $table->getName(); ?>/<?php echo ($table->getRowCount() ? 'browse' : 'structure'); ?>">
							<?php $this->widget('Icon', array('name'=>'browse', 'size'=>16, 'disabled'=>!$table->getRowCount(), 'title'=>Yii::t('database', 'Xrows', array('{amount}'=>$table->getRowCount() ? $table->getRowCount() : 0)))); ?>
						</a>
						<a href="#tables/<?php echo $table->getName(); ?>/structure"><?php echo $table->getName(); ?></a>
						<a href="#tables/<?php echo $table->getName(); ?>/insert" class="icon10" style="display: none;">
							<?php $this->widget('Icon', array('name'=>'add', 'size'=>10)); ?>
						</a>
					</li>
				<?php } ?>
			</ul>

		</div>
  		<div class="sidebarHeader">
			<a class="icon">
				<com:Icon name="view" size="24" text="database.views" />
				<span><?php echo Yii::t('database', 'views') ?></span>
			</a>
		</div>
		<div class="sidebarContent">
			<ul class="select">
				<?php foreach(View::model()->findAll(array('select'=>'TABLE_NAME','condition'=>'TABLE_SCHEMA=:schema', 'params'=>array(':schema'=>$_GET['schema']), 'order'=>'TABLE_NAME ASC')) AS $table) { ?>
					<li class="nowrap">
						<?php echo CHtml::openTag('a', array('href'=>'#tables/'.$table->getName().'/browse')); ?>
							<com:Icon name="view" size="16" text="core.username" />
						<?php echo CHtml::closeTag('a'); ?>
						<?php echo CHtml::openTag('a', array('href'=>'#tables/'.$table->getName().'/structure')); ?>
							<span><?php echo $table->getName(); ?></span>
						<?php echo CHtml::closeTag('a'); ?>
					</li>
				<?php } ?>
			</ul>
		</div>
  		<div class="sidebarHeader">
			<a class="icon">
				<com:Icon name="bookmark" size="24" text="core.bookmarks" />
				<span><?php echo Yii::t('core', 'bookmarks') ?></span>
			</a>
		</div>
		<div class="sidebarContent">
			<ul class="select">
				<li>test</li>
			</ul>
		</div>
  		<div class="sidebarHeader">
			<a class="icon">
				<img src="images/icons/table_24.png" />
				<span>Procedures</span>
			</a>
		</div>
		<div class="sidebarContent">
			procedures
		</div>
	</div>
  </div>
  <div class="ui-layout-center" id="content">
  	<?php echo $content; ?>
  </div>

</body>
</html>