<?php

class Kimai_Remote_Api_Civicrm extends Kimai_Remote_Api
{

    /**
     * Authenticates a user and returns the API key.
     *
     * The result is either an empty string (not allowed or need to login first via web-interface) or
     * a string with max 30 character, representing the users API key.
     *
     * @param string $username
     * @param string $password
     * @return array
     */
    public function civiAuthenticate($username, $password = '')
    {
        
    }

}
