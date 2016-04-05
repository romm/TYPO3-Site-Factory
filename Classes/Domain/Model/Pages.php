<?php
namespace Romm\SiteFactory\Domain\Model;

/*
 * 2016 Romain CANON <romain.hydrocanon@gmail.com>
 *
 * This file is part of the TYPO3 Site Factory project.
 * It is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License, either
 * version 3 of the License, or any later version.
 *
 * For the full copyright and license information, see:
 * http://www.gnu.org/licenses/gpl-3.0.html
 */

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Model for the database "pages" table.
 */
class Pages extends AbstractEntity
{

    /**
     * The title of the page.
     *
     * @var string
     */
    protected $title = '';

    /**
     * The hidden flag of the page.
     *
     * @var int
     */
    protected $hidden = 0;

    /**
     * The deleted flag of the page.
     *
     * @var int
     */
    protected $deleted = 0;

    /*********************
     * SETTERS & GETTERS *
     *********************/
    /**
     * @return    string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param    string
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return    int
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param    int
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return    int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param    int
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

}
