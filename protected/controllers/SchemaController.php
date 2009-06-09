<?php

class SchemaController extends Controller
{
	const PAGE_SIZE = 10;

	/**
	 * @var string specifies the default action to be 'list'.
	 */
	public $defaultAction = 'list';

	/**
	 * @var CActiveRecord the currently loaded data model instance.
	 */
	private $_schema;
	public $schema;

	/**
	 * @var Default layout for this controller
	 */
	public $layout = 'schema';

	public function __construct($id, $module=null)
	{
		$request = Yii::app()->getRequest();
		$this->schema = $request->getParam('schema');

		if($request->isAjaxRequest && $this->schema && $request->pathInfo != 'schemata/update')
		{
			$this->layout = '_schema';
		}
		elseif($request->isAjaxRequest)
		{
			$this->layout = false;
		}

		parent::__construct($id, $module);
		$this->connectDb($this->schema);
	}

	/**
	 * Shows schema index.
	 *
	 * Currently redirects to table list (via Ajax).
	 */
	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * Lists all tables.
	 */
	public function actionTables()
	{
		$schema = $this->loadSchema();

		$criteria = new CDbCriteria;
		$criteria->condition = 'TABLE_SCHEMA = :schema AND TABLE_TYPE IN (\'BASE TABLE\', \'SYSTEM VIEW\')';
		$criteria->params = array(
			':schema' => $this->schema,
		);

		// Sort
		$sort = new CSort('Table');
		$sort->attributes = array(
			'TABLE_NAME' => 'name',
			'TABLE_ROWS' => 'rows',
			'TABLE_COLLATION' => 'collation',
			'ENGINE' => 'engine',
			'DATA_LENGTH' => 'datalength',
			'DATA_FREE' => 'datafree',
		);
		$sort->route = '#tables';
		$sort->applyOrder($criteria);

		$schema->tables = Table::model()->findAll($criteria);

		$this->render('tables', array(
			'schema' => $schema,
			'tableCount' => count($schema->tables),
			'sort' => $sort,
		));
	}

	/**
	 * Lists all views.
	 */
	public function actionViews()
	{
		$schema = $this->loadSchema();

		$criteria = new CDbCriteria;
		$criteria->condition = 'TABLE_SCHEMA = :schema';
		$criteria->params = array(
			':schema' => $this->schema,
		);

		// Sort
		$sort = new CSort('View');
		$sort->attributes = array(
			'TABLE_NAME' => 'name',
			'IS_UPDATABLE' => 'updatable',
		);
		$sort->route = '#views';
		$sort->applyOrder($criteria);

		$schema->views = View::model()->findAll($criteria);

		$this->render('views', array(
			'schema' => $schema,
			'viewCount' => count($schema->views),
			'sort' => $sort,
		));
	}

	/**
	 * Lists all routines (procedures & functions).
	 */
	public function actionRoutines()
	{
		$schema = $this->loadSchema();

		$criteria = new CDbCriteria;
		$criteria->condition = 'ROUTINE_SCHEMA = :schema';
		$criteria->params = array(
			':schema' => $this->schema,
		);

		// Sort
		$sort = new CSort('View');
		$sort->attributes = array(
			'ROUTINE_NAME' => 'name',
		);
		$sort->route = '#routines';
		$sort->applyOrder($criteria);

		$schema->routines = Routine::model()->findAll($criteria);

		$this->render('routines', array(
			'schema' => $schema,
			'routineCount' => count($schema->routines),
			'sort' => $sort,
		));
	}

	/**
	 * @todo(rponudic): Finish
	 */
	public function actionSql($_query = false, $_execute = true) {

		$db = $this->db;

		$request = Yii::app()->getRequest();
		$query = $_query ? $_query : $request->getParam('query');

		if($query)
		{

			$pages = new CPagination;
			$pages->setPageSize(self::PAGE_SIZE);

			$sort = new Sort($db);
			$sort->multiSort = false;

			$sort->route = '/schema/sql';

			$oSql = new Sql($query);
			$oSql->applyCalculateFoundRows();

			if(!$oSql->hasLimit)
			{
				$offset = (isset($_GET['page']) ? (int)$_GET['page'] : 1) * self::PAGE_SIZE - self::PAGE_SIZE;
				$oSql->applyLimit(self::PAGE_SIZE, $offset, true);
			}

			$oSql->applySort($sort->getOrder(), true);

			$query = $oSql->getOriginalQuery();

			if($_execute)
			{

				$cmd = $db->createCommand($oSql->getQuery());

				try
				{
					// Fetch data
					$data = $cmd->queryAll();

					$total = (int)$db->createCommand('SELECT FOUND_ROWS()')->queryScalar();
					$pages->setItemCount($total);

					$columns = array();

					// Fetch column headers
					if($total > 0)
					{
						$columns = array_keys($data[0]);
					}
				}
				catch (Exception $ex)
				{
					$error = $ex->getMessage();
				}

			}

		}

		$this->render('sql', array(
			'data' => $data,
			'columns' => $columns,
			'query' => $query,
			'pages' => $pages,
			'sort' => $sort,
			'error' => $error,
		));

	}

	/**
	 * Create a new schema.
	 */
	public function actionCreate()
	{
		$schema = new Schema;
		if(isset($_POST['Schema']))
		{
			$schema->attributes = $_POST['Schema'];
			if($sql = $schema->save())
			{
				$response = new AjaxResponse();
				$response->addNotification('success',
					Yii::t('message', 'successAddSchema', array('{schema}' => $schema->SCHEMA_NAME)),
					null,
					$sql);
				$response->reload = true;
				$response->send();
			}
		}

		$collations = Collation::model()->findAll(array(
			'order' => 'COLLATION_NAME',
			'select'=> 'COLLATION_NAME, CHARACTER_SET_NAME AS collationGroup'
		));

		$this->render('form', array(
			'schema' => $schema,
			'collations' => $collations,
		));
	}

	/**
	 * Update a schema.
	 *
	 * @todo(mburtscher): Renaming. Requires copying the whole schema.
	 */
	public function actionUpdate()
	{
		$isSubmitted = false;
		$sql = null;
		$schema = $this->loadSchema();
		if(isset($_POST['Schema']))
		{
			$schema->attributes = $_POST['Schema'];
			if($sql = $schema->save())
			{
				$isSubmitted = true;
			}
		}

		$collations = Collation::model()->findAll(array(
			'order' => 'COLLATION_NAME',
			'select'=>'COLLATION_NAME, CHARACTER_SET_NAME AS collationGroup'
		));

		$this->render('form', array(
			'schema' => $schema,
			'collations' => $collations,
			'isSubmitted' => $isSubmitted,
			'sql' => $sql,
		));
	}

	/**
	 * Drop a schema.
	 */
	public function actionDrop()
	{
		$response = new AjaxResponse();
		$response->reload = true;
		$schemata = (array)$_POST['schemata'];
		$droppedSchemata = $droppedSqls = array();

		foreach($schemata AS $schema)
		{
			$schemaObj = Schema::model()->findByPk($schema);
			try
			{
				$sql = $schemaObj->delete();
				$droppedSchemata[] = $schema;
				$droppedSqls[] = $sql;
			}
			catch(DbException $ex)
			{
				$response->addNotification('error',
					Yii::t('message', 'errorDropSchema', array('{schema}' => $schema)),
					$ex->getText(),
					$ex->getSql());
			}
		}

		$count = count($droppedSchemata);
		if($count > 0)
		{
			$response->addNotification('success',
				Yii::t('message', 'successDropSchema', array($count, '{schema}' => $droppedSchemata[0], '{schemaCount}' => $count)),
				($count > 1 ? implode(', ', $droppedSchemata) : null),
				implode("\n", $droppedSqls));
		}

		$response->send();
	}

	/**
	 * Lists all schemata.
	 */
	public function actionList()
	{
		$criteria = new CDbCriteria();

		// Pagination
		$pages = new CPagination(Schema::model()->count($criteria));
		$pages->pageSize = self::PAGE_SIZE;
		$pages->applyLimit($criteria);
		$pages->route = '#schemata';

		// Sort
		$sort = new CSort('Schema');
		$sort->attributes = array(
			'SCHEMA_NAME' => 'name',
			'tableCount' => 'tableCount',
			'DEFAULT_COLLATION_NAME' => 'collation',
		);
		$sort->defaultOrder = 'SCHEMA_NAME ASC';
		$sort->route = '#schemata';
		$sort->applyOrder($criteria);

		$criteria->group = 'SCHEMA_NAME';
		$criteria->select = 'SCHEMA_NAME, DEFAULT_COLLATION_NAME, COUNT(*) AS tableCount';

		$schemaList = Schema::model()->with(array(
			'tables' => array('select' => 'COUNT(??.TABLE_NAME) AS tableCount'),
		))->together()->findAll($criteria);

		$this->render('list',array(
			'schemaList' => $schemaList,
			'schemaCount' => $pages->getItemCount(),
			'schemaCountThisPage' => min($pages->getPageSize(), $pages->getItemCount() - $pages->getCurrentPage() * $pages->getPageSize()),
			'pages' => $pages,
			'sort' => $sort,
		));
	}

	/**
	 * Loads the current schema.
	 *
	 * @return	Schema
	 */
	public function loadSchema()
	{
		if(is_null($this->_schema))
		{
			$this->_schema = Schema::model()->findByPk($this->schema);

			if(is_null($this->_schema))
			{
				throw new CHttpException(500, 'The requested schema does not exist.');
			}
		}
		return $this->_schema;
	}


	/**
	 * Bookmark actions
	 * @todo(mburtscher): Is this already finished???
	 */
	public function actionShowBookmark()
	{

		$id = Yii::app()->getRequest()->getParam('id');
		$bookmark = Yii::app()->user->settings->get('bookmarks', 'database', $this->schema, 'id', $id);

		self::actionSql($bookmark['query'], false);

	}

	/**
	 * @todo(mburtscher): What's going on here?
	 */
	public function actionExport()
	{


		if(!$_POST['tables'])
		{
			$criteria = new CDbCriteria;
			$criteria->condition = 'TABLE_SCHEMA = :schema';
			$criteria->params = array(
				':schema' => $this->schema,
			);

			$tables = Table::model()->findAll($criteria);

		}
		else
		{



			foreach($_POST['tables'] AS $table)
			{

			}

		}

		$this->render('export', array(
			'tables'=>$tables
		));
	}

	/**
	 * @todo(mburtscher): What's going on here?
	 */
	public function actionImport()
	{

		// This is no ajax - request, so unset layout
		$this->layout = false;

		$file = null;

		// Upload file
		if(isset($_FILES['file']))
		{

			$file = CUploadedFile::getInstanceByName('file');

			#$splitter = new SqlSplitter()

			#$queries = SqlSplitter::

			predie($file);


		}
		$this->render('import', array(
			'file' => $file,
		));

	}

}