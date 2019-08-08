<?php

namespace Ang3\Bundle\OdooApiBundle\ORM;

/**
 * @final
 *
 * @author Joanis ROUANET
 */
final class Events
{
    /**
     * The postLoad event occurs when a record was loaded.
     *
     * @Event("Ang3\Bundle\OdooApiBundle\ORM\Event\RecordEvent")
     */
    const postLoad = 'odoo_record.post_load';

    /**
     * The prePersist event occurs when a record is about to be created.
     *
     * @Event("Ang3\Bundle\OdooApiBundle\ORM\Event\RecordEvent")
     */
    const prePersist = 'odoo_record.pre_persist';

    /**
     * The postPersist event occurs when a record was created.
     *
     * @Event("Ang3\Bundle\OdooApiBundle\ORM\Event\RecordEvent")
     */
    const postPersist = 'odoo_record.post_persist';

    /**
     * The preUpdate event occurs when a record is about to be updated.
     *
     * @Event("Ang3\Bundle\OdooApiBundle\ORM\Event\RecordUpdateEvent")
     */
    const preUpdate = 'odoo_record.pre_update';

    /**
     * The postUpdate event occurs when a record was updated.
     *
     * @Event("Ang3\Bundle\OdooApiBundle\ORM\Event\RecordUpdateEvent")
     */
    const postUpdate = 'odoo_record.post_update';

    /**
     * The preDelete event occurs when a record is about to be deleted.
     *
     * @Event("Ang3\Bundle\OdooApiBundle\ORM\Event\RecordEvent")
     */
    const preDelete = 'odoo_record.pre_delete';

    /**
     * The postDelete event occurs when a record was updated.
     *
     * @Event("Ang3\Bundle\OdooApiBundle\ORM\Event\RecordEvent")
     */
    const postDelete = 'odoo_record.post_delete';
}
