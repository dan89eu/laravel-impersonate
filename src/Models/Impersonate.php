<?php

namespace Lab404\Impersonate\Models;

use Illuminate\Database\Eloquent\Model;
use Lab404\Impersonate\Services\ImpersonateManager;
use Lab404\Impersonate\Services\SentinelImpersonateManager;

trait Impersonate
{
    /**
     * Return true or false if the user can impersonate an other user.
     *
     * @param   void
     * @return  bool
     */
    public function canImpersonate()
    {
        return true;
    }

    /**
     * Return true or false if the user can be impersonate.
     *
     * @param   void
     * @return  bool
     */
    public function canBeImpersonated()
    {
        return true;
    }

    /**
     * Impersonate the given user.
     *
     * @param   Model $user
     * @return  bool
     */
    public function impersonate(Model $user)
    {
        return app(SentinelImpersonateManager::class)->take($this, $user);
    }

    /**
     * Check if the current user is impersonated.
     *
     * @param   void
     * @return  bool
     */
    public function isImpersonated()
    {
        return app(SentinelImpersonateManager::class)->isImpersonating();
    }

    /**
     * Leave the current impersonation.
     *
     * @param   void
     * @return  bool
     */
    public function leaveImpersonation()
    {
        if ($this->isImpersonated())
        {
            return app(SentinelImpersonateManager::class)->leave();
        }
    }
}
