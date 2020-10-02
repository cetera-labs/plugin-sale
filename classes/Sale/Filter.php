<?php
namespace Sale;

class Filter {
	
	use \Cetera\DbConnection;
	
	const TYPE_NUMERIC        = 1;
	const TYPE_NUMERIC_SLIDER = 2;
	const TYPE_CHECKBOX       = 3;
	const TYPE_RADIO          = 4;
	const TYPE_DROPDOWN       = 5;
	
	protected $objectDefinition;
	protected $offersObjectDefinition;
	protected $catalog;
	protected $info = null;
	protected $data = null;
	protected $where = null;
	protected $active = false;
	protected $a;
	public $name = false;
	
	protected static $instances = array();
		
	public static function get($name, $objectDefinition, $catalog = null)
	{
		if (!is_a($catalog, '\\Cetera\\Catalog'))
		{
			if ($catalog) 
				$catalog = \Cetera\Catalog::getById($catalog);
				else $catalog = \Cetera\Catalog::getRoot();
		}
		
		if (!is_a($objectDefinition, '\\Cetera\\ObjectDefinition'))
		{
			$objectDefinition = \Cetera\ObjectDefinition::find($objectDefinition);
		}	
		if (!isset(self::$instances[$name.'_'.$objectDefinition->id.'_'.$catalog->id]))
		{
			self::$instances[$name.'_'.$objectDefinition->id.'_'.$catalog->id] = new self($name, $objectDefinition, $catalog);
		}
		return self::$instances[$name.'_'.$objectDefinition->id.'_'.$catalog->id];
	}

	protected function __construct($name, $objectDefinition, $catalog)
	{
		$this->catalog = $catalog;
		$this->objectDefinition = $objectDefinition;
		$this->name = $name;	
		$this->a = \Cetera\Application::getInstance();
		$this->offersObjectDefinition = Offer::getObjectDefinition();
	}
	
	public function submittedValue($name)
	{
		return (isset($_REQUEST[ $this->name ][$name]) && $_REQUEST[ $this->name ][$name])?$_REQUEST[ $this->name ][$name]:null;
	}
	
	public function getQueryString()
	{
		return http_build_query(array($this->name => $_REQUEST[ $this->name ]));
	}
	
	public function isActive()
	{
		$this->getInfo();
		return $this->active;
	}	
	
	public function getInfo()
	{		
		if (!$this->info)
		{		
			$this->active = false;
			
			$sql = ' FROM '.$this->objectDefinition->getTable().' main LEFT JOIN '.$this->offersObjectDefinition->getTable().' offer ON (main.id = offer.product) WHERE main.idcat IN ('.implode(',',$this->catalog->getSubs()).')';
			
			$data = self::getDbConnection()->fetchAll('SELECT A.*, B.* FROM sale_filter A LEFT JOIN types_fields B ON (A.field_id = B.field_id) WHERE catalog_id IN (0,?) ORDER BY sort, A.field_id, catalog_id', array( $this->catalog->id ));
			$this->info = [];

			foreach ($data as $d) {		
				
				try {
					$od = \Cetera\ObjectDefinition::getById( $d['id'] );
					$field = $od->getField($d['name']);
					$d['field'] = $field;
				}
				catch (\Exception $e) {
					continue;
				}
				
				$d['describ'] = $this->a->decodeLocaleString( $d['describ'] );
								
				$d['iterator'] = [];
				$d['submitted'] = false;
				           
				if (is_subclass_of($d['field'], '\\Cetera\\ObjectFieldLinkAbstract')) {	
					try {
						$f = $this->generateField($d['name']);
						$d['iterator'] = $d['field']->getIterator();//->where('id IN (SELECT '.$f.$sql.')');
					} 
					catch (\Exception $e) {
						continue;
					}
					$d['value'] = $this->submittedValue($d['name']);
					if ($d['value']) {
						$this->active = true;
						$d['submitted'] = true;
					}
				}
				elseif ($field instanceof \Cetera\ObjectField) {
					if (
							($field['type'] == FIELD_INTEGER || $field['type'] == FIELD_DOUBLE)
							&&
							($d['filter_type'] == self::TYPE_NUMERIC_SLIDER || $d['filter_type'] == self::TYPE_NUMERIC)
					   )
					{
						$f = $this->generateField($d['name']);
						$min_max = self::getDbConnection()->fetchArray('SELECT MIN('.$f.'), MAX('.$f.') '.$sql);
						if ($min_max[1] === NULL && $min_max[0] === NULL) continue;
						
						$d['min'] = $min_max[0];
						$d['max'] = $min_max[1];
						if ($d['max'] == $d['min']) $d['max'] += 10;
						
						$d['absolute_min'] = $d['min'];
						$d['absolute_max'] = $d['max'];
						
						$d['value_min'] = $this->submittedValue($d['name'].'_min');
						$d['value_max'] = $this->submittedValue($d['name'].'_max');
						if($d['value_min'] === null) $d['value_min'] = $d['min'];
						if($d['value_max'] === null) $d['value_max'] = $d['max'];
						if ($d['value_min'] != $d['min'] || $d['value_max'] != $d['max'])
						{
							$this->active = true;
							$d['submitted'] = true;
						}
					}
					elseif ($field['type'] == FIELD_BOOLEAN) {
						
						$d['disabled'] = !(boolean)self::getDbConnection()->fetchColumn('SELECT MAX('.$this->generateField($d['name']).') '.$sql);
						
						if ($d['filter_type'] == self::TYPE_RADIO) {
							$d['iterator'] = array(
								array('id' => 0, 'name' => self::t()->_('да') ),
								array('id' => 0, 'name' => self::t()->_('нет') )
							);
						}
						else {
							$d['iterator'] = false;
							$d['filter_type'] = self::TYPE_CHECKBOX;
						}
						$d['value'] = $this->submittedValue($d['name']);
						if ($d['value']) {
							$this->active = true;
							$d['submitted'] = true;
						}
					}
					elseif ($field['type'] == FIELD_LINKSET) {
						$d['value'] = $this->submittedValue($d['name']);
						$d['iterator'] = [];
						foreach ($field->getCatalog()->getMaterials()->select('id','name','1 as disabled')->subFolders()->orderBy('name') as $m) {
							$d['iterator'][$m->id] = array(
								'id'   => $m->id,
								'name' => $m->name,
								'disabled' => true,
							);							
						}
						if ($d['value']) {
							$this->active = true;
							$d['submitted'] = true;
						}						
					}
					elseif ($field['type'] == FIELD_MATSET) {				
						$d['value'] = $this->submittedValue($d['name']);
						$d['iterator'] = [];
						foreach ($field->getObjectDefinition()->getMaterials()->select('id','name','1 as disabled')->orderBy('name') as $m) {
							$d['iterator'][$m->id] = array(
								'id'   => $m->id,
								'name' => $m->name,
								'disabled' => true,
							);							
						}					
						if ($d['value']) {
							$this->active = true;
							$d['submitted'] = true;
						}
						
					}					
					else {	
						$d['iterator'] = [];

						$f = $this->generateField($d['name']);
						$list = $this->applyOffers( $this->catalog->getMaterials() )->subFolders()->select($f.' AS '.$d['name'])->orderBy($f)->groupBy($f, false);
				
						if (!$list->getCountAll()) continue;						
						foreach($list as $m)
						{
							if (!$m->fields[$d['name']]) continue;
							$d['iterator'][$m->fields[$d['name']]] = array(
								'id'   => $m->fields[$d['name']],
								'name' => $m->fields[$d['name']],
								'disabled' => true,
							);
						}
						$d['value'] = $this->submittedValue($d['name']);
						if ($d['value']) {
							$this->active = true;
							$d['submitted'] = true;
						}
					
						if (!count($d['iterator'])) continue;
					}					
				}
				
				$this->info[ $d['field_id'] ] = $d;
			}
		}
		return $this->info;
	}
	
	private function getQuery()
	{
		$query = $this->getDbConnection()->createQueryBuilder();
		$query->from($this->objectDefinition->getTable(), 'main');
		$query->leftJoin('main', $this->offersObjectDefinition->getTable(), 'offer', 'main.id = offer.product');
		$query->where('main.idcat IN ('.implode(',',$this->catalog->getSubs()).')');	
		return $query;
	}

	private function applyQuery($query, $exclude = false)
	{
        return;
		if (!$this->isActive()) return;		
		$r = $this->getWhere();
		foreach ($r['join'] as $j) {
			$field = $this->objectDefinition->getField($j);
			if ($field instanceof \Cetera\ObjectFieldLinkSetAbstract) {
				$query->leftJoin('main', $field->getLinkTable(), $field->name, 'main.id = '.$field->name.'.id');
			}
			$field = $this->offersObjectDefinition->getField($j);
			if ($field instanceof \Cetera\ObjectFieldLinkSetAbstract) {
				$query->leftJoin('offer', $field->getLinkTable(), $field->name, 'offer.id = '.$field->name.'.id');
			}			
		}
		foreach ($r['where'] as $k => $w) {
			if ($k == $exclude) continue;
			$query->andWhere($w);
		}		
	
	}		
	
	public function getData()
	{
		if (!$this->data) {	
			$this->data = $this->getInfo();
			
			//if ($this->isActive()) {
				$r = $this->getWhere();
				$sql = ' FROM '.$this->objectDefinition->getTable().' main LEFT JOIN '.$this->offersObjectDefinition->getTable().' offer ON (main.id = offer.product) WHERE main.idcat IN ('.implode(',',$this->catalog->getSubs()).')';
				
				foreach ($this->data as $k => $d) {
										
					if ($d['field'] instanceof \Cetera\ObjectFieldLink) {
					}
					elseif ($d['field'] instanceof \Cetera\ObjectFieldLinkSet or $d['field'] instanceof \Cetera\ObjectFieldMaterialSet) {
						
						if ($d['field'] instanceof \Cetera\ObjectFieldLinkSet) {
							$list = $d['field']->getCatalog()->getMaterials()->subFolders()->orderBy('name');
						}
						else {
							$list = $d['field']->getObjectDefinition()->getMaterials()->orderBy('name');		
						}
						$list->joinReverse($this->objectDefinition, $d['name'], 1);
						$list->where($d['name'].'.idcat IN ('.implode(',',$this->catalog->getSubs()).')');
						
						foreach ($list as $m) {							
							if (isset($d['iterator'][$m->id])){
								$d['iterator'][$m->id]['disabled'] = false;
							}
						}
						$this->data[$k] = $d;

					}
					elseif ($d['field'] instanceof \Cetera\ObjectField) {		
						if (
								($d['field']['type'] == FIELD_INTEGER || $d['field']['type'] == FIELD_DOUBLE)
								&&
								($d['filter_type'] == self::TYPE_NUMERIC_SLIDER || $d['filter_type'] == self::TYPE_NUMERIC)
						   )
						{
							
						    /*
							$query = $this->getQuery();
							$f = $this->generateField($d['name']);
							$query->select('MIN('.$f.') as min, MAX('.$f.') as max');
							$this->applyQuery($query, $k);
							$row = $query->execute()->fetch();
							
							if ($row['max'] == $row['min']) $row['max'] += 10;
							$d['min'] = $row['min'];
							$d['max'] = $row['max'];
							
							$d['value_min'] = $this->submittedValue($d['name'].'_min');
							$d['value_max'] = $this->submittedValue($d['name'].'_max');
							if($d['value_min'] === null || $d['value_min']>$d['max'] || $d['value_min']<$d['min']) $d['value_min'] = $d['min'];
							if($d['value_max'] === null || $d['value_max']>$d['max'] || $d['value_max']<$d['min']) $d['value_max'] = $d['max'];
							
							$this->data[$k] = $d;
                                                    */
						}
						elseif ($d['field']['type'] == FIELD_BOOLEAN) {

						}					
						else {	

							$query = $this->getQuery();
							$f = $this->generateField($d['name']);
							$query->select($f.' AS value');
							$query->groupBy('value');
							$query->andWhere($f.' IS NOT NULL');
							$this->applyQuery($query, $k);
							$s = $query->execute();
							while ($row = $s->fetch()) {
								if (!$row['value']) continue;
								if (isset($d['iterator'][$row['value']])){
									$d['iterator'][$row['value']]['disabled'] = false;
								}
							}
							
							$this->data[$k] = $d;

						}						
					}
				}
			//}
		}
		return $this->data;
	}
	
	protected function applyOffers(\Cetera\Iterator\Material $iterator)
	{
		$iterator->groupBy('main.id')->getQuery()->leftJoin('main', $this->offersObjectDefinition->getTable(), 'offer', 'main.id = offer.product');
		return $iterator;
	}
	
	/*
	 * применить фильтр к итератору
	 */
	public function apply($iterator)
	{
		if (!$this->isActive()) return;
		$this->applyOffers($iterator);
		
		$r = $this->getWhere();
		
		foreach ($r['join'] as $j) {
			$iterator->join($j);
		}
		foreach ($r['where'] as $w) {
			$iterator->where($w);
		}	
		
	}	
	
	public function getWhere()
	{
		if ($this->where === null) {
			$result = array(
				'where' => [],
				'join'  => []
			);
			
			if ($this->isActive()) {
			
				foreach ($this->getInfo() as $f) {
					switch($f['filter_type']) 
					{
						case self::TYPE_NUMERIC:
						case self::TYPE_NUMERIC_SLIDER:	
							$w = [];
							if ($this->submittedValue($f['name'].'_min') !== null) {
								$w[] = $this->generateField($f['name']).' >= '.(float)$this->submittedValue($f['name'].'_min');
							}
							if ($this->submittedValue($f['name'].'_max') !== null && $this->submittedValue($f['name'].'_max')){
								$w[] =  $this->generateField($f['name']).' <= '.(float)$this->submittedValue($f['name'].'_max');
							}	
							if (count($w)) { 
								$result['where'][$f['field_id']] = implode(' and ', $w);
							}
							break;
						case self::TYPE_RADIO:
						case self::TYPE_DROPDOWN:
							if ($this->submittedValue($f['name']) !== null)
							{
								if ($f['field']['type'] == FIELD_LINKSET) {
									$result['join'][$f['name']] = $f['name'];
									$result['where'][$f['field_id']] = $f['name'].'.dest = '.$this->getDbConnection()->quote($this->submittedValue($f['name']));
								}	
								else {
									$result['where'][$f['field_id']] = $this->generateField($f['name']).' = '.$this->getDbConnection()->quote($this->submittedValue($f['name']));
								}
							}				
							break;
						case self::TYPE_CHECKBOX:
							if ($this->submittedValue($f['name']) !== null)
							{
								
								if (is_array($this->submittedValue($f['name']))) {	
									$v = array();
									foreach ($this->submittedValue($f['name']) as $value => $dummy){
										$v[] = $this->getDbConnection()->quote($value);
									}						
								}
								else {
									$v = array( $this->getDbConnection()->quote($this->submittedValue($f['name'])) );
								}
						
								if ($f['field']['type'] == FIELD_LINKSET || $f['field']['type'] == FIELD_MATSET) {
									$result['join'][$f['name']] = $f['name'];
									$result['where'][$f['field_id']] = $f['name'].'.dest IN ('.implode(',',$v).')';
								}						
								elseif (is_array($this->submittedValue($f['name']))) {
									$result['where'][$f['field_id']] = $this->generateField($f['name']).' IN ('.implode(',',$v).')';
								}
								else {
									if ($this->submittedValue($f['name'])) {
										$result['where'][$f['field_id']] = $this->generateField($f['name']).' > 0';
									}
								}
							}
							break;
					}
				}
			}
			$this->where = $result;
		}
		return $this->where;
	}	
	
	protected function generateField($field)
	{
		if ($this->objectDefinition->hasField($field) && $this->offersObjectDefinition->hasField($field)) {
			return "IF(offer.{$field} IS NULL OR offer.{$field}='',main.{$field}, offer.{$field})";
		}
		elseif ($this->objectDefinition->hasField($field)) {
			return 'main.'.$field;
		}
		elseif ($this->offersObjectDefinition->hasField($field)) {
			return 'offer.'.$field;
		}
	}
	
	/*
	* убрать в трейт \Cetera\Translator
	*/
	public static function t()
	{
		return \Cetera\Application::getInstance()->getTranslator();
	}	
	
	private static function getTrans($type)
	{
		switch ($type)
		{
			case self::TYPE_NUMERIC: 		return self::t()->_('Число от-до');
			case self::TYPE_NUMERIC_SLIDER: return self::t()->_('Число от-до с ползунком');
			case self::TYPE_CHECKBOX: 		return self::t()->_('Флажки');
			case self::TYPE_RADIO: 			return self::t()->_('Радиокнопки');
			case self::TYPE_DROPDOWN: 		return self::t()->_('Выпадающее меню');
		}		
	}	
	
	public static function getTypes()
	{		
		$res = array(
			array(
				'id'     => self::TYPE_CHECKBOX,
				'name'   => self::getTrans(self::TYPE_CHECKBOX),
				'fields' => array(FIELD_TEXT, FIELD_LINK, FIELD_LINKSET, FIELD_MATSET, FIELD_ENUM, FIELD_BOOLEAN, FIELD_INTEGER),
			),
			array(
				'id'     => self::TYPE_RADIO,
				'name'   => self::getTrans(self::TYPE_RADIO),
				'fields' => array(FIELD_TEXT, FIELD_LINK, FIELD_LINKSET, FIELD_MATSET, FIELD_ENUM, FIELD_BOOLEAN, FIELD_INTEGER),
			),
			array(
				'id'     => self::TYPE_DROPDOWN,
				'name'   => self::getTrans(self::TYPE_DROPDOWN),
				'fields' => array(FIELD_TEXT, FIELD_LINK, FIELD_LINKSET, FIELD_MATSET, FIELD_ENUM, FIELD_BOOLEAN, FIELD_INTEGER),
			),
			array(
				'id'     => self::TYPE_NUMERIC,
				'name'   => self::getTrans(self::TYPE_NUMERIC),
				'fields' => array(FIELD_INTEGER, FIELD_DOUBLE),
			),
			array(
				'id'     => self::TYPE_NUMERIC_SLIDER,
				'name'   => self::getTrans(self::TYPE_NUMERIC_SLIDER),
				'fields' => array(FIELD_INTEGER, FIELD_DOUBLE),
			),
		);
		return $res;
	}
	
	public function getFields()
	{		
		$data = self::getDbConnection()->fetchAll('
			SELECT A.*, B.type as field_type, D.describ as type_name, B.describ as field_name, C.name as catalog_name 
			FROM sale_filter A 
			LEFT JOIN types_fields B ON (A.field_id = B.field_id) 
			LEFT JOIN types D ON (B.id = D.id)
			LEFT JOIN dir_data C ON (A.catalog_id = C.id) 
			ORDER BY sort, A.field_id, catalog_id');
		
		foreach ($data as $id => $value)
		{
			$data[$id]['field_name'] = $this->a->decodeLocaleString( $data[$id]['type_name'] ) .' - '. $this->a->decodeLocaleString( $data[$id]['field_name'] );
			$data[$id]['filter_type_name'] = $this->getTrans( $value['filter_type'] );
		}
		return $data;
	}

	public function deleteField($id)
	{
		self::getDbConnection()->delete('sale_filter', array('id' => $id));
	}

	public function addField($data)
	{
		self::getDbConnection()->insert('sale_filter', array(
			'sort'        => $data['sort'],
			'catalog_id'  => $data['catalog_id'],
			'field_id'    => $data['field_id'],
			'filter_type' => $data['filter_type'],
		));
	}	
	
	public function updateField($data)
	{
		self::getDbConnection()->update('sale_filter', array(
			'sort'        => $data['sort'],
			'catalog_id'  => $data['catalog_id'],
			'field_id'    => $data['field_id'],
			'filter_type' => $data['filter_type'],
		),array(
			'id' => $data['id']
		));
	}	

}