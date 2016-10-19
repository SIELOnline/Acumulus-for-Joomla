<?php
/**
 * @copyright   Buro RaDer
 * @license     GPLv3; see license.txt.
 */

/**
 * Acumulus Entry Table class.
 */
class AcumulusTableAcumulusEntry extends JTable
{
    /** @var int */
    public $id = null;

    /** @var string */
    public $entry_id = null;

    /** @var string */
    public $token = null;

    /** @var string */
    public $source_type = null;

    /** @var int */
    public $source_id = null;

    /** @var string */
    public $created = null;

    /** @var string */
    public $updated = null;

    /**
     * Constructor
     *
     * @param JDatabaseDriver $db
     *   A database connector object. Leave empty for the default instance.
     */
    public function __construct(JDatabaseDriver $db = null)
    {
        parent::__construct('#__acumulus_entry', 'id', $db ? $db : JFactory::getDbo());
    }

    /**
     * Returns a set of instances that satisfy the filters.
     *
     * @param array $keys
     *   The filters to query on as a set of field => value pairs.
     *
     * @return AcumulusTableAcumulusEntry[]
     *   The, possibly empty, result set.
     */
    public function loadMultiple(array $keys) {
        $reset = true;

        if ($reset)
        {
            $this->reset();
        }

        // Initialise the query.
        $query = $this->_db->getQuery(true)->select('*')->from($this->_tbl);
        $fields = array_keys($this->getProperties());

        foreach ($keys as $field => $value)
        {
            // Check that $field is in the table.
            if (!in_array($field, $fields))
            {
                throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
            }
            // Add the search tuple to the query.
            $operator = $value === null ? 'is' : '=';
            $value = $value === null ? 'null' : $this->_db->quote($value);
            $query->where($this->_db->quoteName($field) . $operator . $value);
        }

        $this->_db->setQuery($query);

        $isFirst = true;
        $result = array();
        $rows = $this->_db->loadAssocList();
        if ($rows !== null) {
            foreach ($rows as $row) {
                if ($isFirst) {
                    $this->bind($row);
                    $result[] = $this;
                    $isFirst = false;
                }
                else {
                    $result[] = (new static())->bind($row);
                }
            }
        }
        return $result;
    }
}
