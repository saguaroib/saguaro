<?php

/*

    Head. Or more specifically everything before the body tag.

    $head = new Head;
    echo $head->generate();

*/

class Head {
    public $info = [ //These are the defaults used unless specified otherwise.
        'page' => [
            'title' => '',
            'sub' => ''
        ],
        'css' => [
            'raw' => [], //Raw css to push to <style> tags
            'sheet' => [] ////Names of css files to include
        ],
        'js' => [
            'raw' => [], //Raw js to push to <script> tags
            'script' => [] //Names of js files to include
        ],
        'ribbon' => [ //[Navigation] ribbon items
            'item' => []
        ]
    ];

    function generate($noHead = false, $admin = false) {
		global $cssArray, $headVars;
        
        /* begin page content */
        $dat = "<!DOCTYPE html><head>
                <meta name='description' content='" . S_DESCR . "'/>
                <meta http-equiv='content-type'  content='text/html;charset=utf-8'/>
                <meta name='viewport' content='width=device-width, initial-scale=1'/>
                <link rel='shortcut icon' href='" . CSS_PATH . "imgs/favicon.ico'>
                <title>" .  strip_tags($this->info['page']['title']) ."</title>";

        $dat .= "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . "stylesheets/base.css'/>";
        $defaultStyle = (NSFW) ? "saguaba" : "sagurichan";
        $dat .= "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . "stylesheets/{$defaultStyle}.css'/>";
                
		foreach ($cssArray as $key => $value) 
			$dat .= "<link rel='alternate stylesheet' type='text/css' href='" . CSS_PATH . $value . "' title='" . $key . "' />";

        foreach ($this->info['css']['raw'] as $css) //adding raw css to the head in <style> tags
            $dat .= "<style type='text/css'>{$css}</style>";
            
        foreach($this->info['css']['sheet'] as $sheet) //Adding CSS stylesheets to the head
            $dat .= "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . $sheet . "'>";

		$dat .= $this->headerJS();

        foreach($this->info['js']['raw'] as $raw) //Adding js code in <script tags> to the head
            $dat .= "<script type='text/javascript'>{$raw}</script>"; 

        foreach($this->info['js']['script'] as $script) //Adding whole scripts to the head
            $dat .= "<script src='" . JS_PATH . "/{$script}' type='text/javascript'></script>";

		if (defined('EXTRA_SHIT')) $dat .= EXTRA_SHIT; 
		
        $dat .= '</head>';
        
        if ($noHead !== true) {
            $headSub .= '<div class="boardSubtitle">' . strip_tags($this->info['page']['sub']) . '</div>';
            $boardTitle = "<div class='boardTitle'>" . strip_tags($this->info['page']['title']) . "</div>" . $headSub;

            $bannerImg .= (defined('SHOWTITLEIMG') && SHOWTITLEIMG) ? '<img class="bannerImg" data-src="' . $this->randomBanner() . '" src="' . $this->randomBanner() . '" /><br>' : '';
            
            $ribbon = ($admin) ? $this->adminRibbon() . "<hr>" : null;
            
            $dat .= '<body><div class="beforePostform" /><div id="boardNavDesktop">' . $this->get_cached_file(BOARDLIST) . '</div>
                <div class="linkBar">[<a href="javascript:void(0);" id="settingsWindowLink">Settings</a>][<a href="' . HOME . '" target="_top">' . S_HOME . '</a>]</div><div class="boardBanner">' . $bannerImg . $boardTitle . '</div><hr>' . $ribbon . '<a id="top"></a>';
            $dat .= (ENABLE_ADS) ? "<div class='ads aboveForm'>" . ADS_ABOVEFORM . '<hr></div></div>' : "</div>";
        } else {
            $dat .= "<body>";
        }

        return $dat;
    }

    //Returns the HTML path to a random banner. $bannerAssets is stored in the board config.
    private function randomBanner() {
        //global $bannerAssets;
        /*
        $banner = ASSET_PATH . '/banners/' . BOARD_DIR . '/' . $bannerAssets[array_rand($bannerAssets)];
  
        return $banner;*/
    }
    
    private function adminRibbon() {
        $modes = array("panel", "reports", "appeals", "filters", "assets", "users", "settings", "rebuild", "logout");
        $temp .= "<div class='adminRibbon'>";
        $temp .= "[<a href='/" . BOARD_DIR . "/'>Return to Index</a>] ";
        
        foreach($modes as $mode) 
            if (($mode === 'logout' || $mode === 'panel') || valid($mode)) $temp .= "[<a href='" . PHP_SELF_ABS . "?mode=admin&admin={$mode}'>" . ucfirst($mode) . "</a>] "; //Truly php has functions for everything
            
        $temp .= "</div>";
        return $temp;
    }
    
	private function headerJS($admin = false) {
        global $cssArray;
        $temp .= "var SaguaroSelfAbsolute = '" . PHP_SELF_ABS . "';";
        $temp .= "var SaguaroSiteRoot = '" . SITE_ROOT . "';";
        $temp .= "var SaguaroStaticPath = '" . CSS_PATH . "';";
        $temp .= "var SaguaroBoard = '" . BOARD_DIR . "';";
        $temp .= "var SaguaroImageSource = '" . PUBLIC_IMAGE_DIR . "';";
        $temp .= "var SaguaroThumbSource = '" . PUBLIC_THUMB_DIR . "';";
        //$temp .= "var inPanel = 'false';";
        $temp .= (NSFW) ? 'var style_group = "nsfw";' : 'var style_group = "sfw";';

		if ($admin) { //Admin js variables go here
            $temp .= "var inPanel = 'true';";
		}

		$temp = "<script type='text/javascript'>" . $temp . "</script>";
		
		return $temp;
	}
    
    private function get_cached_file($filename) {
        static $cache = array();
        if (isset($cache[$filename]))
            return $cache[$filename];
        $cache[$filename] = @file_get_contents($filename);
        return $cache[$filename];
    }
}