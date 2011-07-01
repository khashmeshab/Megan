<?php
	/**
	 * Megan Template Engine
	 * Megan is a simple, easy-to-use, light-weight and high-performance template engine written in PHP.
	 *
	 * Valid tags in templates:
	 * 		${label}	Local label valid for that section.
	 * 		#{label}	Global label valid for that section and all sub-sections.
	 * 		{{section{	Start of section.
	 * 		}}section}	End of section.
	 * 
	 * @name		Megan Template Engine
	 * @author		Masoud Gheysari M <m.gheysari@gmail.com>
	 * @copyright 	2011 - Masoud Gheysari M
	 * @version 	1.2.1
	 * @license		BSD
	 */
	
	class Megan {
		private $labels_array,
			$sections_array;
		
		function __construct() {
		}
		
		function __set($name,$value) {
			$this->labels_array[$name]=$value;
		}
		
		function __toString() {
			return $this->Generate(true);
		}
		
		function &NewSection($name) {
			$megan_object=new Megan();
			$this->sections_array[$name][]=$megan_object;
			return $megan_object;
		}

		function Generate($template,$return=false) {
			$i=0;
			// replace global labels
			while(true) {
				$i=strpos($template,'#{',$i+2);
				if(!$i) break;
				$j=strpos($template,'}',$i+2);
				$tag=substr($template,$i+2,$j-$i-2);
				$template=substr($template,0,$i).$this->labels_array[$tag].substr($template,$j+1);
			}
			// replace sections
			while(true) {
				$sections="";
				$i=strpos($template,'{{');
				if(!$i) break;
				$j=strpos($template,'{',$i+2);
				$tag=substr($template,$i+2,$j-$i-2);
				$k=strpos($template,'}}'.$tag.'}',$j+2);
				if(!$k) die("PARTHIA Template Error: No closing tag for '$tag' section.");
				$subtemplate=substr($template,$j+1,$k-$j-1);
				foreach($this->sections_array[$tag] as $section) {
					$sections.=$section->Generate($subtemplate,true);
				}
				$template=substr($template,0,$i).$sections.substr($template,$k+strlen($tag)+3);
			}
			// replace local labels
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