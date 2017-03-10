<?php
	class WpFastestCacheCreateCache extends WpFastestCache{
		public $options = array();
		public $cdn;
		private $startTime;
		private $blockCache = false;
		private $err = "";
		public $cacheFilePath = "";
		public $exclude_rules = false;

		public function __construct(){
			//to fix: PHP Notice: Undefined index: HTTP_USER_AGENT
			$_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] ? strip_tags($_SERVER['HTTP_USER_AGENT']) : "Empty User Agent";
			
			$this->options = $this->getOptions();

			$this->checkActivePlugins();

			$this->set_cdn();

			$this->set_cache_file_path();

			$this->set_exclude_rules();

			$this->set_content_url();
		}

		public function set_content_url(){
			$content_url = content_url();

			// Hide My WP
			if($this->isPluginActive('hide_my_wp/hide-my-wp.php')){
				$hide_my_wp = get_option("hide_my_wp");

				if(isset($hide_my_wp["new_content_path"]) && $hide_my_wp["new_content_path"]){
					$hide_my_wp["new_content_path"] = trim($hide_my_wp["new_content_path"], "/");
					$content_url = str_replace("wp-content", $hide_my_wp["new_content_path"], $content_url);
				}
			}

			// WP Hide & Security Enhancer
			if($this->isPluginActive('wp-hide-security-enhancer/wp-hide.php')){
				$wph_settings = get_option("wph_settings");

				if(isset($wph_settings["module_settings"])){
					if(isset($wph_settings["module_settings"]["new_content_path"]) && $wph_settings["module_settings"]["new_content_path"]){
						$wph_settings["module_settings"]["new_content_path"] = trim($wph_settings["module_settings"]["new_content_path"], "/");
						$content_url = str_replace("wp-content", $wph_settings["module_settings"]["new_content_path"], $content_url);
					}
				}
			}

			if (!defined('WPFC_WP_CONTENT_URL')) {
				define("WPFC_WP_CONTENT_URL", $content_url);
			}
		}

		public function set_exclude_rules(){
			if($json_data = get_option("WpFastestCacheExclude")){
				$this->exclude_rules = json_decode($json_data);
			}
		}

		public function set_cache_file_path(){

			if($this->isMobile() && isset($this->options->wpFastestCacheMobile)){
				if(class_exists("WpFcMobileCache") && isset($this->options->wpFastestCacheMobileTheme)){
					$wpfc_mobile = new WpFcMobileCache();
					$this->cacheFilePath = $this->getWpContentDir()."/cache/".$wpfc_mobile->get_folder_name()."".$_SERVER["REQUEST_URI"];
				}
			}else{
				$this->cacheFilePath = $this->getWpContentDir()."/cache/all".$_SERVER["REQUEST_URI"];

				// qTranslate: in name.com/de REQUEST_URI is "/" instead of "/de" so need to check it
				// if(isset($_SERVER["HTTP_COOKIE"]) && $_SERVER["HTTP_COOKIE"]){
				// 	if(preg_match("/qtrans/i", $_SERVER["HTTP_COOKIE"])){
				// 		if(isset($_SERVER["REDIRECT_URL"]) && $_SERVER["REDIRECT_URL"]){
				// 			$this->cacheFilePath = $this->getWpContentDir()."/cache/all".$_SERVER["REDIRECT_URL"];
				// 		}
				// 	}
				// }

				//     [HTTP_COOKIE] => qtrans_front_language=en; _ga=GA1.2.1550945439.1457456694; _gat_awxoapTracker=1
			}

			$this->cacheFilePath = $this->cacheFilePath ? rtrim($this->cacheFilePath, "/")."/" : "";

			if(strlen($_SERVER["REQUEST_URI"]) > 1){ // for the sub-pages
				if(!preg_match("/\.html/i", $_SERVER["REQUEST_URI"])){
					if($this->is_trailing_slash()){
						if(!preg_match("/\/$/", $_SERVER["REQUEST_URI"])){
							$this->cacheFilePath = false;
						}
					}else{
						//toDo
					}
				}
			} 
		}

		public function set_cdn(){
			$cdn_values = get_option("WpFastestCacheCDN");
			if($cdn_values){
				$std_obj = json_decode($cdn_values);
				$arr = array();

				if(is_array($std_obj)){
					$arr = $std_obj;
				}else{
					array_push($arr, $std_obj);
				}

				foreach ($arr as $key => &$std) {
					$std->originurl = trim($std->originurl);
					$std->originurl = trim($std->originurl, "/");
					$std->originurl = preg_replace("/http(s?)\:\/\/(www\.)?/i", "", $std->originurl);

					$std->cdnurl = trim($std->cdnurl);
					$std->cdnurl = trim($std->cdnurl, "/");
					
					if(!preg_match("/https\:\/\//", $std->cdnurl)){
						$std->cdnurl = "//".preg_replace("/http(s?)\:\/\/(www\.)?/i", "", $std->cdnurl);
					}
				}
				
				$this->cdn = $arr;
			}
		}

		public function checkActivePlugins(){
			//for WP-Polls
			if($this->isPluginActive('wp-polls/wp-polls.php')){
				require_once "wp-polls.php";
				$wp_polls = new WpPollsForWpFc();
				$wp_polls->execute();
			}
		}

		public function checkShortCode($content){
			if(preg_match("/\[wpfcNOT\]/", $content)){
				if(!is_home() || !is_archive()){
					$this->blockCache = true;
				}
				$content = str_replace("[wpfcNOT]", "", $content);
			}
			return $content;
		}

		public function createCache(){		
			if(isset($this->options->wpFastestCacheStatus)){

				if(isset($this->options->wpFastestCacheLoggedInUser) && $this->options->wpFastestCacheLoggedInUser == "on"){
					// to check logged-in user
					foreach ((array)$_COOKIE as $cookie_key => $cookie_value){
						if(preg_match("/comment_author_|wordpress_logged_in|wp_woocommerce_session/i", $cookie_key)){
							return 0;
						}
					}
				}

				if(preg_match("/\?/", $_SERVER["REQUEST_URI"]) && !preg_match("/\/\?fdx\_switcher\=true/", $_SERVER["REQUEST_URI"])){ // for WP Mobile Edition
					if(defined('WPFC_CACHE_QUERYSTRING') && WPFC_CACHE_QUERYSTRING){
						//
					}else{
						return 0;
					}
				}

				if(preg_match("/(".$this->get_excluded_useragent().")/", $_SERVER['HTTP_USER_AGENT'])){
					return 0;
				}

				if(isset($_SERVER['REQUEST_URI']) && preg_match("/(\/){2}$/", $_SERVER['REQUEST_URI'])){
					return 0;
				}

				if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "POST"){
					return 0;
				}

				if(preg_match("/^https/i", get_option("home")) && !is_ssl()){
					//Must be secure connection
					return 0;
				}

				if(!preg_match("/^https/i", get_option("home")) && is_ssl()){
					//must be normal connection
					return 0;
				}

				if(preg_match("/www\./", get_option("home")) && !preg_match("/www\./", $_SERVER['HTTP_HOST'])){
					return 0;
				}

				if(!preg_match("/www\./", get_option("home")) && preg_match("/www\./", $_SERVER['HTTP_HOST'])){
					return 0;
				}

				// http://mobiledetect.net/ does not contain the following user-agents
				if(preg_match("/Nokia309|Casper_VIA/i", $_SERVER['HTTP_USER_AGENT'])){
					return 0;
				}

				if($this->exclude_page()){
					//echo "<!-- Wp Fastest Cache: Exclude Page -->"."\n";
					return 0;
				}

				if(preg_match("/Empty\sUser\sAgent/i", $_SERVER['HTTP_USER_AGENT'])){ // not to show the cache for command line
					return 0;
				}

				//to show cache version via php if htaccess rewrite rule does not work
				if($this->cacheFilePath && @file_exists($this->cacheFilePath."index.html")){
					if($content = @file_get_contents($this->cacheFilePath."index.html")){
						$content = $content."<!-- via php -->";
						die($content);
					}
				}else{
					if($this->isMobile()){
						if(class_exists("WpFcMobileCache") && isset($this->options->wpFastestCacheMobileTheme)){
							if(isset($this->options->wpFastestCacheMobileTheme_themename) && $this->options->wpFastestCacheMobileTheme_themename){
								$create_cache = true;
							}else if($this->isPluginActive('wptouch/wptouch.php') || $this->isPluginActive('wptouch-pro/wptouch-pro.php')){
								//to check that user-agent exists in wp-touch's list or not
								if($this->is_wptouch_smartphone()){
									$create_cache = true;
								}else{
									$create_cache = false;
								}
							}else if($this->isPluginActive('any-mobile-theme-switcher/any-mobile-theme-switcher.php')){
								if($this->is_anymobilethemeswitcher_mobile()){
									$create_cache = true;
								}else{
									$create_cache = false;
								}
							}else{
								if((preg_match('/iPhone/', $_SERVER['HTTP_USER_AGENT']) && preg_match('/Mobile/', $_SERVER['HTTP_USER_AGENT'])) || (preg_match('/Android/', $_SERVER['HTTP_USER_AGENT']) && preg_match('/Mobile/', $_SERVER['HTTP_USER_AGENT']))){
									$create_cache = true;
								}else{
									$create_cache = false;
								}
							}
						}else{
							$create_cache = false;
						}
					}else{
						$create_cache = true;
					}

					if($create_cache){
						$this->startTime = microtime(true);
						add_action( 'get_footer', array($this, "wp_print_scripts_action"));

						if(isset($this->options->wpFastestCacheLazyLoad)){
							if(!class_exists("WpFastestCacheLazyLoad")){
								include_once $this->get_premium_path("lazy-load.php");
							}

							if(method_exists("WpFastestCacheLazyLoad",'get_js_source_new')){
								add_action('wp_head', array($this, "wp_print_lazy_load_new_script_action"));
							}else{
								//anymore to use get_js_source_new() for header
								//Later we should remove the following line
								add_action('get_footer', array($this, "wp_print_lazy_load_script_action"));
							}
						}

						ob_start(array($this, "callback"));
					}
				}
			}
		}

		public function wp_print_lazy_load_new_script_action(){
			echo WpFastestCacheLazyLoad::get_js_source_new();
		}

		public function wp_print_lazy_load_script_action(){
			echo WpFastestCacheLazyLoad::get_js_source();
		}

		public function wp_print_scripts_action(){
			echo "<!--WPFC_FOOTER_START-->";
		}

		public function ignored($buffer){
			$list = array(
						"\/wp\-comments\-post\.php",
						"\/sitemap\.xml",
						"\/wp\-login\.php",
						"\/robots\.txt",
						"\/wp\-cron\.php",
						"\/wp\-content",
						"\/wp\-admin",
						"\/wp\-includes",
						"\/index\.php",
						"\/xmlrpc\.php",
						"\/wp\-api\/",
						"leaflet\-geojson\.php",
						"\/clientarea\.php"
					);
			if($this->isPluginActive('woocommerce/woocommerce.php')){
				if(preg_match("/page-id-(\d+)/", $buffer, $page_id)){
					if(function_exists("wc_get_page_id")){
						$woocommerce_ids = array();

						//wc_get_page_id('product')
						//wc_get_page_id('product-category')
						
						array_push($woocommerce_ids, wc_get_page_id('cart'), wc_get_page_id('checkout'), wc_get_page_id('receipt'), wc_get_page_id('confirmation'));

						if (in_array($page_id[1], $woocommerce_ids)) {
							return true;
						}
					}
				}

				//"\/product"
				//"\/product-category"

				array_push($list, "\/cart", "\/checkout", "\/receipt", "\/confirmation", "\/wc-api\/");
			}

			if(preg_match("/".implode("|", $list)."/i", $_SERVER["REQUEST_URI"])){
				return true;
			}

			return false;
		}

		public function exclude_page(){
			$preg_match_rule = "";
			$request_url = trim($_SERVER["REQUEST_URI"], "/");

			if($this->exclude_rules){

				foreach((array)$this->exclude_rules as $key => $value){
					$value->type = isset($value->type) ? $value->type : "page";

					if(isset($value->prefix) && $value->prefix && $value->type == "page"){
						$value->content = trim($value->content);
						$value->content = trim($value->content, "/");

						if($value->prefix == "homepage"){
							if($request_url == "/" || $request_url == ""){
								return true;
							} 
						}else if($value->prefix == "exact"){
							if(strtolower($value->content) == strtolower($request_url)){
								return true;	
							}
						}else{
							if($value->prefix == "startwith"){
								$preg_match_rule = "^".preg_quote($value->content, "/");
							}else if($value->prefix == "contain"){
								$preg_match_rule = preg_quote($value->content, "/");
							}

							if(preg_match("/".$preg_match_rule."/i", $request_url)){
								return true;
							}
						}
					}else if($value->type == "useragent"){
						if(preg_match("/".preg_quote($value->content, "/")."/i", $_SERVER['HTTP_USER_AGENT'])){
							return true;
						}
					}else if($value->type == "cookie"){
						if(preg_match("/".preg_quote($value->content, "/")."/i", $_SERVER['HTTP_COOKIE'])){
							return true;
						}
					}
				}

			}
			return false;
		}

		public function is_xml($buffer){
			if(preg_match("/^\s*\<\?xml/i", $buffer)){
				return true;
			}
			return false;
		}

		public function callback($buffer){
			$buffer = $this->checkShortCode($buffer);

			if(preg_match("/Mediapartners-Google|Google\sWireless\sTranscoder/i", $_SERVER['HTTP_USER_AGENT'])){
				return $buffer;
			}else if($this->is_xml($buffer)){
				return $buffer;
			}else if (is_user_logged_in() || $this->isCommenter()){
				return $buffer;
			} else if(isset($_SERVER["HTTP_ACCEPT"]) && preg_match("/json/i", $_SERVER["HTTP_ACCEPT"])){
				return $buffer;
			}else if(isset($_COOKIE["wptouch-pro-view"])){
				return $buffer."<!-- \$_COOKIE['wptouch-pro-view'] has been set -->";
			}else if($this->checkWoocommerceSession()){
				if($this->checkHtml($buffer)){
					return $buffer;
				}else{
					return $buffer."<!-- \$_COOKIE['wp_woocommerce_session'] has been set -->";
				}
			}else if(defined('DONOTCACHEPAGE') && $this->isPluginActive('wordfence/wordfence.php')){ // for Wordfence: not to cache 503 pages
				return $buffer."<!-- DONOTCACHEPAGE is defined as TRUE -->";
			}else if($this->isPasswordProtected($buffer)){
				return $buffer."<!-- Password protected content has been detected -->";
			}else if($this->isWpLogin($buffer)){
				return $buffer."<!-- wp-login.php -->";
			}else if($this->hasContactForm7WithCaptcha($buffer)){
				return $buffer."<!-- This page was not cached because ContactForm7's captcha -->";
			}else if(is_404()){
				return $buffer;
			}else if($this->ignored($buffer)){
				return $buffer;
			}else if($this->blockCache === true){
				return $buffer."<!-- wpfcNOT has been detected -->";
			}else if(isset($_GET["preview"])){
				return $buffer."<!-- not cached -->";
			}else if($this->checkHtml($buffer)){
				return $buffer."<!-- html is corrupted -->";
			}else if((function_exists("http_response_code")) && (http_response_code() == 301 || http_response_code() == 302)){
				return $buffer;
			}else if(!$this->cacheFilePath){
				return $buffer."<!-- permalink_structure ends with slash (/) but REQUEST_URI does not end with slash (/) -->";
			}else{				
				$content = $buffer;

				if(isset($this->options->wpFastestCacheRenderBlocking) && method_exists("WpFastestCachePowerfulHtml", "render_blocking")){
					if(class_exists("WpFastestCachePowerfulHtml")){
						if(!$this->is_amp($content)){
							$powerful_html = new WpFastestCachePowerfulHtml();

							if(isset($this->options->wpFastestCacheRenderBlockingCss)){
								$content = $powerful_html->render_blocking($content, true);
							}else{
								$content = $powerful_html->render_blocking($content);
							}
						}
					}
				}

				if(isset($this->options->wpFastestCacheCombineCss)){
					require_once "css-utilities.php";
					$css = new CssUtilities($this, $content);
					$content = $css->combineCss();
					unset($css);
				}else if(isset($this->options->wpFastestCacheMinifyCss)){
					require_once "css-utilities.php";
					$css = new CssUtilities($this, $content);
					$content = $css->minifyCss();
					unset($css);
				}

				if(isset($this->options->wpFastestCacheCombineJs) || isset($this->options->wpFastestCacheMinifyJs) || isset($this->options->wpFastestCacheCombineJsPowerFul)){
					require_once "js-utilities.php";
				}

				if(isset($this->options->wpFastestCacheCombineJs)){

					$head_new = $this->get_header($content);

				    if($head_new){
						if(isset($this->options->wpFastestCacheMinifyJs) && $this->options->wpFastestCacheMinifyJs){
							$js = new JsUtilities($this, $head_new, true);
						}else{
							$js = new JsUtilities($this, $head_new);
						}

						$tmp_head = $js->combine_js();

						$content = str_replace($head_new, $tmp_head, $content);

						unset($r);
						unset($js);
						unset($tmp_head);
						unset($head_new);
				    }
				}

				if(class_exists("WpFastestCachePowerfulHtml")){
					if(!isset($powerful_html)){
						$powerful_html = new WpFastestCachePowerfulHtml();
					}

					$powerful_html->set_html($content);

					if(isset($this->options->wpFastestCacheCombineJsPowerFul) && method_exists("WpFastestCachePowerfulHtml", "combine_js_in_footer")){
						if(isset($this->options->wpFastestCacheMinifyJs) && $this->options->wpFastestCacheMinifyJs){
							$content = $powerful_html->combine_js_in_footer($this, true);
						}else{
							$content = $powerful_html->combine_js_in_footer($this);
						}
					}
					
					if(isset($this->options->wpFastestCacheRemoveComments)){
						$content = $powerful_html->remove_head_comments();
					}

					if(isset($this->options->wpFastestCacheMinifyHtmlPowerFul)){
						$content = $powerful_html->minify_html();
					}

					if(isset($this->options->wpFastestCacheMinifyJs) && method_exists("WpFastestCachePowerfulHtml", "minify_js_in_body")){
						$content = $powerful_html->minify_js_in_body($this, $this->exclude_rules);
					}
				}

				if($this->err){
					return $buffer."<!-- ".$this->err." -->";
				}else{
					$content = $this->cacheDate($content);
					$content = $this->minify($content);
					
					if($this->cdn){
						$content = preg_replace_callback("/(srcset|src|href|data-lazyload)\=[\'\"]([^\'\"]+)[\'\"]/i", array($this, 'cdn_replace_urls'), $content);
						// url()
						$content = preg_replace_callback("/(url)\(([^\)]+)\)/i", array($this, 'cdn_replace_urls'), $content);
					}
					
					if(isset($this->options->wpFastestCacheLazyLoad)){
						$content = $powerful_html->lazy_load($content);
					}
					
					$content = str_replace("<!--WPFC_FOOTER_START-->", "", $content);

					if($this->cacheFilePath){
						$this->createFolder($this->cacheFilePath, $content);
					}
					
					return $content."<!-- need to refresh to see cached version -->";
				}
			}
		}

		public function get_header($content){
			$head_first_index = strpos($content, "<head");
			$head_last_index = strpos($content, "</head>");

			return substr($content, $head_first_index, ($head_last_index-$head_first_index + 1));
		}

		public function minify($content){
			$content = preg_replace("/<\/html>\s+/", "</html>", $content);
			$content = str_replace("\r", "", $content);
			return isset($this->options->wpFastestCacheMinifyHtml) ? preg_replace("/^\s+/m", "", ((string) $content)) : $content;
		}

		public function checkHtml($buffer){
			if(preg_match('/<html[^\>]*>/si', $buffer) && preg_match('/<body[^\>]*>/si', $buffer)){
				return false;
			}
			// if(strlen($buffer) > 10){
			// 	return false;
			// }

			return true;
		}

		public function cacheDate($buffer){
			if($this->isMobile() && class_exists("WpFcMobileCache")){
				$comment = "<!-- Mobile: WP Fastest Cache file was created in ".$this->creationTime()." seconds, on ".date("d-m-y G:i:s", current_time('timestamp'))." -->";
			}else{
				$comment = "<!-- WP Fastest Cache file was created in ".$this->creationTime()." seconds, on ".date("d-m-y G:i:s", current_time('timestamp'))." -->";
			}

			if(defined('WPFC_REMOVE_FOOTER_COMMENT') && WPFC_REMOVE_FOOTER_COMMENT){
				return $buffer;
			}else{
				return $buffer.$comment;
			}
		}

		public function creationTime(){
			return microtime(true) - $this->startTime;
		}

		public function isCommenter(){
			$commenter = wp_get_current_commenter();
			return isset($commenter["comment_author_email"]) && $commenter["comment_author_email"] ? true : false;
		}
		public function isPasswordProtected($buffer){
			if(preg_match("/action\=[\'\"].+postpass.*[\'\"]/", $buffer)){
				return true;
			}

			foreach($_COOKIE as $key => $value){
				if(preg_match("/wp\-postpass\_/", $key)){
					return true;
				}
			}

			return false;
		}

		public function createFolder($cachFilePath, $buffer, $extension = "html", $prefix = "", $gzip = false){
			$create = false;
			
			if($buffer && strlen($buffer) > 100 && $extension == "html"){
				if(!preg_match("/^\<\!\-\-\sMobile\:\sWP\sFastest\sCache/i", $buffer)){
					if(!preg_match("/^\<\!\-\-\sWP\sFastest\sCache/i", $buffer)){
						$create = true;
					}
				}
			}

			if(($extension == "css" || $extension == "js") && $buffer && strlen($buffer) > 5){
				$create = true;
				$buffer = trim($buffer);
				if($extension == "js"){
					if(substr($buffer, -1) != ";"){
						$buffer .= ";";
					}
				}
			}

			$cachFilePath = urldecode($cachFilePath);

			if($create){
				if (!is_user_logged_in() && !$this->isCommenter()){
					if(!is_dir($cachFilePath)){
						if(is_writable($this->getWpContentDir()) || ((is_dir($this->getWpContentDir()."/cache")) && (is_writable($this->getWpContentDir()."/cache")))){
							if (@mkdir($cachFilePath, 0755, true)){

								file_put_contents($cachFilePath."/".$prefix."index.".$extension, $buffer);
								
								if(class_exists("WpFastestCacheStatics")){
									if(!preg_match("/After\sCache\sTimeout/i", $_SERVER['HTTP_USER_AGENT'])){
										if(preg_match("/wpfc\-mobile\-cache/", $cachFilePath)){
											$extension = "mobile";
										}
										
						   				$cache_statics = new WpFastestCacheStatics($extension, strlen($buffer));
						   				$cache_statics->update_db();
									}
				   				}

				   				if($extension == "html"){
				   					if(!file_exists(WPFC_WP_CONTENT_DIR."/cache/index.html")){
				   						@file_put_contents(WPFC_WP_CONTENT_DIR."/cache/index.html", "");
				   					}
				   				}else{
				   					if(!file_exists(WPFC_WP_CONTENT_DIR."/cache/wpfc-minified/index.html")){
				   						@file_put_contents(WPFC_WP_CONTENT_DIR."/cache/wpfc-minified/index.html", "");
				   					}
				   				}

							}else{
							}
						}else{

						}
					}else{
						if(file_exists($cachFilePath."/".$prefix."index.".$extension)){

						}else{

							file_put_contents($cachFilePath."/".$prefix."index.".$extension, $buffer);
							
							if(class_exists("WpFastestCacheStatics")){
								if(!preg_match("/After\sCache\sTimeout/i", $_SERVER['HTTP_USER_AGENT'])){
									if(preg_match("/wpfc\-mobile\-cache/", $cachFilePath)){
										$extension = "mobile";
									}

					   				$cache_statics = new WpFastestCacheStatics($extension, strlen($buffer));
					   				$cache_statics->update_db();
								}
			   				}
						}
					}
				}
			}elseif($extension == "html"){
				$this->err = "Buffer is empty so the cache cannot be created";
			}
		}

		public function replaceLink($search, $replace, $content){
			$href = "";

			if(stripos($search, "<link") === false){
				$href = $search;
			}else{
				preg_match("/.+href=[\"\'](.+)[\"\'].+/", $search, $out);
			}

			if(count($out) > 0){
				$content = preg_replace("/<link[^>]+".preg_quote($out[1], "/")."[^>]+>/", $replace, $content);
			}

			return $content;
		}

		public function is_amp($content){
			$request_uri = trim($_SERVER["REQUEST_URI"], "/");

			// https://wordpress.org/plugins/amp/
			if($this->isPluginActive('amp/amp.php')){
				if(preg_match("/amp$/", $request_uri)){
					if(preg_match("/<html[^\>]+amp[^\>]*>/i", $content)){
						return true;
					}
				}
			}

			return false;
		}

		public function isMobile(){
			foreach ($this->get_mobile_browsers() as $value) {
				if(preg_match("/".$value."/i", $_SERVER['HTTP_USER_AGENT'])){
					return true;
				}
			}

			foreach ($this->get_operating_systems() as $key => $value) {
				if(preg_match("/".$value."/i", $_SERVER['HTTP_USER_AGENT'])){
					return true;
				}
			}
		}

		public function checkWoocommerceSession(){
			foreach($_COOKIE as $key => $value){
			  if(preg_match("/^wp\_woocommerce\_session/", $key)){
			  	return true;
			  }
			}

			return false;
		}

		public function isWpLogin($buffer){
			// if(preg_match("/<form[^\>]+loginform[^\>]+>((?:(?!<\/form).)+)user_login((?:(?!<\/form).)+)user_pass((?:(?!<\/form).)+)<\/form>/si", $buffer)){
			// 	return true;
			// }
			if($GLOBALS["pagenow"] == "wp-login.php"){
				return true;
			}

			return false;
		}

		public function hasContactForm7WithCaptcha($buffer){
			if(is_single() || is_page()){
				if(preg_match("/<input[^\>]+_wpcf7_captcha[^\>]+>/i", $buffer)){
					return true;
				}
			}
			
			return false;
		}

		public function is_wptouch_smartphone(){
			// https://plugins.svn.wordpress.org/wptouch/tags/4.0.4/core/mobile-user-agents.php
			// wptouch: ipad is accepted as a desktop so no need to create cache if user agent is ipad 
			// https://wordpress.org/support/topic/plugin-wptouch-wptouch-wont-display-mobile-version-on-ipad?replies=12
			if(preg_match("/ipad/i", $_SERVER['HTTP_USER_AGENT'])){
				return false;
			}

			$wptouch_smartphone_list = array();

			$wptouch_smartphone_list[] = array( 'iPhone' ); // iPhone
			$wptouch_smartphone_list[] = array( 'Android', 'Mobile' ); // Android devices
			$wptouch_smartphone_list[] = array( 'BB', 'Mobile Safari' ); // BB10 devices
			$wptouch_smartphone_list[] = array( 'BlackBerry', 'Mobile Safari' ); // BB 6, 7 devices
			$wptouch_smartphone_list[] = array( 'Firefox', 'Mobile' ); // Firefox OS devices
			$wptouch_smartphone_list[] = array( 'IEMobile/11', 'Touch' ); // Windows IE 11 touch devices
			$wptouch_smartphone_list[] = array( 'IEMobile/10', 'Touch' ); // Windows IE 10 touch devices
			$wptouch_smartphone_list[] = array( 'IEMobile/9.0' ); // Windows Phone OS 9
			$wptouch_smartphone_list[] = array( 'IEMobile/8.0' ); // Windows Phone OS 8
			$wptouch_smartphone_list[] = array( 'IEMobile/7.0' ); // Windows Phone OS 7
			$wptouch_smartphone_list[] = array( 'OPiOS', 'Mobile' ); // Opera Mini iOS
			$wptouch_smartphone_list[] = array( 'Coast', 'Mobile' ); // Opera Coast iOS

			foreach ($wptouch_smartphone_list as $key => $value) {
				if(isset($value[0]) && isset($value[1])){
					if(preg_match("/".preg_quote($value[0], "/")."/i", $_SERVER['HTTP_USER_AGENT'])){
						if(preg_match("/".preg_quote($value[1], "/")."/i", $_SERVER['HTTP_USER_AGENT'])){
							return true;
						}
					}
				}else if(isset($value[0])){
					if(preg_match("/".preg_quote($value[0], "/")."/i", $_SERVER['HTTP_USER_AGENT'])){
						return true;
					}
				}
			}

			return false;
		}

		public function is_anymobilethemeswitcher_mobile(){
			// https://plugins.svn.wordpress.org/any-mobile-theme-switcher/tags/1.9/any-mobile-theme-switcher.php
			$user_agent = $_SERVER['HTTP_USER_AGENT'];

			switch(true){
				case (preg_match('/ipad/i',$user_agent));
					return true;     
				break;

				case (preg_match('/ipod/i',$user_agent)||preg_match('/iphone/i',$user_agent));
					return true;     
				break;

				case (preg_match('/android/i',$user_agent));
					return true;
				break;

				case (preg_match('/opera mini/i',$user_agent));
					return true;     
				break;

				case (preg_match('/blackberry/i',$user_agent));
					return true;     
				break;

				case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent));
					return true;     
				break;

				case (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent));
					return true;     
				break;

				case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent)); 
					return true;
				break;
			}

			return false;
		}
	}
?>