<?php
/*

    Modern/new "More Info" panel.

    It doesn't use Log, but neither did the old one. Potentially?

    Currently relies on relative paths since admin.php isn't hidden behind a rewrite mod or similar.
    Therefore, it uses things like IMG_DIR directly without accounting for the change.

*/

class ModernInfo {
    private $cache = [];

    function generate($no) {
        global $mysql;

        //Pull row from table.
        $no = $mysql->escape_string($no);
        $query = "SELECT * FROM " . SQLLOG . " WHERE no='" . $no . "'";
        $row = $mysql->fetch_assoc($query);
        $this->cache = $row;

        $html = $this->generateCSS() . "<div id='left'>
                <div id='manage' class='section'>
                    <div class='info'>Post Management</div>
                    <div class='info' style='text-align:center'>
                        " . $this->generateInfoHeader() . "
                    </div>
                    <div class='info'>
                        <select name='mode'>
                            <option value='sticky'>Sticky</option>
                            <option value='lock'>Lock</option>
                            <option value='permasage'>Permasage</option>
                            <option value='unsticky'>Unsticky</option>
                            <option value='unlock'>Unlock</option>
                        </select>
                        <input name='no' value='221' type='hidden'>
                        <input value='Submit' type='submit'>
                    </div>
                </div>";
        $html .= $this->generateBanForm() . $this->generatePostInfo() . $this->generateMediaInfo(); //Generate boxes.
        $html .= "</div>";
        $a = "<div id='right' class='preview_holder'>
                <a href='huge.png' target='_blank'><img class='preview' src='huge.png'></img></a>
            </div>";
        return $html;
    }
    private function generateInfoHeader() {
        $post = $this->cache; $no = $post['no'];

        //Generate header
        $stat = "(".(($post['resto'] == 0)?'OP':'Reply').")";
        $url = RES_DIR.(($post['resto'] == 0)?$post['no']:$post['resto']).PHP_EXT."#pc".$post['no'];
        $info = "<a href='$url' title='#$no' class='overflow' target='_blank'>$stat #$no</a>";
        if ($post['resto'] > 0) { $info .= "<small><a href='?mode=more&no=".$post['resto']."'>(parent: #".$post['resto'].")</a></small>"; }
        //Terribly inefficient. Indicate special status.
        if ($post['sticky']) { $info .= "<img class='icon' src='sticky.gif' title='Sticky' alt='Sticky' />"; }
        if ($post['locked']) { $info .= "<img class='icon' src='locked.gif' title='Locked/Closed' alt='Locked/Closed' />"; }
        if ($post['permasage']) { $info .= "<img class='icon' src='permasage.gif' title='Permasaged' alt='Permasaged' />"; }

        return $info;
    }
    private function generatePostInfo() {
        $post = $this->cache; $no = $post['no'];
        $info = "<div id='post' class='section'><div class='info'>Post Information</div>";
        $info .= "<div class='info' id='res'>" . $this->generateInfoHeader() . "</div>";

        //Exclusive stats for OPs.
        if ($post['resto'] == 0) {
            global $mysql;

            $replies = $mysql->num_rows($mysql->query("SELECT no FROM ".SQLLOG." WHERE resto='".$no."'")); //Fetch replies. The query seems a bit overkill.
            $media = count(explode(" ",$post['media']));
            $info .= "<div class='info' id='resx'>
                          <table style='width:100%;text-align:center'>
                              <tr><td>Replies</td><td>Media</td></tr>
                              <tr><td>$replies</td><td>$media</td></tr>
                          </table>
                      </div>";
        }

        $info .= "<div class='info' id='name'>" . $post['name'] . " <span class='inlinfo' style='float:right;'>(127.0.0.1)</span></div>
                <div class='info' id='date'>" . $post['now'] . "</div>
                <div class='info' id='subject'>" . $post['sub'] . "</div>
                <div class='info' id='comment'>" . $post['com'] . "</div>
            </div>";
        /*<div class='preview_holder'>
            <a href='small.png' target='_blank'><img class='preview' src='small.png'></img></a>
        </div>";*/

        return $info;
    }
    private function generateMediaInfo() {
        if (empty($this->cache['media'])) { return ""; } //If no media, stop now. Crude check until the column is sorted out.

        global $mysql;
        $query = $mysql->query("SELECT * FROM " . SQLMEDIA . " WHERE parent='" . $this->cache['no'] . "'");
        $html = "<div id='media' class='section'><div class='info'>Media Information</div>";

        while ($media = $mysql->fetch_assoc($query)) {
            //var_dump($media);
            $html .= "<div class='info'><a href='" . IMG_DIR . $media['localname'] . "' title='Hash: " . $media['hash'] . "' target='_blank'>" . $media['filename'] . "</a>";

            //Calculate size: http://stackoverflow.com/a/2510468
            $base = log($media['filesize']) / log(1024);
            $suffix = array("","K","M","G","T");
            $size = round(pow(1024, $base - floor($base))).$suffix[floor($base)]."B";

            //Extra info.
            $html .= "<br><small>$size / " . $media['width']. "x" . $media['height'] . " / <a href='" . THUMB_DIR . $media['localthumbname'] . "'>Thumb</small>";
            $html .= "</div>"; //Close.
        }

        $html .= "</div>";

        return $html;
    }
    private function generateBanForm() {
        $form = "<div id='ban' class='section'><form>
                        <div class='info'>Ban Management</div>
                        <div class='info'>
                            <input type='number' min='1' size='4' name='banlength'  placeholder='Unban in...' />
                             <select name='timeunit'>
                                <!-- <option value='minutes'>Minutes</option> -->
                                <option value='hours'>Hours</option>
                                <option value='days'>Days</option>
                                <option value='weeks'>Weeks</option>
                                <option value='months'>Months</option>
                                <!--<option value='years'>Years</option>
                                <option value='decades'>Decades</option>-->
                            </select>
                        </div>
                        <div class='info'>
                            <strong>Type:</strong>
                            <select name='banType'>
                                <option value='warn'>Warning only</option>
                                <option value='thisboard'>This board - /saguaro/ </option>
                                <option value='global'>All boards</option>
                                <option value='perma'>Permanent - All boards</option>
                            </select>
                        </div>
                        <div class='info'>
                            <textarea rows='2' cols='25' name='pubreason' placeholder='Public reason.' style='width:100%;resize:vertical;'></textarea>
                        </div>
                        <div class='info'><input name='staffnote' type='text' style='width:100%' placeholder='Staff notes.'></div>
                        <div class='info'>
                            <input name='custmess' placeholder='Append to post comment.' type='text' style='width:100%'>
                        </div>
                        <div class='info'>
                            <strong>After ban:</strong>
                            <select name='afterban'>
                                <option value='none'>None</option>
                                <option value='delpost'>Delete this post</option>
                                <option value='delallbyip'>Delete all by this IP</option>
                                <option value='delimgonly'>Delete image only</option>
                            </select>
                        </div>
                        <div class='info'>
                            <table cellspacing='0' cellpadding='0'>
                                <tr>
                                    <td><strong>Blacklist:</strong></td>
                                    <td><label><input name='blacklistcom' type='checkbox'> Comment</label></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td><label><input name='blacklistimage' type='checkbox'> Image MD5</label></td>
                                </tr>
                            </table>
                        </div>
                        <div class='info' style='text-align:right'><input value='Ban' type='submit' style='min-width:30%'></div>
                    </form></div>";
        return $form;
    }

    private function generateCSS() {
        //Temporary until a stylesheet location is determined.
        $css = '<style>body {
                    background: #EEF2FF url(fade-blue.png) top center repeat-x;
                    font-size: 10pt;
                    color: #000000;
                    font-family: Arial, Helvetica, sans-serif;
                }

                /*

                    Post info page specific CSS.

                */

                .section table { font-size: 10pt; }

                div#container {
                    max-width:100%;
                    width:100%;
                }

                div#left {
                    display:inline-block;
                    max-width:250px;
                    margin:0 10 0 5px;
                    float:left;
                    overflow:hidden;
                }

                div#left select {
                    width:auto;
                }

                div#right {
                    display:inline-block;
                    vertical-align:top;
                    margin-right:10px;
                    position:absolute;
                }

                div.preview_holder {
                    background-color: #434343;
                    background-image:linear-gradient(#434343, #282828);
                }

                img.preview {
                    width:100%;
                    background-color: transparent;

                    /* https://codepen.io/jasonadelia/pen/DnrAe */
                    /*background-image: linear-gradient(0deg,transparent 24%,rgba(255,255,255,.05) 25%,rgba(255,255,255,.05) 26%,transparent 27%,transparent 74%,rgba(255,255,255,.05) 75%,rgba(255,255,255,.05) 76%,transparent 77%,transparent),linear-gradient(90deg,transparent 24%,rgba(255,255,255,.05) 25%,rgba(255,255,255,.05) 26%,transparent 27%,transparent 74%,rgba(255,255,255,.05) 75%,rgba(255,255,255,.05) 76%,transparent 77%,transparent);
                    background-size:12% 12%;*/

                    /* http://lea.verou.me/css3patterns/#blueprint-grid */
                    background-color:#269;
                    background-image: linear-gradient(white 2px, transparent 2px),
                    linear-gradient(90deg, white 2px, transparent 2px),
                    linear-gradient(rgba(255,255,255,.3) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.3) 1px, transparent 1px);
                    background-size:33.33% 33.33%, 33.33% 33.33%, 5.555% 5.555%, 5.555% 5.555%;
                    background-position:-2px -2px, -2px -2px, -1px -1px, -1px -1px
                }
                img.preview:hover {
                    background: #FFF url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYAQMAAADaua+7AAAABlBMVEXj4+OwsLB082AeAAAAAnRSTlOzs+sT0nUAAAATSURBVAjXY/j/gYEkzMD/nxQMALcpI92VPFGdAAAAAElFTkSuQmCC);
                }

                .section {
                    min-width:250px;
                    max-width:250px;
                    border-left: 2px solid black;
                    margin-bottom: 1em;
                    position: relative;
                    display: inline-block;
                    vertical-align: top;
                }

                .section .info:first-child {
                    background-color:#000;
                    color:#fff;
                    text-align:center;
                    font-weight:bold;
                    cursor:pointer;
                }

                .section .info {
                    padding:3px 4px;
                }

                .section .info:nth-child(2n+2) {
                    background-color:#c4d1ff;
                }

                .icon  { padding:0 2px; }
                .icon { float:right; }
                .overflow {
                    max-width:80%;
                    overflow:hidden;
                    text-overflow:ellipsis;
                    display:inline-block;
                    white-space:nowrap;
                }
                .info small { text-align:right;display:block; }
                .info small span { text-decoration:underline; }
                #resx table tr:first-child td { border-bottom:1px solid #000 };

                .inlinfo { font-style:italic; text-align:right; }</style>';
        return $css;
    }
}

?>