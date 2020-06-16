<?php
function resolveLanguage()
{
    if (strpos($_SERVER["SERVER_NAME"], 'blog.shoptet.hu') !== FALSE) {
        return 'hu_HU';
    }
    return 'cs_CZ';
};
add_filter('locale', resolveLanguage);
?>
