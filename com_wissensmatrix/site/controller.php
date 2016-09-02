<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Wissensmatrix Component Controller
 */
class WissensmatrixController extends JControllerLegacy
{
	protected $default_view = 'workers';

	public function display($cachable = false, $urlparams = false)
	{
		$cachable = JFactory::getUser()->get('id') ? false : true;

		$safeurlparams = array('id' => 'INT', 'limit' => 'INT', 'limitstart' => 'INT', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'lang' => 'CMD', 'filter-search' => 'STRING', 'return' => 'BASE64', 'Itemid' => 'INT', 'catid' => 'INT');

		// Add additional models
		$viewName = $this->input->get('view', $this->default_view);
		$format   = $this->input->get('format', 'html');
		switch ($viewName)
		{
			case 'worker':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('wbis'));
				$view->setModel($this->getModel('fwis'));
				break;
			case 'userreport':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('worker'), true);
				$view->setModel($this->getModel('wbis'));
				$view->setModel($this->getModel('fwis'));
				break;
			case 'reportfwiglevelssummary':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('fwigs'), true);
				$view->setModel($this->getModel('fwis'));
				break;
			case 'reportfwigdiffsummary':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('fwigs'), true);
				break;
			case 'reportsfwigs':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('fwigs'), true);
				break;
			case 'reportfwigteam':
			case 'reportfwiglevels':
			case 'reportfwigdiff':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('fwis'), true);
				$view->setModel($this->getModel('workers'));
				break;
			case 'reportfwiteam':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('fwi'), true);
				$view->setModel($this->getModel('workers'));
				break;
			case 'reportresponsiblesfwi':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('workers'), true);
				break;
			case 'reportsfwis':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('fwis'), true);
				$view->setModel($this->getModel('fwigs'));
				break;
			case 'reportswbigs':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('wbigs'), true);
				break;
			case 'reportswbis':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('wbis'), true);
				$view->setModel($this->getModel('wbigs'));
				break;
			case 'reportwbigsummary':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('wbigs'), true);
				$view->setModel($this->getModel('wbis'));
				$view->setModel($this->getModel('workers'));
				break;
			case 'reportwbigteam':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('wbig'), true);
				$view->setModel($this->getModel('wbis'));
				$view->setModel($this->getModel('workers'));
				break;
			case 'reportwbiteam':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('wbi'), true);
				$view->setModel($this->getModel('workers'));
				break;
			case 'zfwigform':
			case 'listfwis':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('fwis'), true);
				break;
			case 'listwbis':
				$viewLayout = $this->input->get('layout', 'default');
				$view       = $this->getView($viewName, $format, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
				$view->setModel($this->getModel('wbis'), true);
				break;
		}

		return parent::display($cachable, $safeurlparams);
	}
}