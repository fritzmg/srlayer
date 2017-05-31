<?php

/**
 * PHP version 5
 *
 * Class ModuleSRLayer
 *
 * @copyright  sr-tag 2014
 * @author     Sven Rhinow <support@sr-tag.de>
 * @author     Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @package    srlayer
 */
class ModuleSRLayer extends \Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'srl_default';

	/**
	 * Target pages
	 * @var array
	 */
	protected $arrTargets = array();

	/**
	 * Options for JS
	 * @var array
	 */
	protected $optionsArr = array();

	/**
	* show files and layer
	* @var bool
	*/
	protected $show = false;


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### SR-LAYER ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		if($this->srl_template != '') $this->strTemplate = $this->srl_template;

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		global $objPage;

		// test start date
		if(strlen($this->srl_start) && ($this->srl_start > time()))
		{
			return false;
		}

		// test stop date
		if(strlen($this->srl_stop) && ($this->srl_stop < time()))
		{
			return false;
		}

		// show at all?
		$showHtml = false;

		// show right away?
		$showNow = false;

		// array for javascript options
		$optionsArr = array();

		// no GET params necessary?
		if($this->srl_no_param)
		{
			$showHtml = true;
			$showNow = true;
		}

		//Modul-Flag fuer "Layer per Link öffnen" pruefen
		if($this->srl_set_mkLinkEvents)
		{
			$showHtml = true;
			$optionsArr[] = 'mkLinkEvents: true';
		}

		// check for cookie
		if($this->srl_set_cookie && $showHtml)
		{
			//Name des Cookies
			if(!$this->srl_cookie_name) $this->srl_cookie_name = 'LAYER_'.$this->id.'_COOKIE';

			if(!$this->Input->cookie($this->srl_cookie_name))
			{
				if(!$this->srl_cookie_dauer) $this->srl_cookie_dauer = 3600;
				$this->setCookie( $this->srl_cookie_name, 1, time() + $this->srl_cookie_dauer );

			} else $showHtml = false;
		}

		// check for session
		if($this->srl_set_session && $showHtml)
		{
			$this->import('Session');
			$this->srl_session_name = 'LAYER_'.$this->id.'_SESSION';

			if(!$this->Session->get($this->srl_session_name))
			{
				$this->Session->set($this->srl_session_name,'1');

			} else $showHtml = false;
		}

		// check for GET parameter and override showing
		if( $this->srl_substr ? \Input::get($this->srl_substr) !== null : false )
		{
			$showHtml = true;
			$showNow = true;
		}

		// only insert HTML, CSS and JS if showHtml was true
		if($showHtml)
		{
			$layerName = $this->srl_substr;

			if($showNow) $this->optionsArr[] = 'showNow: true';
			if(is_numeric($this->srl_option_layerwidth)) $this->optionsArr[] = 'layerWidth:'.$this->srl_option_layerwidth;
			if(is_numeric($this->srl_option_layerheight)) $this->optionsArr[] = 'layerHeight:'.$this->srl_option_layerheight;

			//expert options
			if($this->srl_set_jsoptions == 1)
			{
				if($objPage->hasJQuery)
				{
					if(strlen($this->srl_set_overLayID)) $this->optionsArr[] = "overLayID:'#".$this->srl_set_overLayID."'";
					if(strlen($this->srl_set_layerID)) $this->optionsArr[] = "layerID:'#".$this->srl_set_layerID."'";
					if(strlen($this->srl_set_closeID)) $this->optionsArr[] = "closeID:'#".$this->srl_set_closeID."'";
					if(strlen($this->srl_set_closeClass)) $this->optionsArr[] = "closeClass:'.".$this->srl_set_closeClass."'";

				} else {

					if(strlen($this->srl_set_overLayID)) $this->optionsArr[] = "overLayID:'".$this->srl_set_overLayID."'";
					if(strlen($this->srl_set_layerID)) $this->optionsArr[] = "layerID:'".$this->srl_set_layerID."'";
					if(strlen($this->srl_set_closeID)) $this->optionsArr[] = "closeID:'".$this->srl_set_closeID."'";
					if(strlen($this->srl_set_closeClass)) $this->optionsArr[] = "closeClass:'".$this->srl_set_closeClass."'";

				}

				if(strlen($this->srl_set_overLayOpacity)) $this->optionsArr[] = 'overLayOpacity:'.$this->srl_set_overLayOpacity;
				if(strlen($this->srl_set_duration)) $this->optionsArr[] = 'duration:'.$this->srl_set_duration;
				if(!$this->srl_set_closePerEsc) $this->optionsArr[] = 'closePerEsc:false';
				if(!$this->srl_set_closePerLayerClick) $this->optionsArr[] = 'closePerLayerClick:false';
				if(!$this->srl_set_drawLayerCenterX) $this->optionsArr[] = 'drawLayerCenterX:false';
				if(!$this->srl_set_drawLayerCenterY) $this->optionsArr[] = 'drawLayerCenterY:false';
			}

			$jsOptions = implode(', ',$optionsArr);

			//eigene CSS-Auszeichnungen aus CSS-Datei			
			if($this->srl_css_file)
			{
				$cssObjFile = \FilesModel::findByPk($this->srl_css_file);

				if(version_compare(VERSION, '3.2','>='))
				{					
					if ($cssObjFile === null)
					{
						if (!Validator::isUuid($this->srl_css_file))
						{
						    $this->log($GLOBALS['TL_LANG']['ERR']['version2format'],'ModuleSRLayer.php srl_css_file','TL_ERROR');
						}
					}
					$cssPath = $cssObjFile->path;					
				}
				elseif(version_compare(VERSION, '3.2','<'))
				{					
					$cssPath = $cssObjFile->path;
				}
				
			}
			
			$GLOBALS['TL_CSS'][] = ($cssPath) ? $cssPath : $GLOBALS['SRL_CSS'];

			//wenn jQuery aktiviert ist dann jQuery (vorrangig)
			if($objPage->hasJQuery && is_array($GLOBALS['SRL_JS']['jquery']))
			{
				foreach($GLOBALS['SRL_JS']['jquery'] as $jsSource)
				{
					$GLOBALS['TL_JAVASCRIPT'][] = $jsSource;
				}

				if((int) $this->srl_delay > 0) $GLOBALS['TL_JQUERY'][] = '
					<script type="text/javascript"> 
				 	(function($) {
						$(document).ready(function() 
						{ 
							setTimeout(function()
							{
								$.srLayer( { '.$jsOptions.', '.$this->srl_option_other.' } ); 
							}, '.$this->srl_delay.'); 
						});
	 				})(jQuery);
					</script>';
				else $GLOBALS['TL_JQUERY'][] = '
					<script type="text/javascript">
						(function($) {
							$(document).ready(function() 
							{
								$.srLayer( { '.$jsOptions.', '.$this->srl_option_other.' } ); 
							});
						 })(jQuery);
						</script>';

			}
			// ansonsten Mootools
			else if($objPage->hasMooTools && is_array($GLOBALS['SRL_JS']['mootools']))
			{

				foreach($GLOBALS['SRL_JS']['mootools'] as $jsSource)
				{
					$GLOBALS['TL_JAVASCRIPT'][] = $jsSource;
				}
				if((int) $this->srl_delay > 0) $GLOBALS['TL_MOOTOOLS'][] = '<script type="text/javascript"> window.addEvent(\'domready\', function() { var ml = new srLayer( { '.$jsOptions.', '.$this->srl_option_other.' } ); }.delay('.$this->srl_delay.'));</script>';
				else $GLOBALS['TL_MOOTOOLS'][] = '<script type="text/javascript"> window.addEvent(\'domready\', function() { var ml = new srLayer( { '.$jsOptions.', '.$this->srl_option_other.' } ); });</script>';
			}

			$this->Template->content = $this->srl_content;
			$this->Template->showLayerHtml = $showHtml;
			$this->Template->hideOverlay = ($this->srl_hideOverlay == 1) ? true : false;
		}

	}

}
