<?php

use Hyde\Framework\Hyde;

if (! function_exists('hyde')) {
    /**
     * Get the Hyde facade class.
     *
     * @return \Hyde\Framework\Hyde
     */
    function hyde(): Hyde
    {
        return new Hyde();
    }
}
