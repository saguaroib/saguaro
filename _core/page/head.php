<?php

/*

    Head. Or more specifically everything before the body tag.

    $head = new Head;
    echo $head->generate();

*/

class Head {
    public $info = [ //These are the defaults used unless specified otherwise.
        'page' => [
            'title' => ''
        ],
        'css' => [
            'extra' => []
        ]
    ];

    function generate() {
        $dat = '';
        $boardTitle = '';
        $bannerImg = '';
        $headSub = '';

        if (SHOWTITLETXT > 0) {
            $boardTitle = "<div class='boardTitle'>" . TITLE . "</div>" . $headSub;
            $headSub .= '<div class="boardSubtitle">' . S_HEADSUB . '</div><hr>';
            if (SHOWTITLETXT == 2)  //you cannot stop me repod i am invincible
                $boardTitle ="<div class='boardTitle'/>/" . BOARD_DIR . "/ - " . TITLE . "</div>";
        }
        $bannerImg .= (SHOWTITLEIMG) ? '<img class="bannerImg" src="' . TITLEIMG . '" onclick="this.src=this.src;" alt="' . TITLE . '" /><br>' : '';

        /* begin page content */
        $dat .= "<!DOCTYPE html><head>
                <meta name='description' content='" . S_DESCR . "'/></meta>
                <meta http-equiv='content-type'  content='text/html;charset=utf-8' /></meta>
                <meta name='viewport' content='width=device-width, initial-scale=1'></meta>
                <meta http-equiv='cache-control' content='max-age=0' />
                <meta http-equiv='cache-control' content='no-cache' />
                <meta http-equiv='expires' content='0' />
                <meta http-equiv='expires' content='Tue, 01 Jan 1980 1:00:00 GMT' />
                <meta http-equiv='pragma' content='no-cache' />
                <link rel='shortcut icon' href='" . CSS_PATH . "imgs/favicon.ico'>
                <title>" .  $this->info['page']['title'] ."</title>";

        if (NSFW) {
            $dat .= "<link class='togglesheet' rel='stylesheet' type='text/css' href='" . CSS_PATH . CSS1 . "' title='Saguaba' />
                <link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/mobile.css' title='mobile' />
                <link class='togglesheet' rel='alternate stylesheet' type='text/css' media='screen'  href='" . CSS_PATH . CSS2 . "' title='Sagurichan' />";
        } else {
            $dat .= "<link class='togglesheet' rel='stylesheet' type='text/css' media='screen'  href='" . CSS_PATH . CSS2 . "' title='Sagurichan' />
            <link rel='stylesheet' type='text/css' href='" . CSS_PATH . "/stylesheets/mobile.css' title='mobile' />
            <link class='togglesheet' rel='alternate stylesheet' type='text/css' href='" . CSS_PATH . CSS1 . "' title='Saguaba' />";
        }
        //<link class='togglesheet' rel='alternate stylesheet' type='text/css' media='screen'  href='" . CSS_PATH . CSS4 . "' title='Burichan'/> RIP Burichan 1862-2015
        $dat .= "<link class='togglesheet' rel='alternate stylesheet' type='text/css' media='screen'  href='" . CSS_PATH . CSS3 . "' title='Tomorrow' />";

        foreach($this->info['css']['extra'] as $css) {
            $dat .= "<link rel='stylesheet' type='text/css' href='" . CSS_PATH . "$css'/>";
        }

        $dat .= "<script src='" . JS_PATH . "/jquery.min.js' type='text/javascript'></script>
                <script src='" . JS_PATH . "/styleswitch.js' type='text/javascript'></script>
                <script src='" . JS_PATH . "/main.js' type='text/javascript'></script>";

        if (USE_JS_SETTINGS)  $dat .= '<script src="' . JS_PATH . '/suite_settings.js" type="text/javascript"></script>';
        if (USE_IMG_HOVER)    $dat .= '<script src="' . JS_PATH . '/image_hover.js" type="text/javascript"></script>';
        if (USE_IMG_TOOLBAR)  $dat .= '<script src="' . JS_PATH . '/image_toolbar.js" type="text/javascript"></script>';
        if (USE_IMG_EXP)      $dat .= '<script src="' . JS_PATH . '/image_expansion.js" type="text/javascript"></script>';
        if (USE_UTIL_QUOTE)   $dat .= '<script src="' . JS_PATH . '/utility_quotes.js" type="text/javascript"></script>';
        if (USE_INF_SCROLL)   $dat .= '<script src="' . JS_PATH . '/infinite_scroll.js" type="text/javascript"></script>';
        if (USE_FORCE_WRAP)   $dat .= '<script src="' . JS_PATH . '/force_post_wrap.js" type="text/javascript"></script>';
        if (USE_UPDATER)      $dat .= '<script src="' . JS_PATH . '/thread_updater.js" type="text/javascript"></script>';
        if (USE_THREAD_STATS) $dat .= '<script src="' . JS_PATH . '/thread_stats.js" type="text/javascript"></script>';
        if (REPOD_EXTRA)      $dat .= '<script src="' . JS_PATH . '/extra/bgmod.js" type="text/javascript"></script>';
        if (USE_EXTRAS) {
            foreach (glob(JS_PATH . "/extra/*.js") as $path) {
                $dat .= "<script src='$path' type='text/javascript'></script>";
            }
            unset($path);
        }

        $dat .= EXTRA_SHIT . '</head><body class="is_index"><div class="beforePostform" />' . $titlebar . '
                <span class="boardList desktop">' . ((file_exists(BOARDLIST)) ? file_get_contents(BOARDLIST) : ''). '</div>
                <div class="linkBar">[<a href="' . HOME . '" target="_top">' . S_HOME . '</a>][<a href="' . PHP_ASELF_ABS . '">' . S_ADMIN . '</a>]
                </span><div class="boardBanner">' . $bannerImg . $boardTitle . '</div>' . $headSub . '
                <a href="#top"></a>';

        if (USE_ADS1) {
            $dat .= ADS1 . '<hr>';
        }
        $dat .= "</div>";

        return $dat;
    }
}

?>