<?php


namespace common\widgets;
use common\models\Menu;
use yii\base\Widget;



class SidemenuWidget extends Widget
{
    public $site;
    public $menuName = 'sideMenu';
    public $formfactor;
    public $data;
    public $tree;
    public $menuFinal;
    public $currentItem;
    public $parentItem;
    public $model;
    protected $collapseId;

    public function init()
    {
        parent::init();
        if ($this->formfactor === null) {
            $this->formfactor = 'html';
        }
        $this->formfactor .= '.php';
    }
    public function run()
    {
        $this->data = Menu::find()
            ->where(['site'=>$this->site, 'menu_name'=>$this->menuName])
            ->indexBy('id')
            ->asArray()
            ->all();
        $this->collapseId = 1;
        $this->parentItem =  isset($this->data[$this->currentItem]['parent_id'])?$this->data[$this->currentItem]['parent_id']:0;
        $this->tree = $this->getTree();
            return  $this->getMenuHtml($this->tree);
    }

    protected function getTree()
    {
        $tree = [];
        foreach ($this->data as $id => &$value) {
            if (empty($value['parent_id'])) {
                $tree[$id] = &$value;
            } else {
                $this->data[$value['parent_id']]['childs'][$value['id']] = &$value;
            }
        }

        return $tree;
    }
//    protected function currentItem($id)
//    {
//        $data =[];
//        $data[]=$id;
//    }
    protected function getMenuHtml($tree, $tab='',$menulevel='0')
    {
        $start = '<ul  class="list-unstyled" >';
        $end = '</ul>';
        $str = '';
        foreach ($tree as $item ) {
            $str .=$this->itemToTemplate($item, $tab, $menulevel);
        }
        return $start.$str.$end;
    }
    protected function itemToTemplate($item, $tab, $menulevel)
    {
        ob_start();
        include __DIR__ . '/menu_formfactor/'. $this->formfactor;
        return ob_get_clean();
    }
}