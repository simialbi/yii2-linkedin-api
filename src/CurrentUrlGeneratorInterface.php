<?php
/**
 * @package yii2-linkedin-api
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\linkedin;

/**
 * Interface CurrentUrlGeneratorInterface
 * @package simialbi\yii2\linkedin
 */
interface CurrentUrlGeneratorInterface
{
    /**
     * Returns the current URL.
     *
     * @return string The current URL
     */
    public function getCurrentUrl();

    /**
     * Should we trust forwarded headers?
     *
     * @param bool $trustForwarded
     */
    public function setTrustForwarded($trustForwarded);
}