<?php
namespace Cetera;
include_once('common_bo.php');
header('Content-type: application/json; charset=UTF-8');

$nodes = [];
$node = $_REQUEST['node'];

$od = ObjectDefinition::findByAlias('sale_products'); 
$only = $od->id;	

if ($node == 'root') {

    $nodes[] = array(
        'text' => 'root',
		'name' => 'root',
        'id'   => 'item-0',
		'item_id'  => 0,
        'iconCls'  => 'tree-folder-visible',
        'qtip' => '',
        'leaf' => FALSE,
        'mtype' => 0,
        'disabled' => true
    );

} else {

    list($dummy, $id) = explode('-',$node);
	
	if ($dummy == 'material') {
		// Торговые предложения
		
		$p = \Sale\Product::getById($id);
		if ($p) {
			foreach ($p->getOffers() as $material) {	
			
					$name = htmlspecialchars('['.$p->id.'-'.$material->id.'] '.$material->name);
					$name = str_replace("\n",'',$name);
					$name = str_replace("\r",'',$name);
					$nodes[] = array(
						'text' => $name,
						'id'   => 'material-'.$p->id.'|'.$material->id.'-'.$material->table.'-'.$material->type,
						'iconCls'  => 'tree-material',
						'qtip' => '',
						'leaf' => true,
						'disabled' => false
					);			
			
			}
		}
		
	}
	else {
	
		$c = Catalog::getById($id);
		if ($c) {
			if (!$c->isLink()) {
				foreach ($c->children as $child) {
					$a = process_child($child);    
					if (is_array($a)) $nodes[] = $a;  
				}  
			}		
		   
			if ($c->prototype->materialsType==$only && $c->prototype->materialsType) {                            
				$where = '';
				if ($_GET['query'])
					$where .= 'name LIKE '.$application->getConn()->quote('%'.$_GET['query'].'%');
				$m = $c->getMaterials()->where($where)->setItemCountPerPage(500);
				
				foreach ($m as $material) {				
					$name = htmlspecialchars('['.$material->id.'] '.$material->name);
					$name = str_replace("\n",'',$name);
					$name = str_replace("\r",'',$name);
					$nodes[] = array(
						'text' => $name,
						'id'   => 'material-'.$material->id.'-'.$material->table.'-'.$material->type,
						'iconCls'  => 'tree-material',
						'qtip' => '',
						'leaf' => !$material->hasOffers(),
						'disabled' => $material->hasOffers()
					);
				}
			}
		}
	
	}
}

echo json_encode($nodes);
    
function process_child($child) {
    global $user, $only;
    
    if (!$user->allowCat(PERM_CAT_VIEW,$child->id)) return FALSE;
    
    $cls = 'tree-folder-visible';
    if ($child instanceof Server) $cls = 'tree-server';
    if ($child->isLink()) $cls = 'tree-folder-link';
    if ($child->hidden) $cls = 'tree-folder-hidden';
	
	try {
		if ($child->materialsType) {
			$od = ObjectDefinition::findById($child->materialsType);
			$mtype_name = $od->getDescriptionDisplay();
		}
		else {
			$mtype_name = '';
		}
	}
	catch (\Exception $e) {
		$mtype_name = '';
	}
	
    return array(
        'text'  => '<span class="tree-alias">'.$child->alias.'</span>'.$child->name,
		'name'  => $child->name, 
        'alias' => $child->alias,
        'id'    => 'item-'.$child->id,
		'item_id' => $child->id,
        'iconCls'=> $cls,
        'qtip'  => $child->describ,
        'leaf'  => FALSE,
        'link'  => (int)$child->isLink(),
		'isServer'  => (int)$child->isServer(),
        'mtype' => $child->materialsType,
        'disabled' => TRUE,
		'date'  => $child->dat,
		'mtype_name' => $mtype_name,
    );
}