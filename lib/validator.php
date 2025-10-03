<?php
function required($v){ return isset($v) && trim($v) !== ''; }
function email($v){ return filter_var($v, FILTER_VALIDATE_EMAIL); }
function minlen($v,$n){ return mb_strlen(trim($v))>=$n; }
