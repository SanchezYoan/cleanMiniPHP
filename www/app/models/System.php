<?php

/**
 * Centraliser les clés système utilisées par la configuration.
 */
class System extends Model
{
    /**
     * Clé de version iOS courante.
     */
    public const _IOS_CURRENT_VERSION_ = 'IOS_CURRENT_VERSION';

    /**
     * Clé de version iOS minimale.
     */
    public const _IOS_MINIMUM_VERSION_ = 'IOS_MINIMUM_VERSION';

    /**
     * Clé de version Android courante.
     */
    public const _ANDROID_CURRENT_VERSION_ = 'ANDROID_CURRENT_VERSION';

    /**
     * Clé de version Android minimale.
     */
    public const _ANDROID_MINIMUM_VERSION_ = 'ANDROID_MINIMUM_VERSION';
}
