<?php

/**
 * The INode interface is the base interface, and the parent class of both ICollection and IFile.
 *
 * @copyright Copyright (C) 2007-2012 Rooftop Solutions. All rights reserved
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
interface Sabre_DAV_INode
{
    /**
     * Deleted the current node.
     */
    public function delete();

    /**
     * Returns the name of the node.
     *
     * This is used to generate the url.
     *
     * @return string
     */
    public function getName();

    /**
     * Renames the node.
     *
     * @param string $name The new name
     */
    public function setName($name);

    /**
     * Returns the last modification time, as a unix timestamp.
     *
     * @return int
     */
    public function getLastModified();
}
