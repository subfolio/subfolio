<?php 
/**
 * 
 */
class Subfolio {
  public static $filebrowser;
  public static $auth;
  public static $template;
  public static $filekind;
  
  public static function set_filebrowser($_filebrowser) {
    Subfolio::$filebrowser = $_filebrowser;
  }
  public static function set_auth($_auth) {
    Subfolio::$auth = $_auth;
  }
  public static function set_template($_template) {
    Subfolio::$template = $_template;
  }
  public static function set_filekind($_filekind) {
    Subfolio::$filekind = $_filekind;
  }

  public static function link_to($text, $url) {
    return html::anchor($url, $text);
  }

  public static function mail_to($text, $email, $subject, $body) {
    return "<a href='mailto:$email?subject=$subject&body=$body'>$text</a>"; 
  }

  public static function current_url()
  {
    return "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];;
  }

  public static function current_file($data)
  {
    if ($data == "width") {
      list($width, $height, $type, $attr) = getimagesize(Subfolio::$filebrowser->fullfolderpath."/".Subfolio::$template->content->file->name);
      return $width;
    }

    if ($data == "height") {
      list($width, $height, $type, $attr) = getimagesize(Subfolio::$filebrowser->fullfolderpath."/".Subfolio::$template->content->file->name);
      return $height;
    }

  
    if ($data == "icon") {
    	$file_kind = Subfolio::$filekind->get_kind_by_file(Subfolio::$filebrowser->file);
    	$icon_file = "";
    	$icon_file = Subfolio::$filekind->get_icon_by_file($file_kind);
      return view::get_view_url()."/images/icons/grid/".$icon_file.".png";
    }

    if ($data == "tag") {
    	$new = false;
    	$new_updated_start = Subfolio::$filebrowser->get_updated_since_time();
      
    	$updated = false;
      if (Subfolio::$template->content->file->stats['mtime'] > $new_updated_start) {
          $updated = true;
      }
      if ($new) {
        return "new";
      } else if ($updated) {
        return "updated";
      } else {
        return "";
      }
    }
  
    if ($data == "url") {
      return Subfolio::$filebrowser->get_file_url();
    }

    if ($data == "link") {
      return Subfolio::$filebrowser->get_file_url();
    }

    if ($data == "filename") {
      return Subfolio::$template->content->file->name;
    }

    if ($data == "lastmodified") {
      return format::filedate(Subfolio::$template->content->file->stats['mtime']);
    }

    if ($data == "size") {
      return format::filesize(Subfolio::$template->content->file->stats['size']) ? format::filesize(Subfolio::$template->content->file->stats['size']) : "—";
    }

    if ($data == "comment") {
      return Subfolio::$filebrowser->get_item_property(Subfolio::$filebrowser->file, 'comment') ? Subfolio::$filebrowser->get_item_property(Subfolio::$filebrowser->file, 'comment') : '';
    }

    if ($data == "kind") {
    	$file_kind = Subfolio::$filekind->get_kind_by_file(Subfolio::$filebrowser->file);
    	//$kind = isset($file_kind['kind']) ? $file_kind['kind'] : '';
    	return isset($file_kind['display']) ? $file_kind['display'] : '—';
  	}

    if ($data == "instructions") {
    	$file_kind = Subfolio::$filekind->get_kind_by_file(Subfolio::$filebrowser->file);
    	return isset($file_kind['instructions']) ? $file_kind['instructions'] : '';
  	}
   
    return "&nbsp;";
  }

}

class SubfolioTheme extends Subfolio {

  // ------------------------------------------------------
  // TEMPLATE RELATED FUNCTIONS
  // ------------------------------------------------------

  public static function get_mobile_viewport()
  {
    return (SubfolioTheme::is_iphone());
  }
  
  public static function is_iphone()
  {
    if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPod')) {
      return true;
    } else {
      return false;
    }
  }

  public static function get_page_title() {
    return isset(Subfolio::$template->page_title) ? Subfolio::$template->page_title : "";
  }

  public static function get_site_title() {
    return isset(Subfolio::$template->site_title) ? Subfolio::$template->site_title : "";
  }

  public static function get_site_name() {
    return Kohana::config('filebrowser.site_name');
  }
  
  public static function get_view_url() {
    return view::get_view_url();
  }
  
  public static function get_listing_mode(){
  	$listing_mode = Kohana::config('filebrowser.listing_mode');
  	$listing_mode = view::get_option('listing_mode', $listing_mode);
  	$listing_mode = Subfolio::$filebrowser->get_folder_property('listing_mode', $listing_mode); 
  	
  	return $listing_mode;
  }
  
  public static function get_notice($name=null)
  {
    if ($name == null) {
      $name = 'flash';
    }
    return Session::instance()->get($name);
  }

  public static function get_breadcrumb() {
    $replace_dash_space = view::get_option('replace_dash_space', true);
    $replace_underscore_space = view::get_option('replace_underscore_space', true);
    $display_file_extensions = view::get_option('display_file_extensions', true);

    $breadcrumbs = array();

    $ff = Subfolio::$filebrowser->get_path();
    $parts = explode( "/", $ff);
    $count = 1;
    if ($ff <> "" && sizeof($parts) > 0) { 
      $path = "/";
      foreach ($parts as $key => $value) {
        $crumb = array();
        $crumb['name'] = htmlentities(FileFolder::fix_display_name($value, $replace_dash_space, $replace_underscore_space, $display_file_extensions));

        if ($count == sizeof($parts)) {
          $crumb['url'] = '';
        } else {
          $crumb['url'] = $path.$value;
        }
        $path .= $value."/";
        $breadcrumbs[] = $crumb;
        $count ++;
      }
    }

    return $breadcrumbs;
  }

  public static function subfolio_link()
  {
    return "";
  }

  public function get_collapse_header_button($wrap=""){
    $link = "<a href=''>Collapse</a>";
    if ($wrap <> '') {
      $link = "<$wrap>".$link."</$wrap>";
    }
    return $link;
  }

  public function get_tiny_url($name, $wrap=""){
    $link = "<a href=\"javascript:void(location.href='http://tinyurl.com/create.php?url='+encodeURIComponent(location.href))\">$name</a>";
    if ($wrap <> '') {
      $link = "<$wrap>".$link."</$wrap>";
    }
    return $link;
  }

  // ------------------------------------------------------
  // THEME RELATED FUNCTIONS
  // ------------------------------------------------------
 
  public static function get_option($option_name, $default_value=null)
  {
    return view::get_option($option_name, $default_value);
  }
}

class SubfolioUser extends Subfolio {
  // ------------------------------------------------------
  // USER RELATED FUNCTIONS
  // ------------------------------------------------------
  public static function is_logged_in()
  {
    return Subfolio::$auth->logged_in();
  }
  
  public static function current_user_name() {
    if (Subfolio::$auth->logged_in()) {
      return Subfolio::$auth->get_user()->name;
    } else {
      return NULL;
    }
  }
}

class SubfolioLanguage extends Subfolio {
  // ------------------------------------------------------
  // LANGUAGE RELATED FUNCTIONS
  // ------------------------------------------------------
  public function get_text($name, $args = array())
  {
    return Kohana::lang("filebrowser.".$name, $args);
  }
}

class SubfolioFiles extends Subfolio {
  // ------------------------------------------------------
  // FILE/FOLDER RELATED FUNCTIONS
  // ------------------------------------------------------
  public function have_inline_images($type)
  {
    $have = false;
    if ($type == "top") {
      $inline = Subfolio::$filebrowser->get_file_list("img", "-t-", true);
      if (sizeof($inline) > 0) {
        $have = true;
      }
    } else if ($type == "middle") {
      $inline = Subfolio::$filebrowser->get_file_list("img", "-m-", true);
      if (sizeof($inline) > 0) {
        $have = true;
      }
    } else if ($type == "bottom") {
      $inline = Subfolio::$filebrowser->get_file_list("img", "-b-", true);
      if (sizeof($inline) > 0) {
        $have = true;
      }
    }

    return $have;
  }

  public function inline_images($type)
  {
    $list = array();
    if ($type == "top") {
      $inline = Subfolio::$filebrowser->get_file_list("img", "-t-", true);
    } else if ($type == "middle") {
      $inline = Subfolio::$filebrowser->get_file_list("img", "-m-", true);
    } else if ($type == "bottom") {
      $inline = Subfolio::$filebrowser->get_file_list("img", "-b-", true);
    }

    foreach ($inline as $item) {
      list($width, $height, $type, $attr) = getimagesize(Subfolio::$filebrowser->fullfolderpath."/".$item->name);
      
      $list_item = array();
      $list_item['url'] = "/directory/".Subfolio::$filebrowser->get_folder()."/".$item->name;
      $list_item['width'] = "";
      $list_item['height'] = "";
      
      $list[] = $list_item;
    }
    return $list;
  }

  public function have_inline_texts($type)
  {
    $have = false;
    if ($type == "top") {
      $inline = Subfolio::$filebrowser->get_file_list("txt", "-t-", true);
      if (sizeof($inline) > 0) {
        $have = true;
      }
    } else if ($type == "middle") {
      $inline = Subfolio::$filebrowser->get_file_list("txt", "-m-", true);
      if (sizeof($inline) > 0) {
        $have = true;
      }
    } else if ($type == "bottom") {
      $inline = Subfolio::$filebrowser->get_file_list("txt", "-b-", true);
      if (sizeof($inline) > 0) {
        $have = true;
      }
    }

    return $have;
  }

  public function inline_texts($type)
  {
    $list = array();
    if ($type == "top") {
      $inline = Subfolio::$filebrowser->get_file_list("txt", "-t-", true);
    } else if ($type == "middle") {
      $inline = Subfolio::$filebrowser->get_file_list("txt", "-m-", true);
    } else if ($type == "bottom") {
      $inline = Subfolio::$filebrowser->get_file_list("txt", "-b-", true);
    }

    foreach ($inline as $item) {
      $list[] = array('body' =>format::get_rendered_text(file_get_contents($item->name)));
    }
    return $list;
  }

  public function have_features()
  {
    $file_features = Subfolio::$filebrowser->get_file_list("ftr", null, true);
    return (sizeof($file_features) > 0);
  }

  public function features()
  {
    $list = array();
    $file_features = Subfolio::$filebrowser->get_file_list("ftr", null, true);
    foreach ($file_features as $file_feature) {
      $item = array();

	    $feature = Spyc::YAMLLoad($file_feature->name);
	    $feature_link = "";
	    if (isset($feature['link'])) {
	      $feature_link = $feature['link'];
	    } else if (isset($feature['folder'])) {
	      $feature_link = "/".Subfolio::$filebrowser->get_folder()."/".$feature['folder'];
	    }
      
      $item['link'] = $feature_link;
      $item['image_file'] = $feature['image'];
      $item['title'] = $feature['title'];
      $item['description'] = $feature['description'];
      
      $list[] = $item;
    }
    return $list;
  }

  public function have_gallery_images()
  {
    $files   = Subfolio::$filebrowser->get_file_list("img");
    if (sizeof($files) > 0) { 
      return true;
    }
    return false;
  }

  public function gallery_images()
  {
    $display_filenames = view::get_option('display_file_names_in_gallery', true);
    $files = Subfolio::$filebrowser->get_file_list("img");

    $gallery = array();
    foreach ($files as $file) { 
  		if ($file->needs_thumbnail()) { 
  		  $image_source = $file->get_thumbnail_url(); 
  		} else { 
    		$image_source = $file->get_url(); 
    	}

      list ($width, $height) = $file->get_gallery_width_height();

      if ($image_source <> '') {
        $image = array();  
        $image['width'] = $width;
        $image['height'] = $height;
  			if ($file->has_custom_thumbnail()) { 
  			  
  			  $image['class'] = "gallery_thumbnail custom";
  			  $image['link'] = Subfolio::$filebrowser->get_link(urlencode($file->name));
  			  $image['filename'] = htmlentities($file->name);
  			  $image['url'] = $image_source;
  			  
  			// Custom thumbnails -----------------------------------------------------------------------------
      	} else { 
  			// Genrerated or not thumbnails -----------------------------------------------------------------------------
  			  $image['class'] = "gallery_thumbnail";
  			  $image['link'] = Subfolio::$filebrowser->get_link(urlencode($file->name));
  			  $image['filename'] = htmlentities($file->name);
  			  $image['url'] = $image_source;
  			}
  			$gallery[] = $image;
			}
		} 
    return $gallery;
  }

  public function have_files()
  {
    $folders = Subfolio::$filebrowser->get_folder_list();
    $folders = Subfolio::$filebrowser->sort($folders);
    
    $files  = Subfolio::$filebrowser->get_file_list();
    $files  = Subfolio::$filebrowser->sort($files);
    
    $showListing = false;
    if (sizeof($folders) > 0) {
      $showListing = true;
    } else {
      if (sizeof($files)) {
        $showListing = true;
      }
    }
    return $showListing;
  }

  public function files()
  {
    $listing_mode = SubfolioTheme::get_listing_mode();
    $replace_dash_space = view::get_option('replace_dash_space', true);  
    $replace_underscore_space = view::get_option('replace_underscore_space', true);
    $display_file_extensions = view::get_option('display_file_extensions', true);

    $folders = Subfolio::$filebrowser->get_folder_list();
    $folders = Subfolio::$filebrowser->sort($folders);
    $files  = Subfolio::$filebrowser->get_file_list();
    $files  = Subfolio::$filebrowser->sort($files);

    $new_updated_start = Subfolio::$filebrowser->get_updated_since_time();
    $list = array();
    foreach ($folders as $folder) {
      $restricted = false;
      $have_access = false;
      $new = false;
      $updated = false;

      if (!Subfolio::$filebrowser->is_feature($folder->name)) {
    
        $target = "";
        $folder_kind = Subfolio::$filekind->get_kind_by_file($folder->name);
        $kind = isset($folder_kind['kind']) ? $folder_kind['kind'] : '';
        $icon_file = "i_dir";
    
        $kind_display = isset($folder_kind['display']) ? $folder_kind['display'] : '';
        $url = "";
        $display = $folder->get_display_name($replace_dash_space, $replace_underscore_space, $display_file_extensions);
					
	        if ($folder->contains_access_file()) {
	         	$restricted = true;
          	if ($folder->have_access($this->auth->get_user())) {
            	$have_access = true;
          	} else {
            	$have_access = false;
          	}
	        } else {
          	if ($folder->have_access($this->auth->get_user())) {
            	$have_access = true;
          	} else {
            	$have_access = false;
  	         	$restricted = true;
          	}
	        }

					if (!$restricted || $have_access) {
			      if (false && $folder->stats['ctime'] > $new_updated_start) {
	            	$new = true;
	          } else if ($folder->stats['mtime'] > $new_updated_start) {
	        		$updated = true;
	          }
         	}

        if ($kind == "site") {
        	$folder_kind = $this->filekind->get_kind_by_extension("site");
        } else {
        	$folder_kind = $this->filekind->get_kind_by_extension("dir");
        }

        $icon_file = $this->filekind->get_icon_by_file($folder_kind);
				$listing_mode = $this->filebrowser->get_folder_property('listing_mode', $listing_mode);
		
				// to be confirmed
				if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPod')) {
					$listing_mode = 'grid';
				}
		
        $icon = view::get_view_url()."/images/icons/".$listing_mode."/".$icon_file.".png";        
  
    	  switch ($kind) {
    			case "site" :
    			  		$url = "/directory".Subfolio::$filebrowser->get_link($folder->name)."/index.html";
    			  		$target = "_blank";
    		        $display = format::filename($folder->get_display_name($replace_dash_space, $replace_underscore_space, $display_file_extensions), false);
    		        break;
    
    			case "pages" :
      			  	$url = "/directory".Subfolio::$filebrowser->get_link($folder->name);
      			  	break;
    
    			case "numbers" :
      			  	$url = "/directory".Subfolio::$filebrowser->get_link($folder->name);
      			  	break;
    
    			case "key" :
    						$url = "/directory".Subfolio::$filebrowser->get_link($folder->name);
    						break;
            
    			default:
              	$url = "".Subfolio::$filebrowser->get_link($folder->name);
            		break;
    		}

        $item = array();
        $item['target'] = $target;
        $item['url'] = $url;
        $item['icon'] = $icon;
        $item['filename'] = $display;
        $item['size'] = "&mdash";
        $item['date'] = format::filedate($folder->stats['mtime']);
        $item['kind'] = $kind_display;
        $item['comment'] = format::get_rendered_text(Subfolio::$filebrowser->get_item_property($folder->name, 'comment'));
        $item['restricted'] = $restricted;
        $item['have_access'] = $have_access;
        $item['new'] = $new;
        $item['updated'] = $updated;
        $list[] = $item;
      }
    }

    foreach ($files as $file) {
      $restricted = false;
      $have_access = false;
      $new = false;
      $updated = false;

      if (!$file->has_thumbnail()) {
          $file_kind = Subfolio::$filekind->get_kind_by_file($file->name);
          
          if (isset($file_kind['kind'])) {
            $kind = $file_kind['kind'];
          } else {
            $kind = "";
          }
    
          if ($kind == "img" && !$file->needs_thumbnail()) {
            // don't show listing for image smaller than thumbnail;
            continue;
          }
          $kind_display = isset($file_kind['display']) ? $file_kind['display'] : '';
          
          $icon_file = "";
          $new = false;
          $updated = false;
    				
          if (false && $file->stats['ctime'] > $new_updated_start) {
              $new = true;
          } else if ($file->stats['mtime'] > $new_updated_start) {
              $updated = true;
          }
    
          $icon_file = Subfolio::$filekind->get_icon_by_file($file_kind);
    	  	$listing_mode = Subfolio::$filebrowser->get_folder_property('listing_mode', $listing_mode);
          
       	  // to be confirmed
      	  if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'],'iPod')) {
      		  $listing_mode = 'grid';
      	  }
      	
      	  $icon = view::get_view_url()."/images/icons/".$listing_mode."/".$icon_file.".png";
    
          $target = "";
          $url = "";
          $display = "";
    
    		  switch ($kind) {
    
      			case "pop" :
    	        $width    = Subfolio::$filebrowser->get_item_property($file->name, 'width')    ? Subfolio::$filebrowser->get_item_property($file->name, 'width') : 800;
    	        $height   = Subfolio::$filebrowser->get_item_property($file->name, 'height')   ? Subfolio::$filebrowser->get_item_property($file->name, 'height') : 600;
    	        $url      = Subfolio::$filebrowser->get_item_property($file->name, 'url')      ? Subfolio::$filebrowser->get_item_property($file->name, 'url') : 'http://www.subfolio.com';
    	        $name     = Subfolio::$filebrowser->get_item_property($file->name, 'name')     ? Subfolio::$filebrowser->get_item_property($file->name, 'name') : 'POPUP';
    	        $style    = Subfolio::$filebrowser->get_item_property($file->name, 'style')    ? Subfolio::$filebrowser->get_item_property($file->name, 'style') : 'POPSCROLL';
    
    	        $url = "javascript:pop('$url','$name',$width,$height,'$style');";
    				  $display = format::filename($file->get_display_name($replace_dash_space, $replace_underscore_space, $display_file_extensions), false);
    	        break;
    
      			case "link" :
    	        $url = Subfolio::$filebrowser->get_item_property($file->name, 'url')    ? Subfolio::$filebrowser->get_item_property($file->name, 'url') : '';
    	        $target = Subfolio::$filebrowser->get_item_property($file->name, 'target')    ? Subfolio::$filebrowser->get_item_property($file->name, 'target') : '_blank';
      			  $display = format::filename($file->get_display_name($replace_dash_space, $replace_underscore_space, $display_file_extensions), false);
      			  break;
    
      			default:
      			  $url = Subfolio::$filebrowser->get_link($file->name);
      			  $display = $file->get_display_name($replace_dash_space, $replace_underscore_space, $display_file_extensions);
              break;  			
    	    }


        $item = array();
        $item['target'] = $target;
        $item['url'] = $url;
        $item['icon'] = $icon;
        $item['filename'] = $display;
        $item['size'] = format::filesize($file->stats['size']);
        $item['date'] = format::filedate($file->stats['mtime']);
        $item['kind'] = $kind_display;
        $item['comment'] = format::get_rendered_text(Subfolio::$filebrowser->get_item_property($file->name, 'comment'));
        $item['restricted'] = $restricted;
        $item['have_access'] = $have_access;
        $item['new'] = $new;
        $item['updated'] = $updated;
        $list[] = $item;

        }

    }
    
    return $list;
  }

  public function have_related()
  {
      $list = Subfolio::$filebrowser->get_file_list("cut", null, true);
      return (sizeof($list) > 0);
  }

  public function related()
  {
    $related = array();
    
    $listing_mode = Kohana::config('filebrowser.listing_mode');
    $listing_mode = view::get_option('listing_mode', $listing_mode);
    
    $list = Subfolio::$filebrowser->get_file_list("cut", null, true);
    
    foreach ($list as $item) {
      $link = Subfolio::$filebrowser->get_item_property($item->name, 'url');
      if ($link == "") {
        $link = Subfolio::$filebrowser->get_item_property($item->name, 'directory');
      }
      $name = Subfolio::$filebrowser->get_item_property($item->name, 'name');
      $url = view::get_view_url() ."/images/icons/".$listing_mode."/i_cut.png";
      $width = "18";
      $height = "18";
      
      $rel = array();
      $rel['link'] = $link;
      $rel['filename'] = $name;

      $rel['url']    = $url;
      $rel['width']  = $width;
      $rel['height'] = $height;
      
      $related[] = $rel;
    }
    
    return $related;
  }

  public function is_root() {
    $ff = Subfolio::$filebrowser->get_path();
    return ($ff == "");
  }

  public function parent_link($name) {
    $ff = Subfolio::$filebrowser->get_path();
    if ($ff <> '') {
    	$parent_link = urlencode(dirname($ff));
      $parent_link = str_replace('%2F', '/', $parent_link);
      return html::anchor($parent_link, $name);
    }
    return NULL;
  }

  public function previous_link_or_span($name, $directory_name, $link_id, $class) {
    $ff = Subfolio::$filebrowser->get_path();
    if ($ff <> '') {
  		if(Subfolio::$filebrowser->is_file()) {
  	    $file = Subfolio::$filebrowser->get_file();
  	    $files = Subfolio::$filebrowser->get_parent_file_list();
  	    $files = Subfolio::$filebrowser->prev_next_sort($files);
  			$prev = Subfolio::$filebrowser->get_prev($files, $file->name);
  			if ($prev <> "") {
        	$link = urlencode($prev->name);
          $link = str_replace('%2F', '/', $link);
  				return "<a id='$link_id' href='$link'>$name</a>";
  			} else {
  				return "<span class='".$class."'>".$name."</span>";
  			}
  	  } else { 
  	    $folder  = basename(Subfolio::$filebrowser->get_folder());
  	    $folders = Subfolio::$filebrowser->get_parent_folder_list();
  			$prev    = Subfolio::$filebrowser->get_prev($folders, $folder);
  
  			if ($prev <> "") {
        	$link = urlencode($prev->name);
          $link = str_replace('%2F', '/', $link);
  
  				return "<a id='$link_id' href='$link'>$directory_name</a>";
  			} else {
  				return "<span class='".$class."'>".$directory_name."</span>";
  			}
  	  }
    }
  }

  public function next_link_or_span($name, $directory_name, $link_id, $class) {
    $ff = Subfolio::$filebrowser->get_path();
    if ($ff <> '') {
  		if(Subfolio::$filebrowser->is_file()) {
  	    $file = Subfolio::$filebrowser->get_file();
  	    $files = Subfolio::$filebrowser->get_parent_file_list();
  	    $files = Subfolio::$filebrowser->prev_next_sort($files);
  			$next = Subfolio::$filebrowser->get_next($files, $file->name);
  			if ($next <> "") {
        	$link = urlencode($next->name);
          $link = str_replace('%2F', '/', $link);
  				return "<a id='$link_id' href='$link'>$name</a>";
  			} else {
  				return "<span class='".$class."'>".$name."</span>";
  			}
  	  } else { 
  	    $folder  = basename(Subfolio::$filebrowser->get_folder());
  	    $folders = Subfolio::$filebrowser->get_parent_folder_list();
  			$next = Subfolio::$filebrowser->get_next($folders, $folder);
  
  			if ($next <> "") {
        	$link = urlencode($next->name);
          $link = str_replace('%2F', '/', $link);
  
  				return "<a id='$link_id' href='$link'>$directory_name</a>";
  			} else {
  				return "<span class='".$class."'>".$directory_name."</span>";
  			}
  	  }
	  }
  }

  public function updated_since_link_or_span($type)
  {
    $ls = "";
    $updated_since = Subfolio::$filebrowser->get_updated_since();

    if ($type == "lastweek") {
      if ($updated_since == "lastweek") { 
        $ls = "<span>".SubfolioLanguage::get_text('last_week')."</span>";
      } else { 
        $ls = "<a href=\"?updated_since=lastweek\">".SubfolioLanguage::get_text('last_week')."</a>";
      }
    }

    if ($type == "lastmonth") {
      if ($updated_since == "lastmonth") { 
        $ls = "<span>".SubfolioLanguage::get_text('last_month')."</span>";
      } else { 
        $ls = "<a href=\"?updated_since=lastmonth\">".SubfolioLanguage::get_text('last_month')."</a>";
      }
    }

    if ($type == "lastvisit") {
      if ($updated_since == "lastvisit") { 
        $ls = "<span>".SubfolioLanguage::get_text('last_visit')."</span>";
      } else { 
        $ls = "<a href=\"?updated_since=lastvisit\">".SubfolioLanguage::get_text('my_last_visit')."</a>";
      }
    }

    return $ls;
  }

}
?>