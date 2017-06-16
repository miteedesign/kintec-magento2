<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   1.0.58
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Api\Config;

interface BlogMxInterface
{
    /**
     * Check if Blog Mx enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Check if Blog Mx open graph enabled
     *
     * @return bool
     */
    public function isOgEnabled();

    /**
     * Check if Blog Mx snippets enabled
     *
     * @return bool
     */
    public function isSnippetsEnabled();

    /**
     * Blog Mx actions
     *
     * @return bool
     */
    public function getActions();
}