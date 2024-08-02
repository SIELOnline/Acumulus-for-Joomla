<?php
/**
 * @author    Buro RaDer, https://burorader.com/
 * @copyright SIEL BV, https://www.siel.nl/acumulus/
 * @license   GPL v3, see license.txt
 *
 * @noinspection AutoloadingIssuesInspection
 */

declare(strict_types=1);

namespace Siel\Joomla\Component\Acumulus\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use UnexpectedValueException;

use function get_class;
use function in_array;

/**
 * Acumulus Entry Table class.
 */
class AcumulusEntryTable extends Table
{
    public ?int $id;
    public ?int $entry_id;
    public ?string $token;
    public ?string $source_type;
    public ?int $source_id;
    public ?string $created;
    public ?string $updated;

    /**
     * Constructor
     */
    public function __construct(?DatabaseInterface $db = null)
    {
        parent::__construct('#__acumulus_entry', 'id', $db ?? Factory::getContainer()->get(DatabaseInterface::class));
    }

    /**
     * Returns a set of instances that satisfy the filters.
     *
     * @param array $keys
     *   The filters to query on as a set of field => value pairs.
     *
     * @return AcumulusEntryTable[]
     *   The, possibly empty, result set.
     */
    public function loadMultiple(array $keys): array
    {
        $fields = $this->getProperties();

        // Initialise the query.
        $this->reset();
        $query = $this->_db->getQuery(true)->select('*')->from($this->_tbl);

        foreach ($keys as $field => $value) {
            // Check that $field is in the table.
            if (!in_array($field, $fields, true)) {
                throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
            }
            // Add the search tuple to the query.
            $operator = $value === null ? ' is ' : ' = ';
            if ($value === null) {
                $value = 'null';
            } elseif (is_numeric($value)) {
                $value = (int) $value;
            } else {
                $value = $this->_db->quote($value);
            }
            $query->where($this->_db->quoteName($field) . $operator . $value);
        }

        $this->_db->setQuery($query);

        $result = [];
        $rows = $this->_db->loadAssocList();
        if ($rows !== null) {
            foreach ($rows as $row) {
                $entry = new static();
                $entry->bind($row);
                $result[] = $entry;
            }
        }
        return $result;
    }

    /**
     * Returns a keyed list with the properties of an Acumulus Entry.
     *
     * @return string[]
     */
    public function getProperties($public = true): array
    {
        return [
            'id' => 'id',
            'entry_id' => 'entry_id',
            'token' => 'token',
            'source_type' => 'source_type',
            'source_id' => 'source_id',
            'created' => 'created',
            'updated' => 'updated',
        ];
    }
}
