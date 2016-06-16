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
            'raw' => array(), //Raw css to push to <style> tags
            'sheet' => array() ////Names of css files to include
        ],
        'js' => [
            'raw' => array(), //Raw js to push to <script> tags
            'script' => array() //Names of js files to include
        ]
    ];

    public function generate($noHead = false) {
		global $cssArray, $headVars;
		
        $headSub .= '<div class="boardSubtitle">' . $this->info['page']['sub'] . '</div>';
        $boardTitle = "<div class='boardTitle'>" . $this->info['page']['title'] . "</div>" . $headSub;

        $bannerImg .= (defined('SHOWTITLEIMG') && SHOWTITLEIMG) ? '<img class="bannerImg" data-src="' . $this->randomBanner . '" /><br>' : '';

        /* begin page content */
        $dat .= "<!DOCTYPE html><head>
                <meta name='description' content='" . S_DESCR . "'/>
                <meta http-equiv='content-type'  content='text/html;charset=utf-8'/>
                <meta name='viewport' content='width=device-width, initial-scale=1'/>
                <link rel='shortcut icon' href='" . CSS_PATH . "imgs/favicon.ico'>
                <title>" .  $this->info['page']['title'] ."</title>";

        $defaultStyle = (NSFW) ? "saguaba" : "sagurichan";
        $dat .= "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . "stylesheets/{$defaultStyle}.css'/>";
                
		foreach ($cssArray as $key => $value) 
			$dat .= "<link rel='alternate stylesheet' type='text/css' href='" . CSS_PATH . $value . "' title='" . $key . "' />";

        foreach ($this->info['css']['raw'] as $css) { //adding raw css to the head in <style> tags
            $dat .= "<style type='text/css'>{$css}</style>";
        }
            
        foreach($this->info['css']['sheet'] as $sheet) //Adding CSS stylesheets to the head
            $dat .= "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . $sheet . "'>";

		$dat .= $this->headerJS();

        foreach($this->info['js']['raw'] as $script) //Adding js code in <script tags> to the head
            $dat .= "<script type='text/javascript'>{$raw}</script>";

        foreach($this->info['js']['script'] as $script) //Adding scripts to the head
            $dat .= "<script src='" . JS_PATH . "/{$script}' type='text/javascript'></script>";

		if (defined('EXTRA_SHIT')) $dat .= EXTRA_SHIT; 
		
        $dat .= '</head>';
        
        if ($noHead !== true) {
            $dat .= '<body><div class="beforePostform" /><div id="boardNavDesktop">' . $this->get_cached_file(BOARDLIST) . '</div>
                <div class="linkBar">[<a href="javascript:void(0);" id="settingsWindowLink">Settings</a>][<a href="' . HOME . '" target="_top">' . S_HOME . '</a>]</div><div class="boardBanner">' . $bannerImg . $boardTitle . '</div><hr><a id="top"></a>';
            $dat .= (ENABLE_ADS) ? "<div class='ads aboveForm'>" . ADS_ABOVEFORM . '<hr></div></div>' : "</div>";
        } else {
            $dat .= "<body>";
        }

        return $dat;
    }

    private function randomBanner() {
       /* $list = glob(CSS_PATH . '/banners/' . BOARD_DIR . '/*.*');
        echo $list;
        $ret = array_rand($list);
        return $list[$ret];*/
        return null;
    }
    
	private function headerJS($admin = false) {
        global $cssArray;
		$temp .= "var phpself = '" . PHP_SELF . "';";
		$temp .= "var site = '//" . SITE_ROOT . "';";
        $temp .= "var cssPath = '" . CSS_PATH . "';";
		$temp .= "var board = '" . BOARD_DIR . "';"; 
		$temp .= "var jsPath = '" . JS_PATH . "';";
        $temp .= "var inPanel = 'false';";
		$temp .= (NSFW) ? 'var style_group = "nsfw";' : 'var style_group = "sfw";';

		if ($admin) { //Admin js variables go here
            $temp .= "var inPanel = 'true';";
		}

		$temp = "<script type='text/javascript'>" . $temp . "</script>";
		
		return $temp;
	}
    
    //Only read a file once instead of reading it for EACH page rebuild in a single post.
    private function get_cached_file($filename) { 
        static $cache = array();
        if (isset($cache[$filename]))
            return $cache[$filename];
        $cache[$filename] = @file_get_contents($filename);
        return $cache[$filename];
    }
}

?>
