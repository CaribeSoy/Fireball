<?php
namespace cms\acp\form;

use cms\data\content\ContentAction;
use cms\data\content\ContentCache;
use cms\data\content\ContentEditor;
use cms\data\content\ContentNodeTree;
use cms\data\page\Page;
use cms\data\page\PageAction;
use cms\data\page\PageNodeTree;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\poll\PollManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the content add form.
 * 
 * @author	Jens Krumsieck, Florian Frantzen
 * @copyright	2014 - 2015 codeQuake
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl-3.0.txt>
 * @package	de.codequake.cms
 */
class ContentAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'cms.acp.menu.link.cms.page.list';

	/**
	 * content data
	 * @var	array<mixed>
	 */
	public $contentData = array();

	/**
	 * list of contents
	 * @var	\RecursiveIteratorIterator
	 */
	public $contentList = null;

	/**
	 * css classes of the content
	 * @var	string
	 */
	public $cssClasses = '';

	/**
	 * css id of the content
	 * @var	string
	 */
	public $cssID = '';

	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.cms.content.canAddContent');

	/**
	 * content object type
	 * @var	\wcf\data\object\type\ObjectType
	 */
	public $objectType = null;

	/**
	 * id of the page the cotent will be assigned to
	 * @var	integer
	 */
	public $pageID = 0;

	/**
	 * id of the parent content
	 * @var	integer
	 */
	public $parentID = null;

	/**
	 * position of the new content ('body' or 'sidebar')
	 * @var	string
	 */
	public $position = 'body';

	/**
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;

	/**
	 * content title
	 * @var	string
	 */
	public $title = '';

	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if ($this->objectType === null) {
			if (isset($_REQUEST['objectType'])) $this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('de.codequake.cms.content.type', $_REQUEST['objectType']);
			if ($this->objectType === null || !$this->objectType->getProcessor()->isAvailableToAdd()) {
				throw new IllegalLinkException();
			}
		}

		// form values that can be specified as get parameters
		if (isset($_REQUEST['pageID'])) $this->pageID = intval($_REQUEST['pageID']);
		if (isset($_REQUEST['position'])) $this->position = StringUtil::trim($_REQUEST['position']);
		if (isset($_REQUEST['parentID'])) $this->parentID = intval($_REQUEST['parentID']);

		// register i18n-values
		I18nHandler::getInstance()->register('title');

		// read object type specific parameters
		$this->objectType->getProcessor()->readParameters();
	}

	/**
	 * @see	\wcf\page\IPage::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		I18nHandler::getInstance()->readValues();

		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = StringUtil::trim(I18nHandler::getInstance()->getValue('title'));
		if (isset($_POST['cssID'])) $this->cssID = StringUtil::trim($_POST['cssID']);
		if (isset($_POST['cssClasses'])) $this->cssClasses = StringUtil::trim($_POST['cssClasses']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['contentData']) && is_array($_POST['contentData'])) $this->contentData = $_POST['contentData'];

		foreach ($this->objectType->getProcessor()->multilingualFields as $field) {
			if (I18nHandler::getInstance()->isPlainValue($field)) $this->contentData[$field] = StringUtil::trim(I18nHandler::getInstance()->getValue($field));
		}

		// read object type specific form parameters
		$this->objectType->getProcessor()->readFormParameters();
	}

	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();

		$this->objectType->getProcessor()->validate($this->contentData);

		// validate position
		if (!in_array($this->position, array('body', 'sidebar'))) {
			throw new UserInputException('position');
		}
		if ($this->position == 'sidebar' && !$this->objectType->allowsidebar) {
			throw new UserInputException('position');
		}
		if ($this->position == 'body' && !$this->objectType->allowcontent) {
			throw new UserInputException('position');
		}

		// validate show order
		if ($this->showOrder == 0) {
			$childIDs = ContentCache::getInstance()->getChildIDs($this->parentID ?: null);
			if (!empty($childIDs)) {
				$showOrders = array();
				foreach ($childIDs as $childID) {
					$content = ContentCache::getInstance()->getContent($childID);
					$showOrders[] = $content->showOrder;
				}
				array_unique($showOrders);
				if (isset($this->contentID)) {
					$content = ContentCache::getInstance()->getContent($this->contentID);
					if ($content->showOrder == max($showOrders) && max($showOrders) != 0) $this->showOrder = max($showOrders);
					else $this->showOrder = intval(max($showOrders) + 1);
				}
				else
					$this->showOrder = intval(max($showOrders) + 1);
			}
			else
				$this->showOrder = 1;
		}

		if (!I18nHandler::getInstance()->validateValue('title')) {
			if (I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title');
			}
			else {
				throw new UserInputException('title', 'multilingual');
			}
		}

		$page = new Page($this->pageID);
		if (!$page->pageID) {
			throw new UserInputException('pageID', 'invalid');
		}

		// validate if parent is tabmenu, fallback to a cssID
		if ($this->parentID) {
			$parent = ContentCache::getInstance()->getContent($this->parentID);
			if ($parent->contentTypeID == ObjectTypeCache::getInstance()->getObjectTypeIDByName('de.codequake.cms.content.type', 'de.codequake.cms.content.type.tabmenu')) {
				if ($this->cssID == '') $this->cssID = 'tab-' . $this->pageID . $this->parentID . $this->showOrder . $this->objectType->objectTypeID . rand(0, 20);
			}
		}
	}

	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();

		$data = array(
			'title' => $this->title,
			'pageID' => $this->pageID,
			'parentID' => ($this->parentID) ?  : null,
			'cssID' => $this->cssID,
			'cssClasses' => $this->cssClasses,
			'showOrder' => $this->showOrder,
			'position' => $this->position,
			'contentData' => serialize($this->contentData),
			'contentTypeID' => $this->objectType->objectTypeID
		);

		$this->objectAction = new ContentAction(array(), 'create', array(
			'data' => $data
		));
		$returnValues = $this->objectAction->executeAction();

		$contentID = $returnValues['returnValues']->contentID;
		$contentData = @unserialize($returnValues['returnValues']->contentData);
		$update = array();

		// save polls
		if ($this->objectType->objectType == 'de.codequake.cms.content.type.poll') {
			$pollID = PollManager::getInstance()->save($returnValues['returnValues']->contentID);
			if ($pollID) {
				$contentData['pollID'] = $pollID;
			}
		}

		if (!I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->save('title', 'cms.content.title' . $contentID, 'cms.content', PACKAGE_ID);
			$update['title'] = 'cms.content.title' . $contentID;
		}

		foreach ($this->objectType->getProcessor()->multilingualFields as $field) {
			if (!I18nHandler::getInstance()->isPlainValue($field)) {
				I18nHandler::getInstance()->save($field, 'cms.content.' . $field . $contentID, 'cms.content', PACKAGE_ID);
				$contentData[$field] = 'cms.content.' . $field . $contentID;
			}
		}

		$update['contentData'] = serialize($contentData);

		if (!empty($update)) {
			$editor = new ContentEditor($returnValues['returnValues']);
			$editor->update($update);
		}

		//create revision
		$objectAction = new ContentAction(array($returnValues['returnValues']->contentID), 'createRevision', array(
			'action' => 'create'
		));
		$objectAction->executeAction();

		// update search index
		$objectAction = new PageAction(array($returnValues['returnValues']->pageID), 'refreshSearchIndex');
		$objectAction->executeAction();

		$this->saved();
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('ContentList', array(
			'application' => 'cms',
			'pageID' => $this->pageID
		)));
	}

	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();

		// read page list
		$pageNodeTree = new PageNodeTree();
		$this->pageList = $pageNodeTree->getIterator();

		// read content list
		$contentNodeTree = new ContentNodeTree(null, 0, 1);
		$this->contentList = $contentNodeTree->getIterator();
	}

	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		I18nHandler::getInstance()->assignVariables();

		if ($this->objectType->objectType == 'de.codequake.cms.content.type.poll') {
			PollManager::getInstance()->assignVariables();
		}

		WCF::getTPL()->assign(array(
			'action' => 'add',
			'contentData' => $this->contentData,
			'contentList' => $this->contentList,
			'cssClasses' => $this->cssClasses,
			'cssID' => $this->cssID,
			'objectType' => $this->objectType,
			'pageID' => $this->pageID,
			'pageList' => $this->pageList,
			'parentID' => $this->parentID,
			'position' => $this->position,
			'showOrder' => $this->showOrder
		));
	}
}
