<?php
	/**
	 * Megan Template Engine
	 * Megan is a simple, easy-to-use, light-weight and high-performance template engine written in PHP.
	 *
	 * Valid tags in templates:
	 * 		${label}		Local Label			Local labels are valid for a section.
	 * 		#{label}		Global Label		Global labels are valid for a section and all of its sub-sections.
	 *		@{file}			Embed File			Embed a file in the template before processing it.
	 *		~{file}			Dynamic Include		Process a file and put a dynamic URL for it.
	 * 		{{section{		Section Begin		Beginning of a section.
	 * 		}}section}		Section End			Ending of a section.
	 * 
	 * @name		Megan Template Engine
	 * @author		Masoud Gheysari M <m.gheysari@gmail.com>
	 * @copyright 	2011 - Masoud Gheysari M
	 * @version 	1.2.2
	 * @license		BSD
	 */
	
	// Enable or disable dynamic includes
	define('MEGAN_ENABLE_DYNAMIC'	,true);
	 
	if(MEGAN_ENABLE_DYNAMIC) {
		// Process direct calls to this script for dynamic includes:
		// 1. Temporary turn off error reporting
		$display_errors=ini_get('display_errors');
		ini_set('display_errors','Off');
		
		session_start();

		// 2. If this script is called directly, send the processed included file to the browser
		if($_SERVER['SCRIPT_FILENAME']==__FILE__ && isset($_GET['MeganID'])) {
			header('Content-Disposition: inline; filename='.$_SESSION['Megan'][$_GET['MeganID']]['Name']);
			header('Content-Length: '.strlen($_SESSION['Megan'][$_GET['MeganID']]['Data']));
			echo $_SESSION['Megan'][$_GET['MeganID']]['Data'];
			unset($_SESSION['Megan'][$_GET['MeganID']]);
			die();
		}
		
		// 3. Reset the error reporting status
		ini_set('display_errors',$display_errors);
	}

	class Megan {
		private $megan_url,
			$labels_array,
			$sections_array,
			$template_file;
		
		function __construct($template_file) {
			$this->template_file=$template_file;
			
			// Calculate the URL of Megan.php script to use in dynamic includes
			$www_root_dir=str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['SCRIPT_FILENAME']);
			$megan_script=str_replace($www_root_dir,'',__FILE__);
			if(isset($_SERVER['HTTPS']))
				$this->megan_url='https://'.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].'/'.$megan_script;
			else
				$this->megan_url='http://'.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].'/'.$megan_script;
		}
		
		function __set($name,$value) {
			$this->labels_array[$name]=$value;
		}
		
		function __toString() {
			return $this->Generate(true);
		}
		
		function &NewSection($name) {
			$megan_object=new Megan($this->template_file);
			$this->sections_array[$name][]=$megan_object;
			return $megan_object;
		}

		function Generate($return=false,$template=null) {
			if(!$template) $template=file_get_contents($this->template_file);
			// Embed additional files: @{file} tags
			while(true) {
				$i=strpos($template,'@{');
				if(!$i) break;
				$j=strpos($template,'}',$i+2);
				$tag=substr($template,$i+2,$j-$i-2);
				$template=substr($template,0,$i).file_get_contents(dirname($this->template_file).'/'.$tag).substr($template,$j+1);
			}
			// Replace global labels: #{label} tags
			while(true) {
				$i=strpos($template,'#{');
				if(!$i) break;
				$j=strpos($template,'}',$i+2);
				$tag=substr($template,$i+2,$j-$i-2);
				if(isset($this->labels_array[$tag]))
					$template=substr($template,0,$i).$this->labels_array[$tag].substr($template,$j+1);
			}
			// Process sections: {{section{ and }}section} tags
			while(true) {
				$sections="";
				$i=strpos($template,'{{');
				if(!$i) break;
				$j=strpos($template,'{',$i+2);
				$tag=substr($template,$i+2,$j-$i-2);
				$k=strpos($template,'}}'.$tag.'}',$j+2);
				if(!$k) die("[Megan] Template Error: No closing tag for '$tag' section.");
				$subtemplate=substr($template,$j+1,$k-$j-1);
				foreach($this->sections_array[$tag] as $section) {
					$sections.=$section->Generate(true,$subtemplate);
				}
				$template=substr($template,0,$i).$sections.substr($template,$k+strlen($tag)+3);
			}
			if(MEGAN_ENABLE_DYNAMIC) {
				// Process dynamic includes: ~{file} tags
				while(true) {
					$i=strpos($template,'~{');
					if(!$i) break;
					if(!isset($serialize)) $serialize=serialize($this);
					$j=strpos($template,'}',$i+2);
					$tag=substr($template,$i+2,$j-$i-2);
					$token=md5($serialize.$tag);
					$path=dirname($this->template_file).'/'.$tag;
					$_SESSION['Megan'][$token]['Name']=basename($path);
					$_SESSION['Megan'][$token]['Data']=$this->Generate(true,file_get_contents($path));
					$template=substr($template,0,$i).$this->megan_url.'?MeganID='.$token.substr($template,$j+1);
				}
			}
			// Replace local labels: ${label} tags
			while(true) {
				$i=strpos($template,'${');
				if(!$i) break;
				$j=strpos($template,'}',$i+2);
				$tag=substr($template,$i+2,$j-$i-2);
				$template=substr($template,0,$i).$this->labels_array[$tag].substr($template,$j+1);
			}
			if($return)
				return $template;
			else
				echo $template;
		}
	}
?>