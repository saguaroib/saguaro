<?php
/*

    Modern/new "More Info" panel.

    It doesn't use Log, but neither did the old one. Potentially?

    Currently relies on relative paths since admin.php isn't hidden behind a rewrite mod or similar.
    Therefore, it uses things like IMG_DIR directly without accounting for the change.

*/

class ModernInfo {
    private $cache = [];
    private $mediacache = [];

    function generate($no) {
        global $mysql;

        //Pull row from table.
        $no = $mysql->escape_string($no);
        $query = "SELECT * FROM " . SQLLOG . " WHERE no='" . $no . "'";
        $row = $mysql->fetch_assoc($query);
        if (!$row) { return "Invalid or non-existent post number."; }
        $this->cache = $row; //Copy to local cache.

        $html = $this->generateCSS(); //Inline CSS/style tag until the CSS is relocated.

        //Generate boxes. Order here determines order on the page (obviously).
        $html .= "<div id='left'>"; //Start the left column.
        $html .= $this->generateManager(); //Potentially most functional box.
        $html .= $this->generateBanForm();
        $html .= $this->generatePostInfo(); //This polls the log table an extra time to determine reply count for OPs only.
        $html .= $this->generateMediaInfo(); //This polls the file table, and also caches results to $this->mediacache[].
        $html .= "</div>"; //Close left column.
        $html .= $this->generateRightColumn(); //Generate the right column to show the images.

        //$html = "<div>".$html."</div>"; //Wrap in container?
        return $html;
    }
    private function generateManager() {
        $html = "<div id='manage' class='section'>
                    <div class='info'>Post Management</div>
                    <div class='info' style='text-align:center'>
                        " . $this->generateInfoHeader() . "
                    </div>";

        if ($this->cache['resto'] == 0) { //Functions unique to OPs only.
            $html .= "<div class='info'>
                        <select name='mode'>
                            <option value='sticky'>Sticky</option>
                            <option value='lock'>Lock</option>
                            <option value='permasage'>Permasage</option>
                            <option value='unsticky'>Unsticky</option>
                            <option value='unlock'>Unlock</option>
                        </select>
                        <input name='no' value='".$this->cache['no']."' type='hidden'>
                        <input value='Submit' type='submit'>
                    </div>";
        }
        $html .= "</div>";
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
        //This polls the log table an extra time to determine reply count for OPs only.
        $post = $this->cache; $no = $post['no'];
        $info = "<div id='post' class='section'><div class='info'>Post Information</div>";
        $info .= "<div class='info' id='res'>" . $this->generateInfoHeader() . "</div>";

        //Exclusive stats for OPs.
        if ($post['resto'] == 0) {
            global $mysql; //Unique global.

            $replies = $mysql->num_rows($mysql->query("SELECT no FROM ".SQLLOG." WHERE resto='".$no."'")); //Fetch replies. The query seems a bit overkill.
            $media = count(explode(" ",$post['media']));
            $info .= "<div class='info' id='resx'>
                          <table style='width:100%;text-align:center'>
                              <tr><td>Replies</td><td>Media</td></tr>
                              <tr><td>$replies</td><td>$media</td></tr>
                          </table>
                      </div>";
        }

        $host = substr(md5($post['host']), 12,20);
        $info .= "<div class='info' id='name'>" . $post['name'] . " <span class='inlinfo' style='float:right;'>($host)</span></div>
                <div class='info' id='date'>" . $post['now'] . "</div>
                <div class='info' id='subject'>" . $post['sub'] . "</div>
                <div class='info' id='comment'>" . $post['com'] . "</div>
            </div>";

        return $info;
    }
    private function generateMediaInfo() {
        if (empty($this->cache['media'])) { return ""; } //If no media, stop now. Crude check until the column is sorted out.

        global $mysql;
        $query = $mysql->query("SELECT * FROM " . SQLMEDIA . " WHERE parent='" . $this->cache['no'] . "'");
        $html = "<div id='media' class='section'><div class='info'>Media Information</div>";
        $html .= "<div class='info'><strong>Delete:</strong> <input value='All' type='submit'> <input value='Selected' type='submit'></div>";
        /* Eventually wrap All and Selected in proper forms */

        while ($media = $mysql->fetch_assoc($query)) {
            array_push($this->mediacache,$media);
            //var_dump($media);
            $html .= "<div class='info'><input type='checkbox' name='delete' value='".$media['no']."'><a href='".IMG_DIR.$media['localname']."' title='".$media['filename']."' target='_blank'><span class='overflow'>".$media['filename']."</span></a>";

            $size = $this->calculateSize($media['filesize']);
            //Extra info.
            $html .= "<br><small>$size / " . $media['width']. "x" . $media['height'] . " / <a href='" . THUMB_DIR . $media['localthumbname'] . "'>Thumb</a></small>";
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
        $css = '<style> /* Post info page specific CSS. */
                .section table { font-size: 10pt; }

                div#left, div#right { margin-top:10px; }

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
                    margin-right:5px;
                    margin-left:5px;
                    /*position:absolute;*/
                }

                div.preview_holder {
                    display: inline-block;
                    vertical-align: middle;
                    margin:5px 5px 0px 5px;
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
                    max-width:90%;
                    overflow:hidden;
                    text-overflow:ellipsis;
                    display:inline-block;
                    white-space:nowrap;
                }
                .info small { text-align:right;display:block; }
                .info small span { text-decoration:underline; }
                #resx table tr:first-child td { border-bottom:1px solid #000 };

                .inlinfo { font-style:italic; text-align:right; }

                #right > div:first-child { background-color:#000;color:#fff;font-weight:bold;text-align:center;padding:3px 4px; }
                #right .item { /*display:inline-block;float:left;*/padding-right:5px; }
                #right .item:hover { background-color:rgba(0,0,0,0.1); }
                </style>';
        return $css;
    }

    private function generateRightColumn() {
        $right = "<div id='right'><div>Media (".count($this->mediacache).")</div>"; //Start right column.
        foreach ($this->mediacache as $media) { //$this->mediacache populated by $this->generateMediaInfo();
            $url = IMG_DIR.$media['localname'];
            $thumb = THUMB_DIR.$media['localthumbname'];
            $size = $this->calculateSize($media['filesize']); //It was a this point I realized we weren't storing the thumbnail size, but we weren't before anyway!

            $right .= "<div class='item'>
                        <div style='display:inline-block;' class='preview_holder'><a href='$url' target='_blank'><img class='preview' src='$thumb'></img></a></div>
                        <div style='display:inline-block;vertical-align:top;margin-top:5px;'><span style='font-style:italic'>".$media['filename']."</span><br><small>".$media['hash']."<br>$size / ".$media['width']. "x" . $media['height']."
                        <br><br>
                        Local: ".$media['localname']."<br>
                        Thumb: ".$media['localthumbname']." (".$media['thumb_width']."x".$media['thumb_height'].")</small></div>";


            $right .= "</div>";
        }
        $right .= "</div>";

        return $right;
    }

    private function calculateSize($size) {
        //Calculate size: http://stackoverflow.com/a/2510468
        //$size is in bytes, obviously.
        $base = log($size) / log(1024);
        $suffix = array("","K","M","G","T");
        $size = round(pow(1024, $base - floor($base))).$suffix[floor($base)]."B";

        return $size;
    }
}

?>