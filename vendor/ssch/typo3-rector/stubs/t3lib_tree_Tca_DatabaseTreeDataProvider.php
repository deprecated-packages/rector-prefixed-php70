<?php

namespace RectorPrefix20210528;

if (\class_exists('t3lib_tree_Tca_DatabaseTreeDataProvider')) {
    return;
}
class t3lib_tree_Tca_DatabaseTreeDataProvider
{
}
\class_alias('t3lib_tree_Tca_DatabaseTreeDataProvider', 't3lib_tree_Tca_DatabaseTreeDataProvider', \false);
