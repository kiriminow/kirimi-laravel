<?php

use Kirimi\KirimiClient;

if (!function_exists('kirimi')) {
    /**
     * Get the KirimiClient instance from the container.
     */
    function kirimi(): KirimiClient
    {
        return app('kirimi');
    }
}
