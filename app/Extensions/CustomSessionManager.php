<?php

namespace App\Extensions;

use Illuminate\Session\SessionManager;

class CustomSessionManager extends SessionManager
{
    /**
     * Create an instance of the database session driver.
     *
     * @return \Illuminate\Session\Store
     */
    protected function createDatabaseDriver()
    {
        $table = $this->config->get('session.table');

        $lifetime = $this->config->get('session.lifetime');

        return $this->buildSession(new CustomDatabaseSessionHandler(
            $this->getDatabaseConnection(), $table, $lifetime, $this->container
        ));
    }
}
