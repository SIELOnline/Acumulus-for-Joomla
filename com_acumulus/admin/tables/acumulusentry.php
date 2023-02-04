<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * @noinspection AutoloadingIssuesInspection
 */

declare(strict_types=1);

use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Acumulus Entry Table class.
 */
class AcumulusTableAcumulusEntry extends Table
{
    public ?int $id;
    public ?string $entry_id;
    public ?string $token;
    public ?string $source_type;
    public ?int $source_id;
    public ?string $created;
    public ?string $updated;

    /**
     * Constructor
     *
     * @noinspection PhpUndefinedClassInspection : J3: JDatabaseDriver
     * @param \Joomla\Database\DatabaseDriver|\JDatabaseDriver|null $db
     *   A database connector object. Leave empty for the default instance.
     */
    public function __construct($db = null)
    {
        // J4: $db ?? Factory::getContainer()->get('DatabaseDriver'); (or injection)
        /** @noinspection PhpDeprecationInspection : Deprecated as of J4 */
        parent::__construct('#__acumulus_entry', 'id', $db ?? JFactory::getDbo());
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
    public function loadMultiple(array $keys): array
    {
        $reset = true;
        /** @noinspection PhpConditionAlreadyCheckedInspection @todo: why this construction here/ */
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
            if (!in_array($field, $fields, true))
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
        $result = [];
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
