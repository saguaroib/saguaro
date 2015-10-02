<?php

global $upfile, $upfile_name;

if ($_SERVER["REQUEST_METHOD"] != "POST")
    error(S_UNJUST, $upfile);

//Captcha check
if (BOTCHECK === true && !valid('moderator')) {
    require_once(CORE_DIR . '/general/captcha.php');
    $captcha = new Captcha;

    if ($captcha->isValid() !== true)
        error(S_CAPFAIL, $upfile);
}

//Uploaded file check
if ($_FILES["upfile"]["error"] > 0) {
    if ($_FILES["upfile"]["error"] == UPLOAD_ERR_INI_SIZE || $_FILES["upfile"]["error"] == UPLOAD_ERR_FORM_SIZE)
        error(S_TOOBIG, $upfile);
    if ($_FILES["upfile"]["error"] == UPLOAD_ERR_PARTIAL || $_FILES["upfile"]["error"] == UPLOAD_ERR_CANT_WRITE)
        error(S_UPFAIL, $upfile);
}

if ($upfile_name && $_FILES["upfile"]["size"] == 0) {
    error(S_TOOBIGORNONE, $upfile);
}

//Basic proxy check.
if (PROXY_CHECK && preg_match("/^(mail|ns|dns|ftp|prox|pc|[^\.]\.[^\.]$)/", $host) > 0 || preg_match("/(ne|ad|bbtec|aol|uu|(asahi-net|rim)\.or)\.(com|net|jp)$/", $host) > 0) {
    if (@fsockopen($_SERVER["REMOTE_ADDR"], 80, $a, $b, 2) == 1) {
        error(S_PROXY80, $dest);
    } elseif (@fsockopen($_SERVER["REMOTE_ADDR"], 8080, $a, $b, 2) == 1) {
        error(S_PROXY8080, $dest);
    }
}

//Check if user is banned
require_once(CORE_DIR . "/admin/banish.php");
$checkban = new Banish;
if (!$checkban->checkBan($_SERVER["REMOTE_ADDR"]))
    error(S_BADHOST, $upfile);

//Check if replying to locked thread
$resto = (int) $resto;
if ($resto) {
    global $mysql;
    $resto = (int) $resto;
    $result = $mysql->fetch_array("SELECT * FROM " . SQLLOG . " WHERE no=$resto");
    if ($result["locked"] == '1' && !valid('moderator'))
        error(S_THREADLOCKED, $upfile);
     mysql_free_result($query);
}

?>