<?php
function resolveLanguage()
{
    if (strpos($_SERVER["SERVER_NAME"], 'blog.shoptet.hu') !== FALSE) {
        return 'hu_HU';
    }
    if (strpos($_SERVER["SERVER_NAME"], 'blog.shoptet.sk') !== FALSE) {
        return 'sk_SK';
    }
    return 'cs_CZ';
};
add_filter('locale', 'resolveLanguage');
?>
