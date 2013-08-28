<?php
/**
* SwListView class file.
*
*/

class SwListView extends CWidget {
	/**
	 * @var string enclosing tagname
	 */
	public $tagName;
	/**
	 * @var string name for header view
	 */
	public $headerView;
	/**
	 * @var array html options for enclosing tag
	 */
	public $htmlOptions=array();
	/**
	 * @var string name for header view
	 */
	public $summaryView;
	/**
	 * @var string name for item view
	 */
	public $itemView;
	/**
	 * @var string name for view when we have no elements
	 */
	public $emptyItemView;
	/**
	 * @var boolean hide widget if no items
	 */
	public $hideOnEmpty=false;
	/**
	 * @var string name for footer view
	 */
	public $footerView;
	/**
	 * @var string separator appended between views
	 */
	public $separator="\n";
	/**
	 * @var string name for view when we have no elements
	 */
	public $emptyView;
	/**
	 * @var string template for view ordering
	 */
	public $template="{header}\n{summary}\n{items}\n{pager}\n{footer}";
	/**
	 * @var CDataProvider data provider used to build the list
	 */
	public $dataProvider;
	public $pagination;
	/**
	 * @var array additional data passed to the view
	 */
	public $viewData;

	/**
	 * Initializes the list view.
	 * This method will initialize required property values and instantiate {@link columns} objects.
	 *
	 * @return void
	 */
	public function init() {
		if($this->itemView===null)
			throw new CException(Yii::t('sweelix','The property "itemView" cannot be empty.'));
		parent::init();
		if($this->pagination !== null) {
			$this->dataProvider->setPagination($this->pagination);
		}
		ob_start();
	}

	/**
	 * Render the list view
	 *
	 * @return void
	 */
	public function run() {
		$content = ob_get_contents();
		ob_end_clean();
		if(strlen(trim($content))>0) {
			$this->template = $content;
		}
		if(isset($this->htmlOptions['id']) === true) {
			$this->setId($this->htmlOptions['id']);
		} else {
			$this->htmlOptions['id'] = $this->getId();
		}

		// $this->registerClientScript();
		if(($this->dataProvider->itemCount>0) || ($this->hideOnEmpty === false)) {
			if($this->tagName !==null) {
				echo CHtml::openTag($this->tagName, $this->htmlOptions);
			}
			$this->renderContent();
			if($this->tagName !==null) {
				echo CHtml::closeTag($this->tagName);
			}
		}
	}

	/**
	 * Render the widget enclosed by selected tagName
	 *
	 * @return void
	 */
	public function renderContent() {
		ob_start();
		echo preg_replace_callback("/{(\w+)}/",array($this,'renderSection'),$this->template);
		ob_end_flush();
	}

	/**
	 * Render each section based on template stuff
	 * and produce the html
	 *
	 * @return string
	 */
	protected function renderSection($matches) {
		$method='render'.$matches[1];
		$html = $matches[0];
		if(method_exists($this,$method)) {
			$this->$method();
			$html=ob_get_contents();
			ob_clean();
		}
		return $html;
	}



	/**
	 * Registers necessary client scripts.
	 *
	 * @return void
	 */
	public function registerClientScript() {
	}


	/**
	 * Renders the data item list.
	 * Each item has access to :
	 *  * $index : index of the element
	 *  * $data : raw data
	 *  * $widget : this widget
	 *
	 * @return void
	 */
	public function renderItems() {
		$data=$this->dataProvider->getData();
		if(($n=count($data))>0) {
			$owner=$this->getOwner();
			$viewFile=$owner->getViewFile($this->itemView);
			$j=0;
			foreach($data as $i=>$item) {
				$data=$this->viewData;
				$data['index']=$i;
				$data['data']=$item;
				$data['widget']=$this;
				$owner->renderFile($viewFile,$data);
				if($j++ < $n-1)
					echo $this->separator;
			}
		} else {
			$this->renderEmptyItem();
		}
	}

	/**
	 * Renders the header of the item list.
	 * Header has access to :
	 *  * $widget : this widget
	 *
	 * @return void
	 */
	public function renderHeader() {
		if($this->headerView !== null) {
			$owner=$this->getOwner();
			$viewFile=$owner->getViewFile($this->headerView);
			$data=$this->viewData;
			$data['widget']=$this;
			$owner->renderFile($viewFile,$data);
		}
	}

	/**
	 * Renders the footer of the item list.
	 * Header has access to :
	 *  * $widget : this widget
	 *
	 * @return void
	 */
	public function renderFooter() {
		if($this->footerView !== null) {
			$owner=$this->getOwner();
			$viewFile=$owner->getViewFile($this->footerView);
			$data=$this->viewData;
			$data['widget']=$this;
			$owner->renderFile($viewFile,$data);
		}
	}

	public function renderPager() {

	}

	public function renderSummary() {
		if($this->summaryView !== null) {
			$owner=$this->getOwner();
			$viewFile=$owner->getViewFile($this->summaryView);
			$data=$this->viewData;
			$data['widget']=$this;
			$owner->renderFile($viewFile,$data);
		}
	}

	/**
	 * Renders the empty item when nothing is found
	 * EmptyItem has access to :
	 *  * $widget : this widget
	 *
	 * @return void
	 */
	public function renderEmptyItem() {
		if($this->emptyItemView !== null) {
			$owner=$this->getOwner();
			$viewFile=$owner->getViewFile($this->emptyView);
			$data=$this->viewData;
			$data['widget']=$this;
			$owner->renderFile($viewFile,$data);
		}
	}
}