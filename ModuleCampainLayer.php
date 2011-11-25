<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 */


/**
 * Class ModuleVouchersLatest
 *
 * @copyright  sr-tag 2011 
 * @author     Sven Rhinow <support@sr-tag.de>
 * @package    CampainLayer 
 */
class ModuleCampainLayer extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_campain_layer';
	
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
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### KAMPAGNEN-LAYER ('.$this->cl_substr.') ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
	
           //sucht in den Get-Keys nach einer bestimmten Teil-Zeichenkette           
           $pos = false;
                                
           if(count($_GET)>0) 
	   {
	       
	       foreach($_GET AS $k => $v) 
               {		  
		   $k = strip_tags(trim($k));
		   $getPos = strcmp($k,$this->cl_substr)==0 ? true : false;
		   		  
               }
           }           
           
           //Modul-Flag fuer "keine Parameter notwendig" pruefen
           if($this->cl_no_param || $getPos) 
           {
	       $this->optionsArr[] = 'showNow: true';
	       $this->show = true;
           }
           
           
           //Modul-Flag fuer "Layer per Link öffnen" pruefen
           if($this->cl_set_mkLinkEvents) 
           {
	       $this->optionsArr[] = 'mkLinkEvents: true';
	       $this->show = true;
           }
                      
           //Cookie
           if($this->cl_set_cookie && $this->show)
           {
	       
	       //Name des Cookies
	       if(!$this->cl_cookie_name) $this->cl_cookie_name = 'LAYER_'.$this->id.'_COOKIE';
	       
	       if(!$this->Input->cookie($this->cl_cookie_name))
	       {
		   if(!$this->cl_cookie_dauer) $this->cl_cookie_dauer = 3600;
		   $this->setCookie($this->cl_cookie_name,1,time()+$this->cl_cookie_dauer);
		   
		   
	       }else $this->show = false;
           }
           
           //Session
           if($this->cl_set_session && $this->show)
           {
	       $this->import('Session');
	       $this->cl_session_name = 'LAYER_'.$this->id.'_SESSION';
	       
	       if(!$this->Session->get($this->cl_session_name))
	       {
		  $this->Session->set($this->cl_session_name,'1');
		   
	       }else $this->show = false;
           
           }
           // nur wenn Fund dann CSS, JS und HTML einfuegen
           if($this->show)
           {
		$layerName = $this->cl_substr;
		
		$objTemplate = new FrontendTemplate($this->cl_template);
		$objTemplate->content = $this->cl_content;
		$templateHTML = $objTemplate->parse();
		
		//other options put in array
		if($this->cl_set_drawOverLay == 1) $this->optionsArr[] = 'drawOverLay:true';
		if(strlen($this->cl_set_overLayID)) $this->optionsArr[] = "overLayID:'".$this->cl_set_overLayID."'";
		
		if($this->cl_set_drawLayer == 1) $this->optionsArr[] = 'drawLayer:true';
		if(strlen($this->cl_set_layerID)) $this->optionsArr[] = "overLayID:'".$this->cl_set_layerID."'";
				
		if($this->cl_set_drawCloseBtn == 1) $this->optionsArr[] = 'drawCloseBtn:true';
		if(strlen($this->cl_set_closeID)) $this->optionsArr[] = "closeID:'".$this->cl_set_closeID."'";
		if(strlen($this->cl_set_closeClass)) $this->optionsArr[] = "closeClass:'".$this->cl_set_closeClass."'";
		
		if(strlen($this->cl_set_overLayOpacity)) $this->optionsArr[] = 'overLayOpacity:'.$this->cl_set_overLayOpacity;	
		
		if(!$this->cl_set_closePerEsc) $this->optionsArr[] = 'closePerEsc:false';
                if(!$this->cl_set_closePerLayerClick) $this->optionsArr[] = 'closePerLayerClick:false';
                
                if(!$this->cl_set_drawLayerCenterX) $this->optionsArr[] = 'drawLayerCenterX:false';
                if(!$this->cl_set_drawLayerCenterY) $this->optionsArr[] = 'drawLayerCenterY:false';
                
                if(is_numeric($this->cl_option_layerwidth)) $this->optionsArr[] = 'layerWidth:'.$this->cl_option_layerwidth;
                if(is_numeric($this->cl_option_layerheight)) $this->optionsArr[] = 'layerHeight:'.$this->cl_option_layerheight;
                
                $jsOptions = implode(', ',$this->optionsArr);
                
		//eigene CSS-Auszeichnungen aus CSS-Datei
		if($this->cl_css_file) $GLOBALS['TL_CSS'][] = $this->cl_css_file;
		else $GLOBALS['TL_CSS'][] = 'system/modules/campainLayer/html/css/campain_layer.css';
		
		$GLOBALS['TL_MOOTOOLS'][] = $templateHTML;		
		$GLOBALS['TL_MOOTOOLS'][] = '<script type="text/javascript" src="system/modules/campainLayer/html/js/campainLayer_mootools.js"></script>';
		$GLOBALS['TL_MOOTOOLS'][] = '<script type="text/javascript"><!--//--><![CDATA[//><!--
		window.addEvent(\'domready\', function() {
		var ml = new  myLayer({'.$jsOptions.', '.$this->cl_option_other.' }); 
		}); //--><!]]></script>';	
	   }
	   	  	   
	}

}

?>