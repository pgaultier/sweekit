<?php
/**
 * CLinkPager class file.
*
* @author Qiang Xue <qiang.xue@gmail.com>
* @link http://www.sweelix.net
* @copyright 2010-2013 Sweelix
* @license http://www.sweelix.net/license license
*/

/**
 * CLinkPager displays a list of hyperlinks that lead to different pages of target.
*
* @author Qiang Xue <qiang.xue@gmail.com>
* @package system.web.widgets.pagers
* @since 1.0
*/
class PagerWidget extends CBasePager
{
	const CSS_FIRST_PAGE='first';
	const CSS_LAST_PAGE='last';
	const CSS_PREVIOUS_PAGE='previous';
	const CSS_NEXT_PAGE='next';
	const CSS_INTERNAL_PAGE='page';
	const CSS_HIDDEN_PAGE='hidden';
	const CSS_SELECTED_PAGE='selected';

	/**
	 * @var string the CSS class for the first page button. Defaults to 'first'.
	 * @since 1.1.11
	 */
	public $firstPageCssClass=self::CSS_FIRST_PAGE;
	/**
	 * @var string the CSS class for the last page button. Defaults to 'last'.
	 * @since 1.1.11
	 */
	public $lastPageCssClass=self::CSS_LAST_PAGE;
	/**
	 * @var string the CSS class for the previous page button. Defaults to 'previous'.
	 * @since 1.1.11
	 */
	public $previousPageCssClass=self::CSS_PREVIOUS_PAGE;
	/**
	 * @var string the CSS class for the next page button. Defaults to 'next'.
	 * @since 1.1.11
	 */
	public $nextPageCssClass=self::CSS_NEXT_PAGE;
	/**
	 * @var string the CSS class for the internal page buttons. Defaults to 'page'.
	 * @since 1.1.11
	 */
	public $internalPageCssClass=self::CSS_INTERNAL_PAGE;
	/**
	 * @var string the CSS class for the hidden page buttons. Defaults to 'hidden'.
	 * @since 1.1.11
	 */
	public $hiddenPageCssClass=self::CSS_HIDDEN_PAGE;
	/**
	 * @var string the CSS class for the selected page buttons. Defaults to 'selected'.
	 * @since 1.1.11
	 */
	public $selectedPageCssClass=self::CSS_SELECTED_PAGE;
	/**
	 * @var integer maximum number of page buttons that can be displayed. Defaults to 10.
	 */
	public $maxButtonCount=10;
	/**
	 * @var string the text label for the next page button. Defaults to 'Next &gt;'.
	 */
	public $nextPageLabel;
	/**
	 * @var string the text label for the previous page button. Defaults to '&lt; Previous'.
	 */
	public $prevPageLabel;
	/**
	 * @var string the text label for the first page button. Defaults to '&lt;&lt; First'.
	 */
	public $firstPageLabel;
	/**
	 * @var string the text label for the last page button. Defaults to 'Last &gt;&gt;'.
	 */
	public $lastPageLabel;
	/**
	 * @var array additional data passed to the view
	 */
	public $viewData;
	/**
	 * @var string name for header view
	 */
	public $headerView;
	/**
	 * @var string name for footer view
	 */
	public $footerView;
	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var array HTML attributes for the pager container tag.
	 */
	public $htmlOptions=array();

	/**
	 * Initializes the pager by setting some default property values.
	*/
	public function init()
	{
		if($this->nextPageLabel===null)
			$this->nextPageLabel=Yii::t('yii','&gt;');
		if($this->prevPageLabel===null)
			$this->prevPageLabel=Yii::t('yii','&lt;');
		if($this->firstPageLabel===null)
			$this->firstPageLabel=Yii::t('yii','&lt;&lt;');
		if($this->lastPageLabel===null)
			$this->lastPageLabel=Yii::t('yii','&gt;&gt;');
		if($this->header===null)
			$this->header=Yii::t('yii','Go to page: ');

		if(!isset($this->htmlOptions['id']))
			$this->htmlOptions['id']=$this->getId();
		if(!isset($this->htmlOptions['class']))
			$this->htmlOptions['class']='yiiPager';
	}

	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run()
	{
		$buttons=$this->createPageButtons();
		if(empty($buttons))
			return;
		$this->renderHeader();
		$this->renderItems($buttons);
		$this->renderFooter();
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
	/**
	 * Renders the data item list.
	 * Each item has access to :
	 *  * $index : index of the element
	 *  * $data : raw data
	 *  * $widget : this widget
	 *
	 * @return void
	 */
	public function renderItems($buttons) {
		$owner=$this->getOwner();
		$viewFile=$owner->getViewFile($this->itemView);
		$j=0;
		$n=count($buttons);
		foreach($buttons as $i=>$button) {
			$data=$this->viewData;
			$data['index']=$i;
			$data['data']=$button;
			$data['widget']=$this;
			$owner->renderFile($viewFile,$data);
			if($j++ < $n-1)
				echo $this->separator;
		}
	}

	/**
	 * Creates the page buttons.
	 * @return array a list of page buttons (in HTML code).
	 */
	protected function createPageButtons()
	{
		if(($pageCount=$this->getPageCount())<=1)
			return array();

		list($beginPage,$endPage)=$this->getPageRange();
		$currentPage=$this->getCurrentPage(false); // currentPage is calculated in getPageRange()
		$buttons=array();

		// first page
		// $buttons[]=$this->createPageButton($this->firstPageLabel,0,$this->firstPageCssClass,$currentPage<=0,false);
		$buttons[] = array('title' => $this->firstPageLabel, 'url'=> $this->createPageUrl(0), 'page' => 0, 'hidden' =>  ($currentPage<=0), 'selected' => false);

		// prev page
		if(($page=$currentPage-1)<0)
			$page=0;
		// $buttons[]=$this->createPageButton($this->prevPageLabel,$page,$this->previousPageCssClass,$currentPage<=0,false);
		$buttons[] = array('title' => $this->prevPageLabel, 'url'=> $this->createPageUrl($page), 'page' => $page, 'hidden' =>  ($currentPage<=0), 'selected' => false);

		// internal pages
		for($i=$beginPage;$i<=$endPage;++$i) {
			// $buttons[]=$this->createPageButton($i+1,$i,$this->internalPageCssClass,false,$i==$currentPage);
			$buttons[] = array('title' => $i+1, 'url'=> $this->createPageUrl($i), 'page' => $i, 'hidden' => false, 'selected' => ($i==$currentPage));
		}

		// next page
		if(($page=$currentPage+1)>=$pageCount-1)
			$page=$pageCount-1;
		// $buttons[]=$this->createPageButton($this->nextPageLabel,$page,$this->nextPageCssClass,$currentPage>=$pageCount-1,false);
		$buttons[] = array('title' => $this->nextPageLabel, 'url'=> $this->createPageUrl($page), 'page' => $page, 'hidden' =>  ($currentPage>=$pageCount-1), 'selected' => false);

		// last page
		// $buttons[]=$this->createPageButton($this->lastPageLabel,$pageCount-1,$this->lastPageCssClass,$currentPage>=$pageCount-1,false);
		$buttons[] = array('title' => $this->lastPageLabel, 'url'=> $this->createPageUrl($pageCount-1), 'page' => $pageCount-1, 'hidden' =>  ($currentPage>=$pageCount-1), 'selected' => false);

		return $buttons;
	}

	/**
	 * Creates a page button.
	 * You may override this method to customize the page buttons.
	 * @param string $label the text label for the button
	 * @param integer $page the page number
	 * @param string $class the CSS class for the page button.
	 * @param boolean $hidden whether this page button is visible
	 * @param boolean $selected whether this page button is selected
	 * @return string the generated button
	 */
	protected function createPageButton($label,$page,$class,$hidden,$selected)
	{
		if($hidden || $selected)
			$class.=' '.($hidden ? $this->hiddenPageCssClass : $this->selectedPageCssClass);
		return '<li class="'.$class.'">'.CHtml::link($label,$this->createPageUrl($page)).'</li>';
	}

	/**
	 * @return array the begin and end pages that need to be displayed.
	 */
	protected function getPageRange()
	{
		$currentPage=$this->getCurrentPage();
		$pageCount=$this->getPageCount();

		$beginPage=max(0, $currentPage-(int)($this->maxButtonCount/2));
		if(($endPage=$beginPage+$this->maxButtonCount-1)>=$pageCount)
		{
			$endPage=$pageCount-1;
			$beginPage=max(0,$endPage-$this->maxButtonCount+1);
		}
		return array($beginPage,$endPage);
	}

}